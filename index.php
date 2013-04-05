<?php
/**
 * @package: bourse
 * @author : FVdW
 * @version $Revision: 692 $
 *
 */

require_once 'fwlib/tbs_class_php5.php';
require_once 'fwlib/user.class.php';
require_once 'fwlib/sql.class.php';
require_once 'fwlib/log.inc.php';
require_once 'inc/settings.php';


/** Etats stables
*/
define ('S_MAIN', 1);
define ('S_NOUV_DEPOT', 2);
define ('S_CAISSE', 3);
define ('S_RETRAIT', 4);
define ('S_DEPOT_ART', 5);
define ('S_FIN_DEPOT',6);
define ('S_RETRAIT_SOLDE',7);
define ('S_GESTION',8);
define ('S_CCAISSE',9);
define ('S_CRETRAIT',10);
define ('S_TABLBORD', 11);
define ('S_DEVEROUILLAGE', 12);
define ('S_GESTION_PARTICIPANTS', 13);
define ('S_EDIT_PART', 14);
define ('S_ARTICLE', 15);
define ('S_LST_ART', 16);
define ('S_FICHE_ART', 17);
define ('S_CLOTURE_VENTE', 18);
define ('S_INIT_BOURSE',19);
define ('S_FONDS_CAISSES',20);

/** Etats transitoires
*/
define ('T_SAUV_DEPOSANT',51);
define ('T_FIN_VENTE',52);
define ('T_ANNULVENTE',53);
define ('T_ANNULDEPOT', 54);
define ('T_DEVEROUILLAGE',55);
define ('T_INS_PART',56);
define ('T_UPD_PART',57);
define ('T_DEL_PART',58);
define ('T_CLOTURE_VENTE', 59);
define ('T_REINIT', 60);

/**
 * facilite formattage des champs Ajax (dans appel)
 */
function getCChampAjaxParam($cchamp)
{
  return "'&".$cchamp->getName()."='+\$F('".$cchamp->getId()."')";
}

/**
 * retourne le nom du participant par son id
 *
 * @param int $id : id du participant
 * @return array : ['nom'=> , 'prenom'=> ] du participant
 */
function lect_participant($id)
{
	global $db;
  	$sql = "SELECT nom, prenom FROM participant WHERE idparticipant=$id";
  	$n = $db->query($sql);
  	if($n != 1) {
	 	logFatal("lect_participant($id) : query retourne $n\n\$ql=$sql", __FILE__,__LINE__);
   	}
   	return trim($db->data[0]['prenom'].' '.$db->data[0]['nom']);
}
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
//
//              M A I N  (  )
//
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -


session_start();
/** déconnexion demandée
*/
if(isset($_GET['logout'])) {
	session_destroy();
	header("Location: index.php");
	exit();
}

/** ouverture de la DB
*/
$db = new Cdb(DB_HOST, DB_NAME, DB_USER, DB_PWD);
$db->open();


/** Connexion et ctrl login/pwd - Session
*/
$user = new User(DB_HOST, DB_NAME, DB_USER, DB_PWD);

if (!isset($_SESSION['logged']) || !$_SESSION['logged'] || !$user->uid) {
	/** Si pas connecte : <form> de login puis ctrl
	*/
    require_once 'inc/login_form.inc.php';
	/** Recuperation parametrage bourse (idbourse) aprs connexion validee
	*/
	$sql = "SELECT * FROM bourse WHERE idbourse=".$user->get_field('bourse_idbourse');
	$n = $db->query($sql);
	if($n != 1) exit("<html><body><h1>Erreur idbourse !</h1>\$n=$n<br><pre>sql=$sql</pre></body></html>");
	$_SESSION['bourse'] = $db->data[0];
	}
	
$participTbs = ($user->get_field('prenom')? $user->get_field('prenom').' ':'').$user->get_field('nom');
$bourseTbs = $_SESSION['bourse']['nom_bourse'];


/** Var TBS communes
*/
$eDepotArtTbs	= S_DEPOT_ART;
$eNouvDepotTbs 	= S_NOUV_DEPOT;
$eFinDepotTbs   = S_FIN_DEPOT;

$eGestionTbs	= S_GESTION;

$idBourseTbs    = $_SESSION['bourse']['idbourse'];
$nom_bourseTbs  = $_SESSION['bourse']['nom_bourse'];
$nomBourseTbs	= isset($_SESSION['bourse']['nom_bourse'])? $_SESSION['bourse']['nom_bourse']: '';
$nomAssocTbs	= isset($_SESSION['bourse']['nom_assoc'])? $_SESSION['bourse']['nom_assoc']: '';
$mayCaisseTbs 	= $user->get_field('may_caisse')=='T'?	true:false;
$mayDepotTbs 	= $user->get_field('may_depot')=='T'?	true:false;
$mayRetraitTbs 	= $user->get_field('may_retrait')=='T'?	true:false;
$mayGestionTbs 	= $user->get_field('may_gestion')=='T'?	true:false;

