<?php
/**
 * Include file: gest_part.inc.php
 *
 * Page de gestion des participants
 *
 * @package bourse
 * @version $Revision: 187 $
 * @author FVdW
 */

// Decl champs pour <form> deposant
require_once 'inc/utl_gest_part.inc.php';


/** Verification droit "gestion"
 */
if($user->get_field('may_gestion') != 'T') {
	echo "<html><body><h1>Erreur de droits</h1>Vous n'avez pas acces à la <i>gestion des participants</i></b><br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
	exit();
}


if(!isset($_GET['act'])) logFatal("_GET[act] non defini",__FILE__,__LINE__);
switch($_GET['act']) {
	case 'edit':
		if(!isset($_GET['idparticipant'])) logFatal("_GET[idparticipant] non defini",__FILE__,__LINE__);
		$idparticipant = $_GET['idparticipant'];
		if(!is_numeric($idparticipant)) logFatal("\$idparticipant non numeric [$idparticipant]",__FILE__,__LINE__);
		$eNextActionTbs = T_UPD_PART.'&idparticipant='.$idparticipant.'&act=edit';
		$h1Tbs = "Modification du participant";
		$jsSuppTbs ='';
		break;
	case 'add':
		$eNextActionTbs = T_INS_PART.'&act=add';
		$idparticipant=FALSE;
		$h1Tbs = "Ajouter un participant";
		$jsSuppTbs ='';
		break;
	case 'sup':
		if(!isset($_GET['idparticipant'])) logFatal("_GET[idparticipant] non defini",__FILE__,__LINE__);
		$idparticipant = $_GET['idparticipant'];
		if(!is_numeric($idparticipant)) logFatal("\$idparticipant non numeric [$idparticipant]",__FILE__,__LINE__);
		$eNextActionTbs = T_DEL_PART.'&idparticipant='.$idparticipant;
		$h1Tbs = "Supprimer un participant";
		$jsSuppTbs ='if(confirm("supprimer ce participant ?")) return true;else return false;';
		break;
	default:
	    logFatal("_GET[act] non reconnu [{$_GET['act']}]",__FILE__,__LINE__);
		break;
  
}


/** Acquisition des info du participant
 */
if($idparticipant !== false) {
	$sql = "SELECT * FROM participant WHERE bourse_idbourse=".$_SESSION['bourse']['idbourse']." AND idparticipant=$idparticipant";
	$r = $db->select_one($sql);
	if(!$r) logFatal("la requete retourne 0 rec\n\$sql=$sql\n",__FILE__,__LINE__);
	$nom->default_val = $r['nom'];
	$prenom->default_val = $r['prenom'];
	$connex->default_val = $r['login'];
	$perm->default_val = ($r['may_depot']=='T'? 'may_depot;':'' ).
	                    ($r['may_caisse']=='T'? 'may_caisse;':'').
	                    ($r['may_retrait']=='T'? 'may_retrait;':'').
	                    ($r['may_gestion']=='T'? 'may_gestion;':'');
}
$cnt = 0; // compteur des opération depot, vente, retrait effectuees par ce participant
if($_GET['act']=='sup') {
  	/** si supression verifique si depot, vente ou reprises deja effectuees par ce participant
  	*/
  	$sql = "SELECT count(*) AS cnt FROM depot WHERE idparticipant_depot=$idparticipant OR idparticipant_retrait=$idparticipant";
	$r = $db->select_one($sql);
	if(!$r) logFatal("la requete retourne 0 rec\n\$sql=$sql\n",__FILE__,__LINE__);
	$cnt += $r['cnt'];
	$sql = "SELECT count(*) AS cnt FROM vente WHERE participant_idparticipant=$idparticipant";
	$r = $db->select_one($sql);
	if(!$r) logFatal("la requete retourne 0 rec\n\$sql=$sql\n",__FILE__,__LINE__);
	$cnt += $r['cnt'];
}

if ($cnt && $_GET['act']=='sup') {
	$msgInfoTbs = "<span style='color:red'><b>Attention</b> :</span>&nbsp;Ce participant a déjà enregistré des opérations, il ne peut être supprimé, les permissions de cet utilisateur seront toutes retirées.";
} else {
	$msgInfoTbs = '';
}

/** champs du formulaire
*/
$js_all_errTbs = get_JS_a_all_err($aErr);
switch ($_GET['act']) {
	case 'add':
		$nomTbs 	= $nom->printFieldEx('Nom ');
		$prenomTbs 	= $prenom->printFieldEx('Prénom');
		$loginTbs 	= $connex->printFieldEx('Nom de connexion');
		$pwdsTbs	= $pwd->printFieldEx('Mot de passe').'&nbsp;&nbsp;Confirmez : '.$pwd2->printField();
		$permTbs 	= $perm->printFieldEx('Permissions');
		break;
	case 'edit';
		$nomTbs	= $nom->printFieldEx('Nom ');
		$prenomTbs	= $prenom->printFieldEx('Prénom');
		$loginTbs	= $connex->printFieldEx('Nom de connexion');
		$pwdsTbs	= '<button onclick="return pwd()">Mot de passe</button>&nbsp;'.
						'<span id="isp_pwd" style="display:none">'.
						'Ancien :'.$pwdOld->printField('disabled="1"').'&nbsp;&nbsp;'.
						'Nouveau : '.$pwd->printField('disabled="1"').'&nbsp;&nbsp;'.
						'Confirmez : '.$pwd2->printField('disabled="1"').
						'</span>';
		$permTbs 	= $perm->printFieldEx('Permissions');
		break;
	case 'sup':
		$nomTbs 	= '<label >Nom :&nbsp;</label>'.$nom->default_val;
		$prenomTbs 	= '<label >Prénom :&nbsp;</label>'.$prenom->default_val;
		$loginTbs 	= '<label >Nom de connexion :&nbsp;</label>'.$connex->default_val;
		$pwdsTbs	= '';
		$permTbs 	= '<label >Permission pour :&nbsp;</label>'.($r['may_depot']=='T'? 'Dépot&nbsp;':'' ).	                    ($r['may_caisse']=='T'? 'Caisse&nbsp;':'').
					($r['may_retrait']=='T'? 'Retrait&nbsp;':'').
					($r['may_gestion']=='T'? 'Gestion':'');
		break;
}

$idPwdTbs  		= $pwd->getId();
$idPwd2Tbs		= $pwd2->getId();
$idPwdOldTbs	= $pwdOld->getId();
?>