(function () {
  'use strict';
  const menu = document.getElementById('navbar-menu');
  const toggle = document.querySelector('.sk-menu-toggle');
  const mobile = () => window.innerWidth < 1024;
  const closeMenu = () => { if (menu && menu.classList.contains('show') && toggle) toggle.click(); };
  if (menu && toggle) {
    menu.addEventListener('shown.bs.collapse', () => { if (mobile()) menu.querySelector('a,button')?.focus(); });
    menu.addEventListener('click', event=>{ if(mobile() && event.target.closest('a[href]:not([data-bs-toggle])')) closeMenu(); });
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
    for(let parent=field.parentElement;parent;parent=parent.parentElement) if(parent.tagName==='DETAILS') parent.open=true;
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
    document.querySelectorAll('a.btn-success,a.btn-green,button.btn-success').forEach(button=>{if(/tambah|simpan/i.test(button.textContent)){button.classList.remove('btn-success','btn-green');button.classList.add('btn-primary');}});
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
  function actionIndexFor(headers){return headers.findIndex(h=>/aksi|opsi|pilihan/i.test(h));}
  function enhanceTables() {
    if (!masterRoutes.has(route)) return;
    document.querySelectorAll('table').forEach(table => {
      if (table.dataset.skTable || table.closest('td,form,.modal,.offcanvas') || !table.tHead) return;
      if(!table.tBodies.length && document.getElementById('sk-static-pagination'))table.createTBody();
      if(!table.tBodies.length)return;
      const headers = Array.from(table.tHead.rows[0].cells).map(cell => cell.textContent.trim());
      const rows = Array.from(table.tBodies[0].rows).filter(row => row.cells.length === headers.length && !row.querySelector('td[colspan]'));
      if (!rows.length && !document.getElementById('sk-static-pagination')) return;
      table.dataset.skTable='1';
      table.classList.add('sk-master-cards');
      if(!rows.length && document.getElementById('sk-static-pagination')){const row=table.tBodies[0].insertRow(),cell=row.insertCell();cell.colSpan=headers.length;cell.className='sk-empty-state';cell.textContent='Tidak ada data yang sesuai.';}
      rows.forEach(row=>Array.from(row.cells).forEach((cell,index)=>{cell.dataset.label=headers[index]; if(index===actionIndexFor(headers))cell.classList.add('sk-cell-actions');if(/nama|^mata kuliah$/i.test(headers[index]))cell.classList.add('sk-cell-title');}));
      if(table.closest('.sk-server-results')) {
      if (headers.length>7) {
        const columns=document.createElement('details'); columns.className='sk-table-columns';
        const summary=document.createElement('summary'); summary.textContent='Atur kolom'; columns.append(summary);
        const options=document.createElement('div'); columns.append(options); table.before(columns);
        headers.forEach((header,index)=>{
          const label=document.createElement('label'), checkbox=document.createElement('input'); checkbox.type='checkbox';
          const hide=/^(agama|alamat|ttl|email|jk)$/i.test(header); checkbox.checked=!hide;
          label.append(checkbox,document.createTextNode(header)); options.append(label);
          function applyColumn(){ table.tHead.rows[0].cells[index].classList.toggle('sk-column-hidden',!checkbox.checked); rows.forEach(row=>row.cells[index].classList.toggle('sk-column-hidden',!checkbox.checked)); }
          checkbox.addEventListener('change',applyColumn); applyColumn();
        });
      }
        return;
      }
      let host=table.parentElement.matches('.table-responsive')?table.parentElement:null;
      if (!host) { host=document.createElement('div'); host.className='table-responsive sk-managed-table'; table.before(host); host.append(table); }
      if(document.getElementById('sk-static-pagination')) return;
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
  function mobileForms(){
    document.querySelectorAll('.sk-identity:not([data-mobile-identity])').forEach(identity=>{
      identity.dataset.mobileIdentity='1'; const children=Array.from(identity.children);
      if(children.length<3)return;
      const details=document.createElement('details'); details.className='sk-identity-details';
      const summary=document.createElement('summary'); summary.textContent='Detail akademik'; details.append(summary);
      children.slice(2).forEach(child=>details.append(child)); identity.append(details);
    });
    document.querySelectorAll('.sk-form-section:not([data-mobile-section])').forEach((section,index)=>{
      section.dataset.mobileSection='1'; const title=section.querySelector('legend,h3'); if(!title)return;
      const details=document.createElement('details'); details.className='sk-form-disclosure'; details.open=!mobile()||section===section.closest('form')?.querySelector('.sk-form-section');
      const summary=document.createElement('summary'); summary.textContent=title.textContent; section.before(details);details.append(summary,section);
    });
    document.querySelectorAll('.offcanvas form:not([data-mobile-footer])').forEach(form=>{
      form.dataset.mobileFooter='1';const body=form.querySelector('.offcanvas-body');if(!body)return;
      const buttons=Array.from(body.querySelectorAll('button[type=submit],input[type=submit]')); if(!buttons.length)return;
      const footer=document.createElement('div');footer.className='sk-form-footer';
      buttons.forEach(button=>footer.append(button));
      body.querySelectorAll('button[data-bs-dismiss=offcanvas]').forEach(button=>footer.append(button));form.append(footer);
    });
    const selection=document.getElementById('course-selection');
    if(selection && !document.getElementById('sk-krs-dock')){
      const summary=document.querySelector('.sk-study-summary'),dock=document.createElement('div');dock.id='sk-krs-dock';dock.className='sk-mobile-save sk-krs-dock';
      ['.sk-summary-value','#selection-message','#save-selection'].forEach(selector=>{const el=summary.querySelector(selector);if(el)dock.append(el);});summary.append(dock);
    }
    document.querySelectorAll('.sk-save-bar').forEach(el=>el.classList.add('sk-mobile-save'));
    if(document.querySelector('.sk-mobile-save'))document.body.classList.add('sk-has-mobile-save');
    document.querySelectorAll('#course-selection tr').forEach(row=>{
      const checkbox=row.querySelector('input[type=checkbox]');if(!checkbox||row.dataset.skChoice)return;row.dataset.skChoice='1';
      const label=document.createElement('label');label.className='sk-course-choice';label.htmlFor=checkbox.id;label.append(checkbox);row.cells[0].append(label);
    });
  }
  if(window.visualViewport){
    function viewport(){document.documentElement.style.setProperty('--sk-keyboard-inset',Math.max(0,innerHeight-window.visualViewport.height-window.visualViewport.offsetTop)+'px');}
    visualViewport.addEventListener('resize',viewport);visualViewport.addEventListener('scroll',viewport);viewport();
  }
  function staticTables(){
    const metadata=document.getElementById('sk-static-pagination');if(!metadata)return;
    const table=document.querySelector('table[data-sk-table]');if(!table)return;
    const data=JSON.parse(metadata.textContent),host=table.closest('.table-responsive')||table;
    const form=document.createElement('form');form.method='get';form.className='sk-table-tools';
    const url=new URL(location.href);url.searchParams.forEach((value,key)=>{if(['page','size','list_search'].includes(key))return;const field=document.createElement('input');field.type='hidden';field.name=key;field.value=value;form.append(field);});
    const search=document.createElement('input');search.type='search';search.name='list_search';search.value=data.search;search.className='form-control';search.placeholder='Cari nama atau kode…';search.setAttribute('aria-label','Cari dalam daftar');form.append(search);
    const submit=document.createElement('button');submit.type='submit';submit.className='btn btn-primary';submit.textContent='Cari';form.append(submit);if(!data.hideSearch)host.before(form);
    const pager=document.createElement('div');pager.className='sk-table-pager';host.after(pager);
    const count=document.createElement('span');const pages=Math.max(1,Math.ceil(data.total/data.size));count.textContent=data.total?`${(data.page-1)*data.size+1}–${Math.min(data.page*data.size,data.total)} dari ${data.total} data · Halaman ${data.page}/${pages}`:'0 data';pager.append(count);
    const actions=document.createElement('div');actions.className='sk-actions';pager.append(actions);
    function navigate(page,size){const target=new URL(location.href);target.searchParams.set('page',page);target.searchParams.set('size',size);location.assign(target.href);}
    const size=document.createElement('select');size.className='form-select';size.setAttribute('aria-label','Jumlah baris per halaman');[15,25,50].forEach(n=>size.add(new Option(n+' baris',n)));size.value=data.size;size.onchange=()=>navigate(1,size.value);actions.append(size);
    ['Sebelumnya','Berikutnya'].forEach((label,index)=>{const button=document.createElement('button');button.type='button';button.className='btn btn-secondary';button.textContent=label;button.disabled=index===0?data.page===1:data.page===pages;button.onclick=()=>navigate(data.page+(index===0?-1:1),data.size);actions.append(button);});
  }
  function serverTables(){
    const endpoints={mhs:['search_mhs.php','data-mhs'],dosen:['search_dosen.php','data-dosen'],mata_kuliah:['search_matkul.php','data-matkul'],jurusan:['search_jurusan.php','data-jurusan'],fakultas:['search_fakultas.php','data-fakultas']};
    if(!endpoints[route])return;
    const [endpoint,id]=endpoints[route],host=document.getElementById(id),search=document.getElementById('search_text');if(!host)return;
    let page=1,size=15,filters={},request=null,timer;
    const tools=document.createElement('div');tools.className='sk-table-tools';host.before(tools);
    const pager=document.createElement('div');pager.className='sk-table-pager';host.after(pager);
    const message=document.createElement('span');message.setAttribute('aria-live','polite');pager.append(message);
    const actions=document.createElement('div');actions.className='sk-actions';pager.append(actions);
    const select=document.createElement('select');select.className='form-select form-select-sm';select.setAttribute('aria-label','Jumlah baris per halaman');[15,25,50].forEach(n=>select.add(new Option(n+' baris',n)));actions.append(select);
    const prev=document.createElement('button'),next=document.createElement('button');[prev,next].forEach(btn=>{btn.type='button';btn.className='btn btn-secondary btn-sm';actions.append(btn);});prev.textContent='Sebelumnya';next.textContent='Berikutnya';
    async function load(){
      request?.abort();const controller=new AbortController();request=controller;host.setAttribute('aria-busy','true');prev.disabled=true;next.disabled=true;message.textContent='Memuat data…';
      const body=new URLSearchParams({query:search?.value||'',page:String(page),size:String(size)});Object.entries(filters).forEach(([label,value])=>body.set('filters['+label+']',value));
      try{
        const response=await fetch(endpoint,{method:'POST',body,signal:controller.signal});if(!response.ok)throw new Error('load');
        const html=await response.text();if(controller!==request)return;host.innerHTML=html;
        const data=host.querySelector('.sk-server-results');if(!data)throw new Error('metadata');
        const total=Number(data.dataset.total);page=Number(data.dataset.page);const pages=Math.max(1,Math.ceil(total/size));
        message.textContent=total?`${(page-1)*size+1}–${Math.min(page*size,total)} dari ${total} data · Halaman ${page}/${pages}`:'0 data';prev.disabled=page===1;next.disabled=page===pages;
        tools.replaceChildren();Object.entries(JSON.parse(data.dataset.filters)).forEach(([label,values])=>{
          if(values.length<2 && !filters[label])return;
          const el=document.createElement('select');el.className='form-select form-select-sm';el.setAttribute('aria-label','Semua '+label.toLowerCase());el.add(new Option('Semua '+label.toLowerCase(),''));values.forEach(value=>el.add(new Option(value,value)));el.value=filters[label]||'';tools.append(el);
          el.addEventListener('change',()=>{filters[label]=el.value;page=1;load();});
        });scan();
      }catch(error){if(error.name!=='AbortError'){message.textContent='Data belum berhasil dimuat. Coba lagi.';const retry=document.createElement('button');retry.type='button';retry.className='btn btn-secondary';retry.textContent='Coba lagi';retry.onclick=load;host.replaceChildren(retry);}}
      finally{if(controller===request)host.removeAttribute('aria-busy');}
    }
    prev.onclick=()=>{page--;load();};next.onclick=()=>{page++;load();};select.onchange=()=>{size=Number(select.value);page=1;load();};
    search?.addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(()=>{page=1;filters={};load();},250);});load();
  }
  let scheduled=false;
  function scan() { scheduled=false; enhanceForms(); enhanceTables(); mobileForms(); }
  scan();
  serverTables();
  staticTables();
  new MutationObserver(records=>{
    if (scheduled || !records.some(record=>Array.from(record.addedNodes).some(node=>node.nodeType===1 && (node.matches('table,form,.alert,.sk-status')||node.querySelector('table,form,.alert,.sk-status'))))) return;
    scheduled=true; requestAnimationFrame(scan);
  }).observe(document.body,{childList:true,subtree:true});
})();
