// Only the temporary fixture launched by run_security_tests.py.
const { chromium } = require('playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const base = process.argv[2];
assert.match(base, /^http:\/\/127\.0\.0\.1:\d+$/);
const shots = process.argv[3];
fs.mkdirSync(shots, { recursive: true });
(async () => {
  const browser = await chromium.launch({ channel: 'msedge', headless: true });
  let checks = 0;
  try {
    for (const [role, username, password, route] of [
      ['admin','admin','password123','mhs'],
      ['Jurusan/Prodi','P1','password123','buat_jadwal?qwe=1'],
      ['dosen','D1','resetpassword','input_nilai_dosen?qwe=1'],
      ['mhs','S1','password123','krs?qwe=1']
    ]) {
      const context = await browser.newContext({ viewport: { width: 1366, height: 900 } });
      const page = await context.newPage();
      const errors = [];
      page.on('pageerror', e => errors.push(e.message));
      await page.goto(base+'/pages/login');
      await page.locator('#username').fill(username);
      await page.locator('#myInput').fill(password);
      await page.locator('#level').selectOption(role);
      await Promise.all([page.waitForURL('**/pages/dashboard'),page.getByRole('button',{name:'Masuk',exact:true}).click()]);
      assert.equal(await page.locator('.sk-metric').count(), 3); checks++;
      assert.equal(await page.locator('form').count(), 0); checks++;
      assert.equal(await page.locator('a[aria-current="page"]').textContent(),'Beranda'); checks++;
      assert.equal(await page.evaluate(()=>getComputedStyle(document.documentElement).getPropertyValue('--sk-primary').trim()),'#7b203a'); checks++;
      assert.equal(await page.locator('.sk-navigation .nav-item.active').evaluate(el=>getComputedStyle(el,'::after').borderBottomColor),'rgb(123, 32, 58)'); checks++;
      await page.screenshot({ path: path.join(shots,username+'-desktop.png'), fullPage:true });
      await page.setViewportSize({ width:360,height:800 });
      assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth),true); checks++;
      await page.getByRole('button',{name:'Buka menu navigasi'}).click();
      await page.locator('#navbar-menu.show').waitFor(); checks++;
      assert.equal(await page.locator('.sk-navigation').isVisible(),true); checks++;
      await page.screenshot({ path:path.join(shots,username+'-mobile.png'),fullPage:true });
      await page.setViewportSize({width:1366,height:900});
      if (role==='mhs'||role==='dosen') {
        await page.locator('.sk-account').click();
        await page.getByRole('link',{name:'Profil saya',exact:true}).last().click();
        await page.locator('.sk-profile-heading').waitFor();
        assert.ok(await page.locator('form').count()>0); checks++;
        assert.equal(await page.locator('.sk-metric').count(),0); checks++;
        await page.screenshot({path:path.join(shots,username+'-profile.png'),fullPage:true});
        await page.setViewportSize({width:360,height:800});
        assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth),true); checks++;
        await page.screenshot({path:path.join(shots,username+'-profile-mobile.png'),fullPage:true});
        await page.setViewportSize({width:1366,height:900});
      } else {
        await page.locator('.sk-navigation button').first().click();
        assert.equal(await page.locator('.sk-navigation .dropdown-menu.show').count(),1); checks++;
      }
      await page.goto(base+'/pages/'+route);
      assert.equal(await page.locator('.sk-header').count(),1); checks++;
      assert.equal(await page.locator('a[aria-current="page"]').count(),1); checks++;
      await page.setViewportSize({width:360,height:800});
      assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth),true); checks++;
      await page.screenshot({path:path.join(shots,username+'-detail-mobile.png'),fullPage:true});
      const academicRoutes = {
        'admin': [],
        'Jurusan/Prodi': ['rekap_jadwal?qwe=1','input_nilai?qwe=1','krs_mhs?qwe=1','khs_mhs?qwe=1','transkip_mhs','sks_mhs?qwe=1','mhs_krs?qwe=1&qaz=S1','mhs_khs?qwe=1&qaz=S1','pengaturan_krs?qwe=1&qaz=S2'],
        'dosen': ['jadwal_mengajar?qwe=1','get_input_nilai?qwe=1'],
        'mhs': ['khs?qwe=1','jadwal_kuliah?qwe=1','transkip','ambil_jadwal?qwe=1']
      };
      for (const url of academicRoutes[role]) {
        await page.setViewportSize({width:1366,height:900});
        const response=await page.goto(base+'/pages/'+url);
        assert.equal(response.status(),200); checks++;
        assert.equal(await page.locator('.sk-academic').count(),1); checks++;
        if(role==='mhs'&&url==='khs?qwe=1') {
          assert.equal(await page.locator('.sk-inline-summary strong').last().textContent(),'2,00'); checks++;
        }
        await page.screenshot({path:path.join(shots,username+'-'+url.split('?')[0]+'-desktop.png'),fullPage:true});
        await page.setViewportSize({width:360,height:800});
        assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth),true,url+' mobile width'); checks++;
        await page.screenshot({path:path.join(shots,username+'-'+url.split('?')[0]+'-mobile.png'),fullPage:true});
      }
      await page.setViewportSize({width:1366,height:900});
      if(role==='admin') {
        await page.goto(base+'/pages/mhs');
        await page.locator('[data-bs-toggle="offcanvas"]').first().click();
        await page.locator('.offcanvas.show').waitFor();
        await page.waitForFunction(()=>Math.abs(document.querySelector('.offcanvas.show').getBoundingClientRect().x)<1);
        assert.equal(await page.locator('.offcanvas.show fieldset').count(),3); checks++;
        assert.ok(await page.locator('.offcanvas.show label[for]').count()>=10); checks++;
        await page.screenshot({path:path.join(shots,username+'-long-form.png'),fullPage:true});
      }
      if(role==='Jurusan/Prodi') {
        await page.goto(base+'/pages/pengaturan_krs?qwe=1&qaz=S2');
        await page.locator('#policy-semester').selectOption('3');
        await page.locator('#semester-reason').fill('Penyesuaian semester mahasiswa uji');
        await Promise.all([page.waitForURL('**/pengaturan_krs?qwe=1&qaz=S2&saved=1'),page.getByRole('button',{name:'Simpan semester',exact:true}).click()]);
        assert.equal(await page.locator('#policy-semester').inputValue(),'3'); checks++;
        await page.locator('#policy-semester').selectOption('');
        await page.locator('#semester-reason').fill('Kembali ke semester otomatis');
        await Promise.all([page.waitForResponse(r=>r.request().method()==='POST'&&r.url().includes('pengaturan_krs')&&r.status()===302),page.getByRole('button',{name:'Simpan semester',exact:true}).click()]);
        await page.waitForLoadState('networkidle');
        await page.locator('#permit-course').selectOption('M6');
        await page.locator('#permit-kind').selectOption('semester_atas');
        await page.locator('#permit-reason').fill('Izin semester atas dari program studi');
        await Promise.all([page.waitForResponse(r=>r.request().method()==='POST'&&r.url().includes('pengaturan_krs')&&r.status()===302),page.getByRole('button',{name:'Berikan izin',exact:true}).click()]);
        await page.waitForLoadState('networkidle');
        assert.equal(await page.locator('button').filter({hasText:'Cabut izin'}).count(),1); checks++;
        await page.locator('#revoke-reason-0').fill('Pencabutan izin pengujian');
        await Promise.all([page.waitForResponse(r=>r.request().method()==='POST'&&r.url().includes('pengaturan_krs')&&r.status()===302),page.getByRole('button',{name:'Cabut izin',exact:true}).click()]);
        await page.waitForLoadState('networkidle');
        assert.equal(await page.getByRole('button',{name:'Cabut izin',exact:true}).count(),0); checks++;
        await page.goto(base+'/pages/buat_jadwal?qwe=1');
        await page.getByRole('button',{name:'Tambah jadwal',exact:true}).click();
        await page.locator('#schedule-editor.show').waitFor();
        await page.locator('#schedule-start').fill('10:00');
        await page.locator('#schedule-end').fill('09:00');
        assert.equal(await page.locator('#schedule-end').evaluate(el=>el.validity.valid),false); checks++;
        await page.locator('#schedule-end').fill('11:00');
        assert.equal(await page.locator('#schedule-end').evaluate(el=>el.validity.valid),true); checks++;
        await page.screenshot({path:path.join(shots,username+'-schedule-form.png'),fullPage:true});
        await page.goto(base+'/pages/sks_mhs?qwe=1');
        const limits=page.locator('input[name="sks[]"]');
        for(let i=0;i<await limits.count();i++)await limits.nth(i).fill(i===0?'9':'24');
        await Promise.all([page.waitForURL('**/sks_mhs?qwe=1&saved=1&angkatan='),page.getByRole('button',{name:'Simpan batas SKS',exact:true}).click()]);
        assert.equal(await page.locator('input[name="sks[]"]').first().inputValue(),'9'); checks++;
      }
      if(role==='dosen') {
        await page.goto(base+'/pages/get_input_nilai?qwe=1');
        await page.locator('.sk-grade-input').first().fill('79.5');
        assert.equal(await page.locator('[data-grade-preview]').first().textContent(),'B+'); checks++;
        await page.locator('.sk-grade-input').first().fill('101');
        assert.equal(await page.locator('#grade-form').evaluate(el=>el.checkValidity()),false); checks++;
        await page.locator('.sk-grade-input').first().fill('85');
        await Promise.all([page.waitForResponse(response=>response.request().method()==='POST'&&response.url().includes('get_input_nilai')&&response.status()===302),page.getByRole('button',{name:'Simpan nilai',exact:true}).click()]);
        await page.waitForLoadState('networkidle');
        await page.reload();
        assert.equal(await page.locator('.sk-grade-input').first().inputValue(),'85'); checks++;
      }
      if(role==='mhs') {
        const denied=await page.goto(base+'/pages/pengaturan_krs');
        assert.equal(denied.status(),403); checks++;
        await page.goto(base+'/pages/ambil_jadwal?qwe=1');
        const choices=page.locator('input[name="pilih[]"]');
        await choices.nth(0).check(); await choices.nth(1).check();
        assert.ok(Number(await page.locator('#selected-sks').textContent())>9); checks++;
        assert.equal(await page.locator('#save-selection').isDisabled(),true); checks++;
        await choices.nth(0).uncheck(); await choices.nth(1).uncheck();
        await page.locator('input[name="pilih[]"][value="6"]').check();
        assert.equal(await page.locator('#save-selection').isDisabled(),false); checks++;
        await Promise.all([page.waitForURL('**/krs?qwe=1'),page.locator('#save-selection').click()]);
        assert.equal(await page.locator('#selected-sks').textContent(),'9'); checks++;
        for(const kind of ['krs','khs','transkip','jadwalkuliah']) {
          const response=await page.goto(base+'/pages/cetak/'+kind+'?qwe=1');
          assert.equal(response.status(),200); checks++;
          assert.equal(await page.locator('.sk-paper').count(),1); checks++;
          await page.emulateMedia({media:'print'});
          assert.equal(await page.locator('.sk-print-toolbar').isVisible(),false); checks++;
          await page.pdf({path:path.join(shots,kind+'.pdf'),format:'A4',preferCSSPageSize:true});
          await page.screenshot({path:path.join(shots,kind+'-print.png'),fullPage:true});
          await page.emulateMedia({media:'screen'});
          await page.setViewportSize({width:360,height:800});
          assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth),true,kind+' print preview mobile width'); checks++;
          await page.setViewportSize({width:1366,height:900});
        }
      }
      assert.deepEqual(errors,[]); checks++;
      await context.close();
    }
    console.log('PASS: '+checks+' browser checks (four roles, desktop/mobile, menu, profile)');
    // Copy only synthetic screenshots to the ignored local QA folder.
    const target=path.join(__dirname,'artifacts','ui');
    fs.mkdirSync(target,{recursive:true});
    for(const file of fs.readdirSync(shots)) fs.copyFileSync(path.join(shots,file),path.join(target,file));
  } finally { await browser.close(); }
})().catch(error=>{console.error(error);process.exitCode=1;});
