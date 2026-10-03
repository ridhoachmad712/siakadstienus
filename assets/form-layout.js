/* Kelompokkan kontrol yang sudah dirender PHP tanpa mengganti nama/handler. */
(function(){
  var counter=0;
  function enhance(form){
    if(form.dataset.layoutReady)return;
    var container=form.querySelector('.offcanvas-body');
    if(!container)return;
    var fields=Array.from(container.querySelectorAll('.mb-3')).filter(function(field){return field.querySelector('input[name],select[name],textarea[name]');});
    if(fields.length<6)return;
    form.dataset.layoutReady='1';
    document.body.classList.add('sk-long-form');
    var groups=[{title:'Identitas dan data pribadi',fields:[]},{title:'Informasi akademik',fields:[]},{title:'Kontak',fields:[]}];
    fields.forEach(function(field){
      var input=field.querySelector('input[name],select[name],textarea[name]'),name=input.name;
      var group=/email|alamat|telp/.test(name)?2:(/thn_masuk|lulusan|sekolah|status|kode_matkul|nama_matkul|sks|semester|jenis_mk/.test(name)?1:0);
      var label=field.querySelector('label');
      if(!input.id)input.id='master-field-'+(++counter);
      if(label)label.htmlFor=input.id;
      groups[group].fields.push(field);
    });
    var parent=fields[0].parentElement;
    groups.filter(function(group){return group.fields.length;}).forEach(function(group){
      var section=document.createElement('fieldset');section.className='sk-form-section';
      var legend=document.createElement('legend');legend.textContent=group.title;section.appendChild(legend);
      var grid=document.createElement('div');grid.className='sk-form-grid';section.appendChild(grid);
      group.fields.forEach(function(field){if(field.querySelector('textarea'))field.classList.add('sk-form-wide');grid.appendChild(field);});
      parent.appendChild(section);
    });
    form.querySelectorAll('button[type="submit"],input[type="submit"]').forEach(function(button){button.classList.add('btn-primary');button.classList.remove('btn-green','btn-info');});
  }
  function scan(){document.querySelectorAll('.offcanvas form').forEach(enhance);}
  scan();
  new MutationObserver(scan).observe(document.body,{childList:true,subtree:true});
})();
