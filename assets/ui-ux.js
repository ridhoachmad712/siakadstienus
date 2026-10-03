(function () {
  'use strict';
  const menu = document.getElementById('navbar-menu');
  const toggle = document.querySelector('.sk-menu-toggle');
  const mobile = () => window.innerWidth < 1024;
  const closeMenu = () => { if (menu && menu.classList.contains('show') && toggle) toggle.click(); };
  if (menu && toggle) {
    menu.addEventListener('shown.bs.collapse', () => { if (mobile()) menu.querySelector('a,button')?.focus(); });
    menu.addEventListener('hidden.bs.collapse', () => { if (mobile()) toggle.focus(); });
    document.addEventListener('keydown', event => {
      if (event.key==='Escape' && mobile() && menu.classList.contains('show')) { event.preventDefault(); closeMenu(); }
    });
    document.addEventListener('click', event => {
      if (mobile() && menu.classList.contains('show') && !menu.contains(event.target) && !toggle.contains(event.target)) closeMenu();
    });
  }
  let serial = 0;
  function fieldError(field) {
    if (!field.willValidate) return;
    let error = document.getElementById(field.dataset.skError);
    if (!error) {
      error = document.createElement('small'); error.id = 'sk-error-' + (++serial); error.className = 'sk-field-error';
      field.dataset.skError = error.id; field.insertAdjacentElement('afterend', error);
      field.setAttribute('aria-describedby', [field.getAttribute('aria-describedby'), error.id].filter(Boolean).join(' '));
    }
    field.setAttribute('aria-invalid', 'true');
    error.textContent = field.validity.valueMissing ? 'Kolom ini wajib diisi.' : field.validity.typeMismatch ? 'Masukkan format yang sesuai, misalnya alamat email yang valid.' : field.validationMessage;
    error.hidden = false;
  }
  document.addEventListener('invalid', event => fieldError(event.target), true);
  function clearError(event) {
    const field = event.target;
    if (!field.dataset.skError) return;
    if (field.validity.valid) { field.removeAttribute('aria-invalid'); document.getElementById(field.dataset.skError).hidden = true; }
    else fieldError(field);
  }
  document.addEventListener('input', clearError); document.addEventListener('change', clearError);
  function enhanceForms() {
    document.querySelectorAll('form input:not([type=hidden]),form select,form textarea').forEach(field => {
      if (field.dataset.skLabel) return;
      field.dataset.skLabel = '1';
      if (!field.id || document.getElementById(field.id) !== field) field.id = 'sk-field-' + (++serial);
      const parent = field.closest('.mb-3') || field.parentElement;
      const label = parent.querySelector('label.form-label, label:not(.form-check-label)');
      if (label) label.htmlFor = field.id;
      if (field.required && label && !label.querySelector('.sk-required')) {
        const mark = document.createElement('span'); mark.className = 'sk-required'; mark.textContent = '*'; mark.setAttribute('aria-label','wajib diisi'); label.append(mark);
      }
    });
    document.querySelectorAll('.modal form button[type=submit],.offcanvas form button[type=submit],.modal form input[type=submit]').forEach(button=>{
      if (button.classList.contains('btn-danger') || button.classList.contains('btn-red') || button.closest('[id*=danger]')) return;
      button.classList.remove('btn-success','btn-info','btn-green'); button.classList.add('btn','btn-primary');
    });
    document.querySelectorAll('.table a:not([aria-label])').forEach(link=>{
      if (link.textContent.trim()) return;
      const action=link.matches('[data-bs-toggle=offcanvas]')?'Edit data':(link.dataset.bsTarget||link.getAttribute('href')||'').includes('danger')?'Hapus data':'Lihat detail';
      link.setAttribute('aria-label',action); link.title=action;
    });
    document.querySelectorAll('.alert:not([data-sk-alert])').forEach(el => { el.dataset.skAlert='1'; el.setAttribute('role',el.matches('.alert-danger,.alert-warning')?'alert':'status'); });
    document.querySelectorAll('.sk-status:not([data-sk-status])').forEach(el => {
      el.dataset.skStatus='1'; const value=el.textContent.toLowerCase().trim();
      const type = /perlu ditinjau|belum diatur|belum dinilai|mengulang|semester atas/.test(value) ? 'warning' : /ditutup|terkunci|nonaktif|belum dibuka/.test(value) ? 'neutral' : /dibuka|^aktif$|^lulus$/.test(value) ? 'success' : 'neutral';
      el.classList.add('sk-status-'+type);
    });
  }
  const route = location.pathname.split('/').pop().replace(/\.php$/, '');
  const masterRoutes = new Set(['mhs','dosen','mata_kuliah','jurusan','fakultas','ruangan','grade','thn_akademik','akun_admin','akun_jurusan','akun_dosen','akun_mhs','jurusan_has_mhs','jurusan_has_dosen','jurusan_has_matkul','dosen_has_mhs','krs_mhs','khs_mhs','transkip_mhs']);
  function enhanceTables() {
    if (!masterRoutes.has(route)) return;
    document.querySelectorAll('table').forEach(table => {
      if (table.dataset.skTable || table.closest('td,form,.modal,.offcanvas') || !table.tHead || !table.tBodies.length) return;
      const headers = Array.from(table.tHead.rows[0].cells).map(cell => cell.textContent.trim());
      const rows = Array.from(table.tBodies[0].rows).filter(row => row.cells.length === headers.length && !row.querySelector('td[colspan]'));
      if (!rows.length) return;
      table.dataset.skTable='1';
      let host=table.parentElement.matches('.table-responsive')?table.parentElement:null;
      if (!host) { host=document.createElement('div'); host.className='table-responsive sk-managed-table'; table.before(host); host.append(table); }
      const tools = document.createElement('div'); tools.className='sk-table-tools'; host.before(tools);
      const filters = [];
      function select(label, values) {
        const el=document.createElement('select'); el.className='form-select form-select-sm'; el.setAttribute('aria-label',label);
        el.add(new Option(label,'')); values.forEach(value=>el.add(new Option(value,value))); tools.append(el); return el;
      }
      const actionIndex=headers.findIndex(h=>/aksi|opsi|pilihan/i.test(h));
      const texts=rows.map(row=>Array.from(row.cells).map((cell,index)=>index===actionIndex?'':cell.textContent.trim()));
      let search=null;
      if (!document.getElementById('search_text')) {
        search=document.createElement('input'); search.type='search'; search.className='form-control form-control-sm'; search.placeholder='Cari dalam tabel…'; search.setAttribute('aria-label','Cari dalam tabel'); tools.append(search);
      }
      headers.forEach((header,index)=>{
        if (!/^(status( mhs)?|tahun masuk|angkatan|semester|jenis mk|jenis)$/i.test(header)) return;
        const values=Array.from(new Set(texts.map(row=>row[index]).filter(Boolean))).sort();
        if (values.length>1) filters.push([index,select('Semua '+header.toLowerCase(),values)]);
      });
      if (headers.length>7) {
        const columns=document.createElement('details'); columns.className='sk-table-columns';
        const summary=document.createElement('summary'); summary.textContent='Atur kolom'; columns.append(summary);
        const options=document.createElement('div'); columns.append(options); tools.append(columns);
        headers.forEach((header,index)=>{
          const label=document.createElement('label'), checkbox=document.createElement('input'); checkbox.type='checkbox';
          const hide=/^(agama|alamat|ttl|email|jk)$/i.test(header); checkbox.checked=!hide;
          label.append(checkbox,document.createTextNode(header)); options.append(label);
          function applyColumn(){ table.tHead.rows[0].cells[index].classList.toggle('sk-column-hidden',!checkbox.checked); rows.forEach(row=>row.cells[index].classList.toggle('sk-column-hidden',!checkbox.checked)); }
          checkbox.addEventListener('change',applyColumn); applyColumn();
        });
      }
      const pager=document.createElement('div'); pager.className='sk-table-pager'; host.after(pager);
      const count=document.createElement('span'); count.setAttribute('aria-live','polite'); pager.append(count);
      const actions=document.createElement('div'); actions.className='sk-actions'; pager.append(actions);
      const size=document.createElement('select'); size.className='form-select form-select-sm'; size.setAttribute('aria-label','Jumlah baris per halaman'); [15,25,50].forEach(n=>size.add(new Option(n+' baris',n))); actions.append(size);
      const prev=document.createElement('button'), next=document.createElement('button');
      [prev,next].forEach(btn=>{btn.type='button';btn.className='btn btn-secondary btn-sm';actions.append(btn);}); prev.textContent='Sebelumnya'; next.textContent='Berikutnya';
      const empty=document.createElement('tr'), cell=document.createElement('td'); cell.colSpan=headers.length; cell.className='sk-empty-state'; cell.textContent='Tidak ada data yang sesuai dengan pencarian dan filter.'; empty.append(cell); table.tBodies[0].append(empty);
      let current=1;
      function draw() {
        const term=(search?.value||'').toLocaleLowerCase('id');
        const visible=rows.filter((row,index)=>(!term||texts[index].join(' ').toLocaleLowerCase('id').includes(term))&&filters.every(([column,el])=>!el.value||texts[index][column]===el.value));
        const total=visible.length, limit=Number(size.value), pages=Math.max(1,Math.ceil(total/limit)); current=Math.min(current,pages);
        rows.forEach(row=>row.hidden=true); visible.slice((current-1)*limit,current*limit).forEach(row=>row.hidden=false);
        empty.hidden=total>0; prev.disabled=current===1; next.disabled=current===pages;
        count.textContent=total ? `${(current-1)*limit+1}–${Math.min(current*limit,total)} dari ${total} data · Halaman ${current}/${pages}` : '0 data';
      }
      prev.addEventListener('click',()=>{current--;draw();}); next.addEventListener('click',()=>{current++;draw();});
      [size,search,...filters.map(item=>item[1])].filter(Boolean).forEach(el=>el.addEventListener(el===search?'input':'change',()=>{current=1;draw();})); draw();
    });
  }
  let scheduled=false;
  function scan() { scheduled=false; enhanceForms(); enhanceTables(); }
  scan();
  new MutationObserver(records=>{
    if (scheduled || !records.some(record=>Array.from(record.addedNodes).some(node=>node.nodeType===1 && (node.matches('table,form,.alert,.sk-status')||node.querySelector('table,form,.alert,.sk-status'))))) return;
    scheduled=true; requestAnimationFrame(scan);
  }).observe(document.body,{childList:true,subtree:true});
})();
