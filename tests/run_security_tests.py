"""Run against a temporary MariaDB instance and copied PHP app, never production.

Usage: python tests/run_security_tests.py --mysql-bin C:/xampp/mysql/bin
"""
import argparse
import http.cookiejar
import os
from pathlib import Path
import re
import shutil
import socket
import subprocess
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request

ROOT = Path(__file__).resolve().parents[1]
parser = argparse.ArgumentParser()
parser.add_argument('--mysql-bin', default='C:/xampp/mysql/bin')
parser.add_argument('--ui', action='store_true', help='Run browser checks with Playwright (NODE_PATH required).')
args = parser.parse_args()
MYSQL = Path(args.mysql_bin)
PHP = shutil.which('php')
if not PHP or not (MYSQL / 'mysql_install_db.exe').exists():
    raise SystemExit('PHP and XAMPP MariaDB binaries are required.')

def free_port():
    with socket.socket() as sock:
        sock.bind(('127.0.0.1', 0))
        return sock.getsockname()[1]

class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None

temp_root = Path(tempfile.mkdtemp(prefix='siakad-security-')).resolve()
mysql_port, web_port = free_port(), free_port()
server = web = None
log_handles = []
checks = 0
flags = subprocess.CREATE_NO_WINDOW if os.name == 'nt' else 0

def sql(text, database=None, success=True):
    cmd = [str(MYSQL / 'mysql.exe'), '--host=127.0.0.1', f'--port={mysql_port}', '--user=root', '--batch', '--skip-column-names']
    if database:
        cmd.append(database)
    result = subprocess.run(cmd, input=text, encoding='utf-8', capture_output=True, creationflags=flags)
    if success and result.returncode:
        raise RuntimeError(result.stderr)
    return result

def check(value, label):
    global checks
    if not value:
        raise AssertionError(label)
    checks += 1