$dateTbs = date('d/m/Y');


/** Preparation de la machine d'etats
 */
if(isset($_REQUEST['st'])) {
	$st = $_REQUEST["st"];
} else {
    $st = S_MAIN;
}
/** Var globale aErr : messages d'erreurs
*/
$aErr = array();

/** Etats transitoires
*/
switch($st) {
	case T_SAUV_DEPOSANT:
	    require_once 'inc/utl_deposant.inc.php';
	    $id_deposant = sav_deposant();
	    if($id_deposant !== FALSE) $st = S_DEPOT_ART;
	    else $st = S_NOUV_DEPOT;
	    break;
	case T_ANNULVENTE:
	    /** Supprime une vente pour laquelle il n'y a pas d'articles
		 * Uniquement si user->uid correspond à la vente + ...
	    */
	    require_once 'inc/utl_caisse.inc.php';
	    annul_vente();
		header("Location: index.php");
	    exit();
	case T_ANNULDEPOT:
	    /** Supprime un depot pour lequel il n'y a pas d'articles
		 * Uniquement si user->uid correspond au depot + ...
	    */
	    require_once 'inc/utl_caisse.inc.php';
	    annul_depot();
			    
		header("Location: index.php");
	    exit();
	    
  case T_DEVEROUILLAGE:
		/** Deverouille une caisse
		*/
	    require_once 'inc/utl_caisse.inc.php';
	    deverouille_caisse();
		$st = S_DEVEROUILLAGE;
		break;
		
	case T_INS_PART:
	    /** Ajout d'un participant
	    */
	    require_once 'inc/utl_gest_part.inc.php';
		if (insert_part())  $st = S_GESTION_PARTICIPANTS;
		else $st = S_EDIT_PART;
	    break;

	case T_UPD_PART:
	    /** MaJ d'un participant
	    */
	    require_once 'inc/utl_gest_part.inc.php';
	    if (update_part()) $st = S_GESTION_PARTICIPANTS;
	    else $st = S_EDIT_PART;
	    break;
	    
	case T_DEL_PART:
	    require_once 'inc/utl_gest_part.inc.php';
	    delete_part();
		$st = S_GESTION_PARTICIPANTS;
		break;
		
	case T_CLOTURE_VENTE:
		/** cloture les ventes
		*/
	    require_once 'inc/utl_caisse.inc.php';
	    require_once 'inc/caisse.class.php';
	    cloture_vente();
		$st = S_CLOTURE_VENTE;
		break;
	
	case T_REINIT:
        /** Re-Initialisation de la db 
        */
        require_once 'inc/reinit.inc.php';
        $st = S_MAIN;
        break;
}

/** Etats stables
*/
switch($st) {
	case S_NOUV_DEPOT:
	    $sFile = 'deposant';
	    break;
	case S_DEPOT_ART:
	    $sFile = 'depot_art';
	    break;
	case S_FIN_DEPOT:
	    $sFile = 'fin_depot';
	    break;
	case S_CAISSE:
	    $sFile = 'caisse';
	    break;
	case S_RETRAIT:
	    $sFile = 'retrait';
	    break;
	case S_RETRAIT_SOLDE:
	    $sFile = 'retrait_solde';
	    break;
	case S_GESTION:
	    $sFile = 'gestion';
	    break;
	case S_CCAISSE:
	    $sFile = 'cpt_caisse';
	    break;
	case S_CRETRAIT:
	    $sFile = 'cpt_retrait';
	    break;
	case S_TABLBORD:
	    $sFile = 'tabl_bord';
	    break;
	case S_GESTION_PARTICIPANTS:
	    $sFile = 'gest_part';
	    break;
	case S_EDIT_PART:
	    $sFile = 'edit_part';
	    break;
	case S_DEVEROUILLAGE;
	    $sFile = 'dever';
	    break;
 	case S_FONDS_CAISSES:
	    $sFile = 'fonds_caisses';
	    break;

	case S_ARTICLE:
		$sFile = 'article';
		break;
	case S_LST_ART:
		$sFile = 'lst_art';
		break;
	case S_FICHE_ART:
		$sFile ='fiche_art';
		break;

	case S_CLOTURE_VENTE:		
		$sFile ='cloture_vente';
		break;

    case S_INIT_BOURSE:
        $sFile = 'init_bourse';
        break;
        
	case S_MAIN:
	default:
	    $sFile = 'main';
	    break;
}

// sortie HTML
$TBS = new clsTinyButStrong;
$TBS->LoadTemplate("tbs/$sFile.html") ;
// pour eventuels TBS->mergeBlock()
if (is_file("inc/$sFile.inc.php")) require_once "inc/$sFile.inc.php";

$TBS->Show(TBS_OUTPUT) ;
// Fin pgm
$db->close();
?>