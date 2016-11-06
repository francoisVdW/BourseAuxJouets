<?php
/**
 * Include file: utl_depot_art
 *
 * Page de saisie des depots
 *
 * @package bourse
 * @version $Id$
 * @author FVdW
 */
if(defined('UTL_DEPOT_ART')) return;
define('UTL_DEPOT_ART',1);


// Decl champs pour saisie Articles
require_once 'fwlib/cchamp.class.php';
$description	= new CChamp('TXT_O_desc', '', 255);
$prix_achat		= new CChamp('HID_O_pa');
$prix_vente		= new CChamp('MNT_O_pv',0,'','0.10|500');
$prev_id_art    = new CChamp('HID_L_id_art');
$happy_hour     = new CChamp('SEL_L_happy_hour', 0, 0, '0=Non|1=Oui');
$code_couleur   = new CChamp('SEL_L_couleur', 'White',0,$_SESSION['optCouleur'] );

/**
 * save_art : upate/insert article dans la DB
 * 
 * Effectue ctrl métier
 * @param bool $insert true=insert false=update
 */    
function save_art($insert)
{
	global $db;
	$aRetValue = array();
	global $description, $prix_achat, $prix_vente, $happy_hour, $code_couleur, $prev_id_art;
	if (!$description->chkPost()) 		return array('a_err'=>$description->getErr());
	elseif (!$prix_achat->chkPost()) 	return array('a_err'=>$prix_achat->getErr());
	elseif (!$prix_vente->chkPost()) 	return array('a_err'=>$prix_vente->getErr());
	elseif (!$code_couleur->chkPost()) 	return array('a_err'=>$code_couleur->getErr());
	elseif (!is_numeric($_POST['id_depot'])) return array('a_err'=>"/;Erreur soft id_depot invalide ({$_POST['id_depot']})");
	elseif (!is_numeric($prix_achat->getVal()) ) return array('a_err'=>"/;Erreur soft pa invalide (".$prix_achat->getVal().")");
	if (!empty($_SESSION['bourse']['hh_start_date'])) {
		$happy_hour->chkPost();
	} else {
		$happy_hour->setVal(0);
	}
	// ctrl metier
	$pa = round($prix_achat->getVal(),2);
	$pv = round($prix_vente->getDbVal(),2);
	if ($pa <= 0) {
	  	return array('a_err'=>"/;Attention le prix d'achat ne peut etre <= 0");
	}
	elseif ($pv <= 0) {
	  	return array('a_err'=>"/;Attention le prix de vente ne peut etre <= 0");
	}
	elseif ($pv <= $pa) {
	  	return array('a_err'=>"/;Attention le prix de vente ne peut etre <= au prix d'achat'");
	}
	// 	
	// les données sont valides	
	if ($insert) {
		// Insertion dans la DB
		$sql = "INSERT INTO article (depot_iddepot, prix_achat, prix_achat_ori, prix_vente, prix_vente_ori, description, happy_hour, code_couleur) VALUES (
	    {$_POST['id_depot']},
	    ".round($prix_achat->getVal(),2).",
	    ".round($prix_achat->getVal(),2).",
	    ".round($prix_vente->getDbVal(),2).",
	    ".round($prix_vente->getDbVal(),2).",
	    ".utf8_decode($description->getDbVal()).",
        ".$happy_hour->getDbVal().",    
	    ".$code_couleur->getDbVal().")";
		$id_art = $db->query($sql);
		// Retour a appelant...
		if ($id_art <= 0) {
		    logInfo("save_art(INSERT) : nouvel article n=$n\nsql=$sql",__FILE__,__LINE__);
		    return array('a_err'=>"/;Erreur insert dans la DB");
		} 
	    $aRetValue['op'] = 'insArt'; // indique aux JS : ajouter
   		$aRetValue['id_art'] = $id_art;
	} else {
		// Update de la DB
		if (!$prev_id_art->chkPost()) 	return array('a_err'=>$prev_id_art->getErr());
		$sql = "UPDATE article SET
		prix_achat = $pa, 
		prix_achat_ori = $pa,
		prix_vente = $pv,
		prix_vente_ori = $pv,
		description = ".utf8_decode($description->getDbVal()).",
		happy_hour = ".$happy_hour->getDbVal()."
		code_couleur = ".$code_couleur->getDbVal()."
		WHERE idarticle=".$prev_id_art->getDbVal();
		$n = $db->query($sql);
		// Retour a appelant...
		if ($n < 0) {
		    logInfo("save_art(UPDATE) : MaJ article n=$n\nsql=$sql",__FILE__,__LINE__);
   		    return array('a_err'=>"/;Erreur update dans la DB");
		} 
	    $aRetValue['op'] = 'updArt'; // indique aux JS : MaJ
	    $aRetValue['id_art'] = $prev_id_art->getVal();
	}
    $aRetValue[$description->getName()] = stripslashes($description->getVal());
    $aRetValue[$prix_achat->getName()] = sprintf("%.02f",$prix_achat->getVal());
    $aRetValue[$prix_vente->getName()] = sprintf("%.02f",$prix_vente->getVal());
    $aRetValue[$happy_hour->getName()] = $happy_hour->getVal()? 1:0;
    $aRetValue['code_couleur'] = $code_couleur->getVal();
    $aRetValue['id_depot'] = $_POST['id_depot'];
	
	return $aRetValue;
}

