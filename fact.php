<?php
/**
 * @package: bourse
 * @author : FVdW
 * @version $Revision: 187 $
 *
 */

/**
 * Module des factures
 *
 * @package: bourse
 * @author : FVdW
 *
 */
include_once 'fwlib/tbs_class_php5.php';
include_once 'fwlib/user.class.php';
include_once 'fwlib/sql.class.php';
require_once 'fwlib/log.inc.php';
require_once 'inc/settings.php';

session_start();

/** Connexion et ctrl login/pwd - Session
*/
if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
	echo "<html><body><h1>Erreur :</h1>Vous n'êtes pas connecté ! <br /><center><button onclick='window.close()'>FERMER</button></center></body></html>";
	exit();
}
$user = new User(DB_HOST, DB_NAME, DB_USER, DB_PWD);

/** Ctrl droits
*/
if($user->get_field('may_caisse') != 'T' && $user->get_field('may_gestion') != 'T') {
	echo "<html><body><h1>Erreur de droits</h1>Vous n'avez pas accès aux <b><i>Factures</i></b><br><center><button onclick='window.close()'>FERMER</button></center></body></html>";
	exit();
}

/**  Ctrl variable _GET
 */
if(!isset($_GET['idfacture'])) logFatal("_GET[idfacture] non defini",__FILE__,__LINE__);
$idfacture = $_GET['idfacture'];
if(!is_numeric($idfacture)) logFatal("\$idfactrue invalide [$idfacture]",__FILE__,__LINE__);



/** ouverture de la DB
*/
$db = new Cdb(DB_HOST, DB_NAME, DB_USER, DB_PWD);
$db->open();

/** Recupère le n° et date vente + info facture
*/
$sql = "SELECT v.*, f.* FROM vente v, facture f WHERE f.vente_idvente=v.idvente AND f.idfacture=$idfacture AND v.bourse_idbourse=".$_SESSION['bourse']['idbourse'];
$r = $db->select_one($sql);
if (!$r) {
	exit("<html><body><h1>Erreur de donnée</h1>Il n'existe pas de facture sous le n&deg; $idfacture<br><br><center><button onclick='window.close()'>FERMER</button></center></body></html>");
}
$r = $db->data[0];
$idvente    	= $r['idvente'];
$dateVenteTbs 	= $r['date_vente'];
$noCaisseTbs    = $r['no_caisse'];
$nomCliTbs 		= $r['nom_cli'];
$adr1Tbs 		= $r['adr1'];
$adr2Tbs 		= $r['adr2'];
$adr3Tbs 		= $r['adr3'];
$adr4Tbs 		= $r['adr4'];
$ttlTbs  		= $r['mnt_esp'] + $r['mnt_chq'] + $r['mnt_autr'];
$nomAssocTbs 	= $_SESSION['bourse']['nom_assoc']? $_SESSION['bourse']['nom_assoc']: ' ';
$nomBourseTbs 	= $_SESSION['bourse']['nom_bourse']? $_SESSION['bourse']['nom_bourse']: ' ';
$bourseTbs 		= $_SESSION['bourse']['idbourse'];

/** Recupère les articles de cette vente
*/
$sql = "SELECT * FROM article WHERE vente_idvente=$idvente";
$n = $db->query($sql);
if ($n == 0) {
	exit("<html><body><h1>Erreur de donnée</h1>Il n'existe d'articles vendus pour la facture n&deg; $idfacture<br><br><center><button onclick='window.close()'>FERMER</button></center></body></html>");
}

// sortie HTML
$TBS = new clsTinyButStrong;
$TBS->LoadTemplate("tbs/fact.html") ;
$TBS->MergeBlock('articlesTbs','array', $db->data);
$TBS->Show(TBS_OUTPUT) ;

// Fin pgm
$db->close();
?>