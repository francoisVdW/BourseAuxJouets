<?php
/**
 * Include file: utl_deposant
 *
 * Page de saisie des depots
 *
 * @package bourse
 * @version $Revision: 469 $
 * @author FVdW
 */

if(defined('UTL_GEST_PART')) return;
define('UTL_GEST_PART',1);

// Decl champs pour <form> deposant
require_once 'fwlib/cchamp.class.php';

$nom	= new CChamp('NOM_O_nom', '', 35);
$prenom	= new CChamp('TXT_L_prenom', '', 25);
$connex	= new CChamp('TXT_O_login','',15);
$pwd   	= new CChamp('PWD_O_pwd','',15);
$pwd2	= new CChamp('PWD_O_pwd2','',15);
$pwdOld	= new CChamp('PWD_O_pwdOld','',15);
$perm  	= new CChamp('CB_L_perm', '',0,"may_depot=Dépot|may_caisse=Caisse|may_retrait=Restitution|may_gestion=Gestion");


/**
 * insert d'un nouveau participant dans la DB
 *
 * Verifie que le login est unique, si OK insert nouveau participant
 *
 * @return bool : true si operation OK
 */
function insert_part() {
	global $db;
	global $nom, $prenom, $connex, $pwd, $pwd2, $perm;
	
	global $aErr;
	/** Acqusition et ctrl des données du _POST
	*/
	if (!$nom->chkPost()) $aErr[] = $nom->getErr();
	if (!$prenom->chkPost()) $aErr[] = $prenom->getErr();
	if (!$connex->chkPost()) $aErr[] = $connex->getErr();
	if (!$pwd->chkPost()) $aErr[] = $pwd->getErr();
	if (!$pwd2->chkPost()) $aErr[] = $pwd2->getErr();
	if (!$perm->chkPost()) $aErr[] = $perm->getErr();
	if($pwd->val != $pwd2->val) $aErr[] = $pwd->getId().';Le mot de passe et sa confirmation ne sont pas identiques';
	if(count($aErr)) {
	    return false;
	}
	/** Verification doublon
	*/
	$sql = "SELECT count(*) AS cnt FROM participant WHERE login=".$connex->getDbVal();
	$r = $db->select_one($sql);
	if(!$r) logFatal(__FUNCTION__."() 0 rec\n\$sql=$sql\n",__FILE__,__LINE__);
	if($r['cnt'] > 0) {
		$aErr[] = $connex->id.";Le nom de connexion ".$connex->getVal()." est deja utilise.\\nChoississez un nouveau nom";
		return false;
	}
	$aPerm = explode(';',$perm->getVal());
	$may_depot 		= in_array('may_depot', $aPerm)? "'T'":"'F'";
	$may_caisse 	= in_array('may_caisse', $aPerm)? "'T'":"'F'";
	$may_retrait 	= in_array('may_retrait', $aPerm)? "'T'":"'F'";
	$may_gestion 	= in_array('may_gestion', $aPerm)? "'T'":"'F'";
	$sql = "INSERT INTO participant (bourse_idbourse, nom, prenom, login, pwd, may_depot, may_caisse, may_retrait, may_gestion)".
	    "VALUES ({$_SESSION['bourse']['idbourse']}, ".$nom->getDbVal().", ".$prenom->getDbVal().",".
		$connex->getDbVal().", md5(".$pwd->getDbVal()."),".
        "$may_depot, $may_caisse, $may_retrait, $may_gestion)";
	$id =$db->query($sql);
	if(!$id) logFatal("insert_part() erreur \$id=$id\n\$sql=$sql\n",__FILE__,__LINE__);
	return  true;
}

/**
 * Mise a Jour d'un nouveau participant dans la DB
 *
 * 
 *
 * @return bool : true si operation OK
 */
