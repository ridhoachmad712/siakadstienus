/* Interaksi presentasi. Validasi dan penyimpanan tetap di server. */
(function () {
  var selection = document.getElementById('course-selection');
  if (selection) {
    function updateSelection() {
      var choices = Array.from(selection.querySelectorAll('input[name="pilih[]"]:checked'));
      var total = Number(selection.dataset.existingSks) + choices.reduce(function(sum,input){return sum+Number(input.dataset.sks);},0);
      var over = total > Number(selection.dataset.limit);
      document.getElementById('selected-sks').textContent = total;
      document.getElementById('selection-message').textContent = over ? 'Jumlah SKS melebihi batas pengambilan.' : (choices.length ? choices.length+' mata kuliah tambahan dipilih.' : 'Pilih mata kuliah untuk melihat jumlah SKS.');
      document.getElementById('save-selection').disabled = selection.dataset.open!=='1' || over || !choices.length;
    }
    selection.addEventListener('change',updateSelection);
    updateSelection();
  }
  var gradeForm = document.getElementById('grade-form');
  if (gradeForm) {
    var grades=JSON.parse(gradeForm.dataset.gradeConfig);
    function updateGrade(event) {
      if (event && event.target.matches('.sk-grade-input')) {
        var input=event.target, value=Number(input.value), match=null;
        if (input.value!=='' && Number.isFinite(value) && value>=0 && value<=100) grades.forEach(function(g){
          if (value>=Number(g.nilai_awal) && (Number(g.nilai_akhir)>=100 || value<Number(g.nilai_akhir)+1) && (!match || Number(g.nilai_awal)>Number(match.nilai_awal))) match=g;
        });
        input.closest('tr').querySelector('[data-grade-preview]').textContent=match?match.grade:'—';
      }
      var fields=Array.from(gradeForm.querySelectorAll('.sk-grade-input'));
      var valid=fields.filter(function(input){return input.value!==''&&input.validity.valid;}).length;
      document.getElementById('grade-progress').textContent=valid+' dari '+fields.length+' nilai terisi dan sesuai rentang.';
    }
    gradeForm.addEventListener('input',updateGrade);
    updateGrade();
    var dirty=false;
    gradeForm.addEventListener('input',function(){dirty=true;});
    gradeForm.addEventListener('submit',function(event){if(gradeForm.checkValidity()&&!event.defaultPrevented)dirty=false;});
    window.addEventListener('beforeunload',function(event){if(dirty){event.preventDefault();event.returnValue='';}});
  }
  var editor=document.getElementById('schedule-editor');
  if(editor){
    var start=editor.querySelector('[name="mulai_jam"]'),end=editor.querySelector('[name="sampai_jam"]');
    function checkTime(){end.setCustomValidity(start.value&&end.value&&end.value<=start.value?'Jam selesai harus setelah jam mulai.':'');}
    start.addEventListener('input',checkTime);end.addEventListener('input',checkTime);
  }
})();
