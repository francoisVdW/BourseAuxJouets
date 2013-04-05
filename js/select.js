function appendOption(idSel,text, val)
{
  var elOptNew = document.createElement('option');
  elOptNew.text = text;
  elOptNew.value = val;
  var theSel = $(idSel);
  if(!theSel) return;
  try {
    theSel.add(elOptNew, null); // standards compliant; doesn't work in IE
  }
  catch(ex) {
    theSel.add(elOptNew); // IE only
  }
}

function removeOption(idSel,all)
{
  var theSel = $(idSel);
  if(!theSel) return;
  if(!all){
    if (theSel.length > 0) theSel.remove(theSel.length - 1);
  } else {
    while(theSel.length) theSel.remove(theSel.length - 1);
  }
}

function getOption(idSel)
{
  var theSel = $(idSel);
  if(!theSel) return 0;
  return theSel.options[theSel.selectedIndex].value;
}