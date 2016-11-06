//
// $Id: $
//
var tmr=false;
function loading(msg) {
    if(msg) {
        $('itdmsg').update(msg);
        $("imsg").show();
        tmr = setTimeout("alert('Le serveur ne répond pas ! recommencez');$('imsg').hide();", 10000);
    } else {
        clearTimeout(tmr);
        $("imsg").hide();
    }
}

function erreur(json) {
    var msg = json.a_err;
    a_all_err.clear();
    a_all_err.addItem(msg);
    alert_e(0);
}

window.onbeforeunload = function (evt) {
    if(!$('ibtn_annulvente').disabled && nbArt) {
        var message = 'Des articles ont été enregistrés!';
        if (typeof evt == 'undefined') {//IE
            evt = window.event;
        }
        if (evt) {
            evt.returnValue = message;
        }
        return message;
    }
}


function list_art(json) {
	var img = "";
	if (json.apply_happy_hour) img = '<image src="img/hh.gif" title="Réduction Happy Hour appliquée" />&nbsp;';
  return "<tr id='a"+json.id_art+"' height='30'><td style='background-color:"+json.code_couleur+"'>"+json.id_depot+"-<b>"+json.id_art+"</b></td><td width='25'><img src=\"img/del.gif\" class=\"sup\" title=\"Retirer l'article de la liste\" onclick='unlock("+json.id_art+", false)' /></td><td>"+json.description+"</td><td align='right'>"+img+json.prix_vente+" &euro;</td></tr>";
}

function conf_art() {
    var sArt = list_art(jsonBak);
    var x = new Insertion.Bottom("ilist", sArt);
    somme();
    $('iinfo').hide();
    aut_saisie(true);
    $('isaisie').reset();
}

function annul_art() {
    $('iinfo').hide();
    aut_saisie(true);
    unlock(jsonBak.id_art, true);
}
function centim(mnt){
    var m = Math.round(Math.round(mnt*100));
    var s;
    if(m<10) s = '0.0'+m;
    else if (m<100) s = '0.'+m;
    else {
        var mod = (m%100);
        if(!mod) mod='00';
        else if(mod<10) mod='0'+mod;
        s = ((m-mod)/100)+'.'+mod
    }
    return s;
}
function paye(actif){
    if(!nbArt) {
        alert('Aucun article a payer');
        aut_saisie(true);
        return false;
    }
    if(actif) {
        $('imode_pay').show();
        $('ifpaye').reset();
        $('ibtn_fin').disabled=true;
        aut_saisie(false);
        $('iMNT_L_esp').focus();
    } else  {
        $('imode_pay').hide();
        aut_saisie(true);
    }
    return false;
}
function facture(){
    $('idata_fact').show();
}
// ----------------------------------------------------------------------------
// Responders 4 Ajax calls
function trtRespSomme(req, json) {
    if (json.a_err) erreur(json);
    else {
        nbArt = parseInt(json.nb_art);
        mntTtl =json.somme;
        $('somme').update(mntTtl+ " &euro;");
        // affichage de la somme dans <div> payer
        $('isp_ttl').update(mntTtl);

        if(nbArt) $('ibtn_annulvente').disabled=false;
        else $('ibtn_annulvente').disabled=true;
    }
}
function trtRespLock(req, json) {
    loading(false);
    if (json.a_err) {
        if (typeof json.id_art == 'undefined') {
            // affiche Div dlg : idiv_not_found
            $('inotfound').show();
            var a = json.a_err.split(";");
            $('info_txt').update(a[1]);
        } else {
            // affiche Div dlg : idiv_vendu
            $('ivendu').show();
            $('info_n_art_v').update(json.id_depot+'-<b>'+json.id_art+'</b>');
            $('info_desc_art_v').update(json.description);
            $$('div#ivendu button')[0].focus();
        }

    } else {
        // affiche boite dialog
        $('iinfo').show();
        $('info_n_art').update(json.id_depot+'-<b>'+json.id_art+'</b>');
        $('info_n_art').style.backgroundColor = json.code_couleur;
        $('info_desc_art').update(json.description);
        var px = json.prix_vente+' &euro;'
        if (json.apply_happy_hour) {
			px = px + " <span style=\"color:#666;font-size:85%\">("+json.prix_vente_ori+" &euro;)</span>";
		}
        $('info_prix_art').update(px);
        // globale hhStarted definie dans caisse.html par var TBS [var.hhStartedTbs]
        if (json.apply_happy_hour) {
			$('img_info_art_hh').show();
		} else {
			$('img_info_art_hh').hide();
		}
        // memorise info
        jsonBak = json;
        aut_saisie(false);
        $('iconfirm').focus();
    }
}

function trtRespUnlock(req, json) {
    if (json.a_err) erreur(json);
    else {
        aut_saisie(true);
        $('isaisie').reset();
        var tr=$('a'+json.id_art);
        if(tr) {
            tr.remove();
            somme();
        }
    }
    loading(false);
}


function trtRespFact(resp, json){
    $('ibtn_enreg_fact').disabled=false;
    loading(false);
    if (json.a_err) {
        erreur(json);
    }
    else {
      idFact = json.id_fact;
      $('idata_fact').hide();
      $('ibtn_fact').show();
      print_fact();
    }
}

function print_fact() {
    var id = "999"
    eval("page" + id + " = window.open('fact.php?idfacture="+idFact+"','" + id + "', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=550,height=400');");

}
