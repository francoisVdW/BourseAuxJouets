/* - - - - - - - - - - - - - - - - - - - - - - -
 JavaScript
 vendredi 3 juin 2005 12:00:36
 HAPedit 3.1.11.111
 $RCSfile: cchamp.js $ 
 $Revision: 2.3 $

 IMPORTANT need prototype.js included !
 - - - - - - - - - - - - - - - - - - - - - - - */
 
 
// Générales
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
function isDept(dept){
	var re = /^((\d{2})|(2[ABab])|(97[1245])|(999))$/
	var v = dept.strip();
  	if (v != "") {
    	if (!re.test(v) || (v>95 && v<99)) return false;
    	else return true;
  	}
  	else return true; // vide
}
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// from : http://tech.irt.org/articles/js049/index.htm
function isEmail() {
	if (this.search(/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/) != -1) return true;
	else return false;
}
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// This function accepts a string variable and verifies if
// it is a proper date or not.  It validates format
// matching either dd-mm-yyyy or dd/mm/yyyy. Then it checks
// to make sure the month has the proper number of days,
// based on which month it is.
//
// The function returns a formated string if a valid date, false if not.
function isDate() {
	var datePat = /^(\d{1,2})[\/|\- ](\d{1,2})[\/|\- ](\d{2,4})$/;
	var matchArray = this.match(datePat); // is format OK?
	if (matchArray == null) return false;
	// parse date into variables
	var d = parseInt(matchArray[1],10);
	var m = parseInt(matchArray[2],10);
	var y = parseInt(matchArray[3],10);
	if (y < 20) y = 2000 + y // reglage du siecle
	else if (y < 100 ) y = 1900 + y
	if (y <= 1900 || y >= 2020) return false;
	if (m < 1 || m > 12) return false;
	var maxDay = 31;
	if (m==4 || m==6 || m==9 || m==11) maxDay = 30;
	else if (m == 2) { // check for february 29th
    	if (y % 4 == 0 && (y % 100 != 0 || y % 400 == 0)) maxDay = 29;
    	else maxDay = 28;
  	}
	if (d < 1 || d > maxDay) return false;
	// to string
	if (d < 10) s= "0"; else s=""; s += d+"/"; if (m < 10) s += "0"; s+= m+"/"+y
	return s;
}
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
function clrDate() {
	var s=this.strip().replace(/[^0-9\-\/\. ]+/g,'');
	var l=s.length;
	var j=""; var m=""; var a=""
	if (l==6 && s.isNum()) {  // cas jjmmaa
    	j=s.substring(0,2)
    	m=s.substring(2,4)
    	a=s.substring(4,6)
  	} else if(l==8 && s.isNum()){ // cas jjmmaaaa
		j=s.substring(0,2)
		m=s.substring(2,4)
		a=s.substring(4,8)
	} else {
    	var ar=s.split(/[\-\/\. ]/g);
		if (ar.length != 3) return s; // pas de modif
    	j=ar[0]; m=ar[1];a=ar[2]
  	}
	if(j.length <2) j="0"+j;
	if(m.length <2) m="0"+m;
	if(a.length <2) return s; // pas de modif
	if(a < 1900) a=(a>20)? "19"+a : "20"+a
	return j+"/"+m+"/"+a
}
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Nettoyage des car interdits pour les nombres accepte . et , + strip()
function clrNum(strict){if (strict)return this.replace(/[^0-9]+/g,''); else return this.replace(/[^0-9\,\.]+/g,'');}
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Nettoyage des car interdits et accentues + strip()
function clrText(noTrim) {
	var a=this.replace(/[\?£\$<>\[\]\{\};"\(\){}\^\[\]\!\\\*²%]+/g,'');
	a = a.replace(/[&]/g,   "et");
/*	a = a.replace(/[àâä]/g,  "a");
	a = a.replace(/[éèêë_]/g, "e");
	a = a.replace(/[îï]/g,   "i");
	a = a.replace(/[ôö]/g,   "o");
	a = a.replace(/[ûüùµ]/g,  "u");
	a = a.replace(/[ç]/g,    "c");
	a = a.replace(/[ñ]/g,    "n");
*/
	a = a.replace(/[_]/g,    " ");
	a = a.replace(/[ ][ ]+/g," ");
	if (noTrim) return a;
	else return a.replace(/\s+$/,'').replace(/^\s+/,'');
}
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
function isName(strict) {
	if(strict) var r=/^[A-Za-z\ ]+$/;
	else var r=/^[A-Za-z\- \.\'0-9\:\&\(\)]+$/; //'
	return r.test(this)
}
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
String.prototype.isNum=function(){var r=/^[0-9]+$/;return r.test(this)}
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
String.prototype.isAmt=function(){var r=/^[0-9]+(\.[0-9]*)?$/;return r.test(this)}
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
String.prototype.isTel=function(){var r=/^0[0-9]{9}$/;return r.test(this)}
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
String.prototype.isCP=function(){var r=/^[0-9]{5}$/;return r.test(this)}
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
String.prototype.clrAmt=function(){var a=this.strip().replace(/[,]/,".");return a.replace(/[^0-9\.]+/g,'')}
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
String.prototype.clrText=clrText;
String.prototype.clrNum=clrNum;
String.prototype.clrDate=clrDate;
String.prototype.isName=isName;
String.prototype.isDate=isDate;
String.prototype.isEmail=isEmail;
//
// fin String
//
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// fonctions traitement dates
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// dateDiff(sDate1,sDate2)
// Arg : sDate[1|2] : string forme "jj/mm/aaaa"
//       Si date1 == "" --> aujourd hui
// retourne : un array [j,m,a] différence entre date1 et date2
function dateDiff(sDate1,sDate2)
{
	// format attendu jj/mm/aaaa
	re = /\d{2}\/\d{2}\/\d{4}/
  	if (sDate1==""){
    	var date1=new Date()
    	date1.setHours(0,0,0,0)
	} else {
    	if (!re.test(sDate1)) {alert("dateDiff():Erreur format date1");return false;}
    	a = sDate1.split("/")
    	var date1 = new Date(a[2],a[1]-1,a[0],0,0,0,0)
  	}
  //
	if (!re.test(sDate2)) {
		alert("dateDiff():Erreur format date2");
		return false;
	}
	a = sDate2.split("/")
	var date2=new Date(a[2],a[1]-1,a[0],0,0,0,0)
	var diff=new Date();
	diff.setTime(Math.abs(date1.getTime()-date2.getTime()));
	return Array(diff.getFullYear()-1970, diff.getMonth(), diff.getDate()-1)
}
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// dateCmp(sDate1,sDate2)
// Arg : sDate[1|2] : string forme "jj/mm/aaaa"
//       Si date1 == "" --> aujourd hui
// retourne : 1 = date1 > date2
//            0 = date1 = date2
//           -1 = date1 < date2
function dateCmp(sDate1, sDate2){
	// format attendu jj/mm/aaaa
	re = /\d{2}\/\d{2}\/\d{4}/
	if (sDate1==""){
		var date1=new Date()
    	m = date1.getMonth()+1;
    	m = (m < 10)? "0"+m:m;
    	j= date1.getDate();
    	j = (j < 10)? "0"+j:j
    	var d1 = ""+date1.getFullYear()+""+m+""+j
  	} else {
		if (!re.test(sDate1)) {
			alert("dateCmp():Erreur format date1");
			return 0;
		}
		a = sDate1.split("/")
    	a.reverse()
    	var d1 = a.join('')
	}
  	if (!re.test(sDate2)) {
	  alert("dateCmp():Erreur format date2");
	  return 0;
	}
  	a = sDate2.split("/")
  	a.reverse()
  	var d2 = a.join('');
  	return (d1 > d2)? 1:(d1 < d2)? -1:0;
}
// fin traitement dates

// *********************************************************************************************
// propres a CChamp
// les fct prefixée fXXXX sont des fct de mise en forme
function fNom(fld){ fld.value=fld.value.clrText().toUpperCase(); }
function fNum(fld){fld.value=fld.value.clrNum();}
function fMnt(fld) {fld.value=fld.value.clrAmt();}
// - - - - - - - - - - - - - - - -
// mise en forme date attend [j]j[/-. ][m]m[/-. ][aa]aa ou jjmmaa ou jjmmaaaa
// retourne jj/mm/aaaa
function fDate(fld) {
	s=fld.value.strip();
  	l=s.length;
  	var j=""; var m=""; var a=""
  	if (l==6 && s.isNum()) {  // cas jjmmaa
	  	j=s.substring(0,2)
    	m=s.substring(2,4)
    	a=s.substring(4,6)
  	} else if (l==8 && s.isNum()){ // cas jjmmaa
    	j=s.substring(0,2)
   		m=s.substring(2,4)
    	a=s.substring(4,8)
  	} else {
  	    s=s.gsub("[\-\.\s+]", "/");
	  	// s=s.replaceChar("-","/").replaceChar(".","/").replaceChar(" ","/");
    	var ar=s.split("/")
    	if (ar.length != 3) return
    	j=ar[0]; m=ar[1];a=ar[2]
  	}
  	if (j.length <2) j="0"+j
  	if (m.length <2) m="0"+m
  	if (a < 1900) a=(a>20)? "19"+a : "20"+a
  	fld.value=j+"/"+m+"/"+a
}
function fTel(fld){fld.value=fld.value.clrNum();}function fCP(fld){fld.value=fld.value.clrNum();}
function fEmail(fld){fld.value=fld.value.strip().toLowerCase();}


// - - - - - - - - - - - - - - - - -
// ARRAY
// proto Array.addItem()
Array.prototype.addItem=function (item) {this[this.length]=item;}

function isArray(obj) {
   if (obj.constructor.toString().indexOf("Array") == -1) return false;
   else return true;
}

// --------------------------------
// Applique le ctrl en fonction du param ctl
//  retourne : "" si OK
//    message d'erreur si erreur
function chkValue(val, ctl)
{
	if (ctl=="TXT") {
    	return ""; // pas de ctl
  	} else if (ctl=="NOM" ) {
    	if (val.isName()==false) return "Nom propre invalide.\nCe champ ne contient pas un nom";
  	} else if (ctl=="NUM") {
		if (val.isNum()==false) return "Valeur invalide.\nCe champ doit contenir une valeur numérique";
	} else if (ctl=="MNT") {
    	if (val.isAmt()==false) return "Valeur invalide.\nCe champ doit contenir une valeur numérique\nou un montant";
  	} else if (ctl=="DATE") {
    	if (val.isDate()==false) return "Date invalide.\nCe champ doit contenir date valide sous la forme jj/mm/aaaa";
  	} else if (ctl=="EMAIL") {
    	if (val.isEmail()==false) return "e-mail invalide.\nCe champ doit contenir une adresse e-mail valide";
  	} else if (ctl=="TEL") {
		if (val.isTel()==false) return "Téléphone invalide.\nCe champ doit contenir un numero de téléphone\nUniquement les 10 chiffres (sans séparateur)";
  	} else if (ctl=="CP") {
    	if (val.isCP()==false) return "Code Postal invalide.\nCe champ doit contenir un code postal\nUniquement 5 chiffres (sans séparateur)";
  	}
  	return ""
}
// --------------------------------
// Controle unitaire des champs (base sur nom du champ)
//    modif var glob a_all_err <-- ["id_field;msg_err", "id_field:msg_err", ...]
//
// Retrourne TRUE si pas d'erreur
//
function chkUnit(frm) {
	a_all_err.clear();
	var hRadio = $H();
  	var i,j,found=false, val, o;
  	for(i=0; i<frm.elements.length; i++) {
    	with(frm.elements[i]) {
      		if(nodeName=="FIELDSET") continue; 
      		if(disabled) continue;   
      		var a = name.split("_")
      		if (a.length < 3) continue;
      		var obl=(a[1]=="O")? true:false;
      		var ctl = a[0];
      		if (type=="select-one") {
        		val = options[selectedIndex].value;
      		} else if (type=="radio") {
      		    // memorise dans h['name'] <= id les differents groupes de radio
      		    // si radio = checked alors valeur de l'id = "/"
        		val="x"; //dummy val
        		if (obl) {
        		    if(checked) hRadio[name] = "/";
        		    else {
        		        // si l'entree n'existe pas dans h alors cree l'entree avec valeur id
						if(h.keys().indexOf(name)==-1) h[name] = id;
					}
        		}
      		} else {
        		val = value.strip()
      		}
      		if (val.blank() || val=="??") {
        		if (obl) {
          			if(val=="??") a_all_err.addItem(id+";Vous devez sélectionner une valeur dans la liste");
            		else a_all_err.addItem(id+";Une valeur est requise pour ce champ");
        		}
      		} else {
        		var local_msg = chkValue(val, ctl);
        		if (local_msg != "") a_all_err.addItem(id+";"+local_msg);
        	}
    	} // with
  	} // for
  	// verif des radio (obl)
	var v = hRadio.values();
  	for (j=0;j<v.length;j++) {
  	    if (v[j] != "/") a_all_err.addItem(v[j]+";Vous devez choisir une valeur;")
  	}
  	if (a_all_err.length > 0) return false;
  	else return true;
}


/* - - - - - - - - - - - - - - - 
 * Affiche message d'erreur dans alert() + postionne le focus sur le champ si possible
 * Message d'erreur issu de a_all_err[]
 */
function alert_e(indx) {
  // ctrl avant fct
  	if (!isArray(a_all_err)) {
    	alert("alert_e(indx) : a_all_err n'est pas un tableau !");
	} else if (indx==0 && !a_all_err.length) {
	    // si a_all_err est vide alors pas de msg d'erreur utile pour placer cette fct dans onLoad
		return false;
    } else if(indx >= a_all_err.length || indx < 0)  {
    	alert("alert_e(indx) valuer de indx ["+indx+"] invalide (max = "+a_all_err.length+")");
  	} else {
		var a = a_all_err[indx].split(";");
		var id = a[0];
		var msg = a[1];
		if(id != "/" && id && id!="") {
			o = $(id);
			if(!o.disabled) {
				if(o.tagName=="SELECT" || (o.tagName=="INPUT" && o.type=="text") || o.tagName=="AREA") o.select();
				if(o.type !="hidden") o.focus();
			}
		}
		alert(msg);
	}
	return false;
}
// End of $RCSfile$