/**
 * ajax_insert_art
 */
function ajax_insert_art()
{
	return save_art(true);
}
/**
 * ajax_upd_art
 */
function ajax_upd_art()
{
	return save_art(false);
}

/**
 * ajax_read_art
 */
function ajax_read_art()
{
	global $db;
	global $description, $prix_achat, $prix_vente, $code_couleur;
	$aRetValue = array();
	if (!is_numeric($_POST['id_art'])) $aRetValue['a_err'] = "/;Erreur soft id_art invalide ({$_POST['id_art']})";
	else {
		// request sur la DB
		$sql = "SELECT * FROM article WHERE idarticle=".$_POST['id_art'];
		$r = $db->select_one($sql);
		// Retour a appelant...
		if (!$r) {
		    $aRetValue['a_err'] = "/;Erreur insert dans la DB. \$n=$n";
		    logFatal("0 rec pour requete : $sql",__FILE__,__LINE__);
		} else {
		    $aRetValue['op'] = 'readArt'; // indique aux JS : retour read
		    $aRetValue['id_art'] = $_POST['id_art'];
		    $aRetValue[$description->getName()] = toUTF8(($r['description']));
		    $aRetValue[$prix_achat->getName()] = sprintf("%.02f",$r['prix_achat']);
		    $aRetValue[$prix_vente->getName()] = sprintf("%.02f",$r['prix_vente']);
			$aRetValue['happy_hour']=$r['happy_hour'];
			$aRetValue['code_couleur']=$r['code_couleur'];
			$aRetValue['id_depot'] = $r['depot_iddepot'];
		}
	}
	return $aRetValue;
}


/**
 * ajax_del_art
 */
function ajax_del_art()
{
	global $db;
	$aRetValue = array();
	if (!is_numeric($_POST['id_art'])) $aRetValue['a_err'] = "/;Erreur soft id_art invalide ({$_POST['id_art']})";
	else {
		// request sur la DB
		$sql = "DELETE FROM article WHERE idarticle=".$_POST['id_art'];
		$n = $db->query($sql);
		// Retour a appelant...
		if ($n != 1) {
		    $aRetValue['a_err'] = "/;Erreur insert dans la DB. \$n=$n";
		    logFatal("\$n=$n pour requete : $sql",__FILE__,__LINE__);
		} else {
		    $aRetValue['op'] = 'delArt'; // indique aux JS : retour del
		    $aRetValue['id_art'] = $_POST['id_art'];
		}
	}
	return $aRetValue;
}
?>