function update_part()
{
	global $db;
	global $nom, $prenom, $connex, $pwd, $pwd2, $pwdOld, $perm;

	global $aErr;
	/** Ctrl param get idparticipant
	*/
	if(!isset($_GET['idparticipant'])) logFatal("update_part() : _GET[idparticipant] non defini",__FILE__, __LINE__);
	$idparticipant = $_GET['idparticipant'];
	if(!is_numeric($idparticipant)) logFatal("update_part() \$idparticipant invalide ($idparticipant)", __FILE__,__LINE__);

	/** Acqusition et ctrl des données du _POST
	*/
	if (!$nom->chkPost()) $aErr[] = $nom->getErr();
	if (!$prenom->chkPost()) $aErr[] = $prenom->getErr();
	if (!$connex->chkPost()) $aErr[] = $connex->getErr();
	if(isset($_POST[$pwdOld->getName()])) {
		if (!$pwd->chkPost()) $aErr[] = $pwd->getErr();
		if (!$pwd2->chkPost()) $aErr[] = $pwd2->getErr();
		if($pwd->val != $pwd2->val) $aErr[] = $pwd->getId().';Le mot de passe et sa confirmation ne sont pas identiques';
		if (!$pwdOld->chkPost()) $aErr[] = $pwdOld->getErr();
		/** Verification mot de passe
		*/
		$sql = "SELECT Count(*) AS cnt FROM participant WHERE idparticipant=$idparticipant AND pwd=md5(".$pwdOld->getDbVal().") AND bourse_idbourse={$_SESSION['bourse']['idbourse']}";
		$r = $db->select_one($sql);
		if(!$r) logFatal("update_part() Erreur SQL 0 rec\n\$sql=$sql\n",__FILE__,__LINE__);
		if($r['cnt']!=1) $aErr[] = $pwd->getId().";L'ancien mot de passe est invalide !";
		$sUpdPwd = ",pwd=md5(".$pwd->getDbVal().")";
	} else {
		$sUpdPwd = '';
 	}

	if (!$perm->chkPost()) $aErr[] = $perm->getErr();
	if(count($aErr)) {
	    return false;
	}
	$aPerm = explode(';',$perm->getVal());
	$may_depot 		= in_array('may_depot', $aPerm)? "'T'":"'F'";
	$may_caisse 	= in_array('may_caisse', $aPerm)? "'T'":"'F'";
	$may_retrait 	= in_array('may_retrait', $aPerm)? "'T'":"'F'";
	$may_gestion 	= in_array('may_gestion', $aPerm)? "'T'":"'F'";
	$sql = "UPDATE participant SET nom=".$nom->getDbVal().",".
		" prenom=".$prenom->getDbVal().",".
		" login=".$connex->getDbVal().",".
		" may_depot=$may_depot,may_caisse=$may_caisse,may_retrait=$may_retrait, may_gestion=$may_gestion".
		$sUpdPwd.
		" WHERE bourse_idbourse={$_SESSION['bourse']['idbourse']} AND idparticipant=$idparticipant";
	$db->query($sql);
	return  true;
}

/**
 * Supression d'un participant
 *
 * @return bool : true si Opération OK
 */
function delete_part()
{
	global $db;
	global $aErr;
	
	/** Ctrl param get idparticipant
	*/
	if(!isset($_GET['idparticipant'])) logFatal("delete_part() : _GET[idparticipant] non defini",__FILE__, __LINE__);
	$idparticipant = $_GET['idparticipant'];
	if(!is_numeric($idparticipant)) logFatal("delete_part() \$idparticipant invalide ($idparticipant)", __FILE__,__LINE__);

  	/** Verifique si depot, vente ou reprises deja effectuees par ce participant
  	*/
  	$sql = "SELECT count(*) AS cnt FROM depot WHERE idparticipant_depot=$idparticipant OR idparticipant_retrait=$idparticipant";
	$r = $db->select_one($sql);
	if(!$r) logFatal("delete_part() :la requete retourne 0 rec\n\$sql=$sql\n",__FILE__,__LINE__);
	$cnt += $r['cnt'];
  	$sql = "SELECT count(*) AS cnt FROM vente WHERE participant_idparticipant=$idparticipant";
	$r = $db->select_one($sql);
	if(!$r) logFatal("delete_part() : la requete retourne 0 rec\n\$sql=$sql\n",__FILE__,__LINE__);
	$cnt += $r['cnt'];
	if($cnt) {
		$sql = "UPDATE participant SET may_depot='F', may_caisse='F', may_retrait='F', may_gestion='F' WHERE bourse_idbourse={$_SESSION['bourse']['idbourse']} AND idparticipant=$idparticipant";
 	} else {
   		$sql = "DELETE FROM participant WHERE bourse_idbourse={$_SESSION['bourse']['idbourse']} AND idparticipant=$idparticipant";
	}
	$n = $db->query($sql);
	if ($n==0) {
		$aErr[] = "/;Aucun participant effacé !";
	  	return false;
	} elseif($n != 1) {
   		logInfo("delete_part() : la requete retourne (\$n)=$n\n\$sql=$sql\n",__FILE__,__LINE__);
   		$aErr[] = "/;Erreur lors de la supression du participant (voir log)";
   		return false;
	} else return true;
}
?>