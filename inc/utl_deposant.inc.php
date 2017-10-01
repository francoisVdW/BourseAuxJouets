<?php
/**
 * Include file: utl_deposant
 *
 * Page de saisie des depots
 *
 * @package bourse
 * @version $Id$
 * @author FVdW
 */

if(defined('UTL_DEPOSANT')) return;
define('UTL_DEPOSANT',1);

// Decl champs pour <form> deposant
require_once 'fwlib/cchamp.class.php';

$nom	= new CChamp("NOM_O_deposant", '', 35);
$prenom	= new CChamp("NOM_L_prenom", '', 25);
$tel    = new CChamp("TEL_O_tel");
$email  = new CChamp("EMAIL_L_email");
switch ($_SESSION['bourse']['adresse_deposant']) {
	case "MANDATORY":
		$adr1   = new CChamp("TXT_O_adr1", '', 25);
		$adr2   = new CChamp("TXT_L_adr2", '', 25);
		$commune= new CChamp("NOM_O_comm", '', 25);
		$cp     = new CChamp("CP_O_cp");
	    break;
	case "OPTION":
	case "NONE":
		$adr1   = new CChamp("TXT_L_adr1", '', 25);
		$adr2   = new CChamp("TXT_L_adr2", '', 25);
		$commune= new CChamp("NOM_L_comm", '', 25);
		$cp     = new CChamp("CP_L_cp");
	    break;
	    break;
}
/**
 * Enregistre le nouveau deposant dans la DB
 *
 * @return mixed : FALSE si erreur (msg d'erreur dans $aErr), id_depot si OK
 */
function sav_deposant()
{
	global $db;
	global $nom, $prenom, $adr1, $adr2, $cp, $commune, $tel, $email;
	global $user;
	global $aErr;
	/** Acqusition et ctrl des données du _POST
	*/
	if (!$nom->chkPost()) $aErr[] = $nom->getErr();
	if (!$prenom->chkPost()) $aErr[] = $prenom->getErr();
	if (!$adr1->chkPost()) $aErr[] = $ard1->getErr();
	if (!$adr2->chkPost()) $aErr[] = $ard2->getErr();
	if (!$cp->chkPost()) $aErr[] = $cp->getErr();
	if (!$commune->chkPost()) $aErr[] = $commune->getErr();
	if (!$tel->chkPost()) $aErr[] = $tel->getErr();
	if (!$email->chkPost()) $aErr[] = $email->getErr();       
    
	if(count($aErr)) {
	    return FALSE;
	}
	
	$sql = "INSERT INTO deposant (nom, prenom, tel, email, adresse, adresse2, cp, commune, idbourse)
		VALUES (".$nom->getDbVal().",
		".$prenom->getDbVal().",
		".$tel->getDbVal().",
		".$email->getDbVal().",
		".$adr1->getDbVal().",
		".$adr2->getDbVal().",
		".$cp->getDbVal().",
		".$commune->getDbVal().",
		".$user->get_field('bourse_idbourse').")";

	$n = $db->query($sql);
	if ($n > 0) {
		return $n; // id du dépot crée
	} elseif ($n == 0) {
	    // enregistrement non crée
	    $aErr[] = "/;Erreur lors de l'enregistrement !";
	    return FALSE;
	}
}
// EoF