try:
    datadir = temp_root / 'mysql'
    subprocess.run([str(MYSQL / 'mysql_install_db.exe'), f'--datadir={datadir}', f'--port={mysql_port}'], check=True, capture_output=True, creationflags=flags)
    db_log = open(temp_root / 'mysql.log', 'w', encoding='utf-8')
    log_handles.append(db_log)
    server = subprocess.Popen([str(MYSQL / 'mysqld.exe'), f'--defaults-file={datadir / "my.ini"}', '--bind-address=127.0.0.1', f'--port={mysql_port}', '--console'], stdout=db_log, stderr=db_log, creationflags=flags)
    for _ in range(100):
        if sql('SELECT 1;', success=False).returncode == 0:
            break
        time.sleep(0.1)
    else:
        raise RuntimeError('Temporary MariaDB did not start.')
    original = (ROOT / 'database/schema.sql').read_text(encoding='utf-8-sig')
    schema = '\n'.join(re.findall(r'CREATE TABLE .*?;|ALTER TABLE .*?;', original, re.S))
    sql('CREATE DATABASE siakad_security_test CHARACTER SET utf8mb4; USE siakad_security_test; SET SESSION sql_mode="NO_AUTO_VALUE_ON_ZERO";\n' + schema)
    sql('ALTER TABLE mata_kuliah MODIFY semester VARCHAR(11) NOT NULL;', 'siakad_security_test')
    sql((ROOT / 'database/migrations/2026-10-03_krs_semester.sql').read_text(encoding='utf-8'), 'siakad_security_test')
    env = dict(os.environ, SIAKAD_TEST_PORT=str(mysql_port))
    result = subprocess.run([PHP, str(ROOT / 'tests/security_integration.php')], env=env, capture_output=True, text=True, creationflags=flags)
    if result.returncode:
        raise RuntimeError(result.stdout + result.stderr)
    print(result.stdout.strip())
    migration = (ROOT / 'database/migrations/2026-10-03_integritas_akademik.sql').read_text(encoding='utf-8')
    sql(migration, 'siakad_security_test')
    sql(migration, 'siakad_security_test')
    check(sql("SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA='siakad_security_test' AND DELETE_RULE='CASCADE';").stdout.strip() == '0', 'CASCADE removed; migration repeatable')
    check(sql('DELETE FROM jadwal_mengajar WHERE id_jadwal=1;', 'siakad_security_test', success=False).returncode != 0, 'DB itself protects graded schedule')
    check(sql("INSERT INTO krs_mhs (kode_prodi,id_jadwal,nim_npm,id_thn_akademik) VALUES ('P1',1,'S1',1);", 'siakad_security_test', success=False).returncode != 0, 'DB duplicate constraint')
    check(sql("INSERT INTO krs_mhs (kode_prodi,id_jadwal,nim_npm,id_thn_akademik) VALUES ('P2',1,'S3',1);", 'siakad_security_test', success=False).returncode != 0, 'DB schedule scope constraint')
    sql('CREATE DATABASE siakad_migration_dirty_test CHARACTER SET utf8mb4; USE siakad_migration_dirty_test;\n' + schema)
    sql("SET foreign_key_checks=0; INSERT INTO krs_mhs VALUES (100,'P1',1,'S1',1),(101,'P1',1,'S1',1);", 'siakad_migration_dirty_test')
    check(sql(migration, 'siakad_migration_dirty_test', success=False).returncode != 0, 'dirty migration rejected')
    check(sql('SELECT COUNT(*) FROM krs_mhs;', 'siakad_migration_dirty_test').stdout.strip() == '2', 'dirty migration preserves records')
    check(sql("SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA='siakad_migration_dirty_test' AND INDEX_NAME='uq_krs_peserta_jadwal';").stdout.strip() == '0', 'preflight before schema mutation')

    # Make a synthetic application copy. Real db_config.php is never read/copied.
    fixture = temp_root / 'web'
    for folder in ('config', 'pages', 'template'):
        for file in (ROOT / folder).rglob('*.php'):
            if file.name == 'db_config.php':
                continue
            target = fixture / file.relative_to(ROOT)
            target.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(file, target)
    (fixture / 'config/db_config.php').write_text(f"<?php return ['host'=>'127.0.0.1:{mysql_port}','user'=>'root','password'=>'','database'=>'siakad_security_test'];", encoding='utf-8')
    if args.ui:
        shutil.copytree(ROOT / 'dist', fixture / 'dist')
        (fixture / 'assets').mkdir()
        for asset in ('siakad.css','academic.js','form-layout.js','ui-ux.js'):
            shutil.copy2(ROOT / 'assets' / asset, fixture / 'assets' / asset)
    # PHP's extensionless routes need a router in this temporary CLI server.
    router = fixture / 'router.php'
    router.write_text("""<?php
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
if (str_starts_with($path,'/config/') || str_starts_with($path,'/database/')) { http_response_code(403); exit; }
if (preg_match('/\\.(css|js|woff2?|ttf|png|jpg|svg)$/i',$path) && is_file(__DIR__.$path)) return false;
$target=realpath(__DIR__.$path.'.php')?:realpath(__DIR__.$path);
if (!$target || !str_starts_with($target,__DIR__.DIRECTORY_SEPARATOR) || !is_file($target)) { http_response_code(404); exit; }
$_SERVER['SCRIPT_FILENAME']=$target;
chdir(dirname($target)); require $target;
""", encoding='utf-8')
    web_log = open(temp_root / 'php.log', 'w', encoding='utf-8')
    log_handles.append(web_log)
    web = subprocess.Popen([PHP, '-S', f'127.0.0.1:{web_port}', '-t', str(fixture), str(router)], stdout=web_log, stderr=web_log, creationflags=flags)
    for _ in range(100):
        try:
            with socket.create_connection(('127.0.0.1', web_port), timeout=0.2):
                break
        except OSError:
            time.sleep(0.05)
    base = f'http://127.0.0.1:{web_port}'
    def browser():
        return urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()), NoRedirect())
    def request(client, path, data=None):
        payload = urllib.parse.urlencode(data, doseq=True).encode() if data is not None else None
        try:
            response = client.open(urllib.request.Request(base+path, data=payload), timeout=10)
        except urllib.error.HTTPError as error:
            response = error
        return response.code, response.read().decode('utf-8', errors='replace'), response.headers
    def login(name, password, role):
        client = browser()
        status, html, _ = request(client, '/pages/login')
        check(status == 200, 'login page renders')
        token = re.search(r'name="csrf-token" content="([^"]+)"', html)[1]
        status, _, headers = request(client, '/pages/login', {'masuk':'1','username':name,'password':password,'level':role,'csrf_token':token})
        check(status == 302 and headers.get('Location') == 'dashboard', role+' login redirect')
        status, html, _ = request(client, '/pages/dashboard')
        check(status == 200, role+' dashboard renders')
        token = re.search(r'name="csrf-token" content="([^"]+)"', html)[1]
        return client, token
    anonymous = browser()
    check(request(anonymous, '/pages/login', {'masuk':'1','username':'S1','password':'password123','level':'mhs'})[0] == 403, 'login CSRF required')
    check(request(anonymous, '/pages/cetak/transkip')[0] == 302, 'anonymous transcript redirects')
    student, student_token = login('S1', 'password123', 'mhs')
    check(request(student, '/pages/akun_admin')[0] == 403, 'student admin page denied')
    check(request(student, '/pages/get_input_nilai?qwe=1')[0] == 403, 'student grade endpoint denied')
    check(request(student, '/pages/krs?aksi=hapus&id_krs=1')[0] == 405, 'GET deletion denied')
    check(request(student, '/pages/ambil_jadwal?qwe=1', {'simpan':'1','pilih[]':['2']})[0] == 403, 'POST CSRF required')
    check(request(student, '/pages/ambil_jadwal?qwe=1', {'simpan':'1','pilih[]':['5'],'csrf_token':student_token})[0] == 422, 'cross prodi KRS denied')
    check(request(student, '/pages/ambil_jadwal?qwe=1', {'simpan':'1','pilih[]':['2'],'csrf_token':student_token})[0] == 302, 'valid KRS HTTP POST')
    check(request(student, '/pages/krs?qwe=1')[0] == 200, 'KRS renders')
    check(request(student, '/pages/cetak/transkip')[0] == 200, 'own transcript renders')
    check(request(student, '/pages/ambil_jadwal?qwe=1', {'aksi':'hapus','id':'1','csrf_token':student_token})[0] == 422, 'student cannot delete teaching schedule')
    prodi, prodi_token = login('P1', 'password123', 'Jurusan/Prodi')
    check(request(prodi, '/pages/get_input_nilai?qwe=5')[0] == 403, 'cross prodi grade read denied')
    check(request(prodi, '/pages/get_input_nilai?qwe=1')[0] == 200, 'own prodi grade page renders')
    check(request(prodi, '/pages/get_input_nilai?qwe=1', {'simpan_nilai':'1','nim_npm[]':['S1'],'nilai_uas[]':['90'],'csrf_token':prodi_token})[0] == 302, 'authorized grade POST')
    check(request(prodi, '/pages/get_input_nilai?qwe=1', {'simpan_nilai':'1','nim_npm[]':['S2'],'nilai_uas[]':['90'],'csrf_token':prodi_token})[0] == 422, 'grade POST nonparticipant denied')
    check(request(prodi, '/pages/buat_jadwal?qwe=1', {'aksi':'hapus','id':'1','csrf_token':prodi_token})[0] == 422, 'used schedule delete denied')
    lecturer, lecturer_token = login('D1', 'resetpassword', 'dosen')
    check(request(lecturer, '/pages/get_input_nilai?qwe=1')[0] == 200, 'lecturer class grade page')
    admin, admin_token = login('admin', 'password123', 'admin')
    check(request(admin, '/pages/akun_admin')[0] == 200, 'admin account management allowed')
    check(request(admin, '/pages/mhs')[0] == 200, 'admin master student renders')
    check(request(admin, '/pages/mhs', {'aksi':'hapus','nim_npm':'S1','csrf_token':admin_token})[0] == 422, 'master student history protected')
    check(request(admin, '/pages/login3')[0] == 302, 'legacy login redirects to single login')
    if args.ui:
        sql("UPDATE tbl_hari SET nama_hari='Senin' WHERE id_hari=1; UPDATE tbl_hari SET nama_hari='Selasa' WHERE id_hari=2; UPDATE jadwal_mengajar SET id_hari=2 WHERE id_jadwal=3;", 'siakad_security_test')
        # More than one page of unrelated synthetic master courses exercises the real AJAX table.
        for number in range(20):
            sql(f"INSERT INTO mata_kuliah VALUES ('UX{number:02d}','Mata kuliah uji {number:02d}',3,'{2 if number % 2 else 4}MN',1);", 'siakad_security_test')
        status, listing, _ = request(admin, '/pages/search_matkul.php', {'query':'UX','page':'2','size':'15'})
        check(status == 200 and 'data-total="20"' in listing and 'data-page="2"' in listing and listing.count('id="offcanvasEndUX') == 5, 'server returns only requested course page')
        status, listing, _ = request(admin, '/pages/search_matkul.php', {'query':'UX00','filters[Semester]':'2MN'})
        check(status == 200 and 'data-total="0"' in listing, 'server combines search OR clauses with semester filter')
        status, listing, _ = request(admin, '/pages/search_matkul.php', {'query':'UX','page':'999','size':'999'})
        check(status == 200 and 'data-size="15"' in listing and 'data-page="2"' in listing, 'server bounds page size and page number')
        check(request(student, '/pages/search_matkul.php', {'page':'1'})[0] == 403, 'server pagination retains role guard')
        status, listing, _ = request(prodi, '/pages/jurusan_has_mhs?list_search=S2')
        check(status == 200 and '"total":1' in listing, 'prodi server list search applies within role scope')
        ui = subprocess.run(['node', str(ROOT / 'tests/ui_smoke.cjs'), base, str(ROOT / 'tests/artifacts/ui')], capture_output=True, text=True, creationflags=flags)
        if ui.returncode:
            raise RuntimeError(ui.stdout + ui.stderr)
        print(ui.stdout.strip())
    web_log.flush()
    logs = (temp_root / 'php.log').read_text(encoding='utf-8', errors='replace')
    check('Undefined variable $sk_attention' not in logs, 'dashboard task summaries initialized')
    check('Fatal error' not in logs and 'Uncaught' not in logs, 'no fatal PHP errors in tested routes')
    check('Undefined variable $tampil_dosen' not in logs and 'Undefined variable $tampil_mhs' not in logs, 'legacy pages receive header profile data')
    print(f'PASS: {checks} HTTP and migration checks')
finally:
    if web:
        web.terminate()
        web.wait(timeout=10)
    if server:
        subprocess.run([str(MYSQL / 'mysqladmin.exe'), '--host=127.0.0.1', f'--port={mysql_port}', '--user=root', 'shutdown'], capture_output=True, creationflags=flags)
        try:
            server.wait(timeout=10)
        except subprocess.TimeoutExpired:
            server.terminate()
            server.wait(timeout=10)
    for handle in log_handles:
        handle.close()
    # Delete only the temporary directory created by this run.
    if temp_root.parent == Path(tempfile.gettempdir()).resolve() and temp_root.name.startswith('siakad-security-'):
        shutil.rmtree(temp_root)
