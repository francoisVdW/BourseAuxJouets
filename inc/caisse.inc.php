<?php
/**
 * Include file: caisse
 *
 * Page de saisie des ventes : la caisse !
 *
 * @package bourse
 * @version $Id$
 * @author FVdW
 */
require_once 'inc/utl_caisse.inc.php';
require_once 'inc/caisse.class.php';

/** Verification droit "caisse"
 */
if($user->get_field('may_caisse') != 'T') {
	echo "<html><body><h1>Erreur de droits</h1>Vous n'avez pas accès au stand <b><i>Caisse</i></b><br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
	exit();
}


$caisse = new Caisse($db, $user->uid);
/** Gestion log_caisse - no_caisse - id_log
*/
if(isset($_GET['no_caisse'])) {
	/** no_caisse defini dans _GET : alors insertion d'un nouveau rec dans log_caisse
	 * et memorise les info de log
	 */
	$no_caisse = $_GET['no_caisse'];
	if(!is_numeric($no_caisse)) logFatal("\$no_caisse invalide", __FILE__,__LINE__);
	
	$caisse->query($no_caisse);		// AVANT MaJ log_caisse

	/** Ferme les log ouvert précedement par cet utilisateur pour cette caisse (sur cette même IP)
	*/
	$sql = "UPDATE log_caisse SET logout_date=Now() WHERE participant_idparticipant={$user->uid} AND ip='".getenv('REMOTE_ADDR')."' AND bourse_idbourse=".$_SESSION['bourse']['idbourse']." AND no_caisse=$no_caisse";
	$db->query($sql);

	/** Crée un nouveau log
	*/
	$sql = "INSERT INTO log_caisse (bourse_idbourse, no_caisse, login_date, participant_idparticipant, ip) VALUES (".$_SESSION['bourse']['idbourse'].", $no_caisse, Now(), ".$user->uid.", '".getenv('REMOTE_ADDR')."')";
	$id_log = $db->query($sql);
	if(!$id_log) logFatal("Erreur insert log_caisse\n\$sql=$sql", __FILE__,__LINE__);
	$_SESSION['no_caisse'] = $no_caisse;
	$_SESSION['id_log'] = $id_log;
} elseif (isset($_SESSION['no_caisse'])) {
    $no_caisse = $_SESSION['no_caisse'];
    $id_log = $_SESSION['id_log'];
    
	$caisse->query($no_caisse);
} else logFatal("_GET[no_caisse] / _SESSION[no_caisse] non defini", __FILE__,__LINE__);

if(!$caisse->is_owner()) {
	exit("Erreur ! vous vous connectez sur une caisse déjà occupée !<br><a href='?st=".S_MAIN."'>RETOUR</a>");
}

$aArticles = array();
$somme = 0;
/** Ctrl arg _GET[id_vente] : si existe et valide alors il faut relire les données
 */
if(isset($_GET['id_vente']) && is_numeric($_GET['id_vente'])) {
	$id_vente = $_GET['id_vente'];
	// vente cloturee ?
	$sql = "SELECT count(*) AS cnt FROM vente WHERE idvente=$id_vente AND date_vente IS NULL";
	$r = $db->select_one($sql);
	if(!$r) {
		// cloturee ou n'existe pas
		$id_vente=0;
	}
} else {
	// Nouvelle vente demandée ... vérifie si il existe une vente en erreur à reprendre (évite Pb <F5>)	
	$id_vente=$caisse->get_err_idvente(); // retourne 0 si pas d'erreur
}	

if($id_vente) {
	/** lecture des données dans la db
	*/
	$sql = "SELECT * FROM article WHERE vente_idvente=$id_vente";
	$n = $db->query($sql);
	if($n) {
		foreach($db->data as $r) {
			$aArticles[] = "<tr id='a{$r['idarticle']}' height='30'><td style='background-color:{$r['code_couleur']}'>{$r['depot_iddepot']}-<b>{$r['idarticle']}</b></td><td width='25'><img src=\"img/del.gif\" class=\"sup\" title=\"Retirer l'article de la liste\" onclick='unlock({$r['idarticle']}, false)' /></td><td>{$r['description']}</td><td align='right'>{$r['prix_vente']} &euro;</td></tr>";
			$somme += $r['prix_vente'];
  		}
 	}
} else {
	/** Nouvelle vente : creation record Vente
	*/
	$sql = "INSERT INTO vente (bourse_idbourse, participant_idparticipant, no_caisse) VALUES (".$user->get_field('bourse_idbourse').",".$user->uid.", $no_caisse)";
	$id_vente = $db->query($sql);
	if($id_vente <= 0) {
		echo "<html><body><h1>Erreur de DB</h1>la requete <pre>$sql</pre>retourne \$id_depot=$id_vente<br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
		exit();
	}
}
/** Memorise la derniere vente
*/
$sql = "UPDATE log_caisse SET last_idvente=$id_vente, date_last_op=Now() WHERE idlog_caisse=$id_log";
$db->query($sql);


/** Variables TBS
 */
$js_all_errTbs 	= get_JS_a_all_err($aErr);

$idArtTbs 		= $idArt->printField();
$idIdArtTbs 	= $idArt->getId();

// champs Articles
$TBS->MergeBlock('articlesTbs','array', $aArticles);
$nbArtTbs = count($aArticles);
$sommeTbs = sprintf("%.02f", $somme);

// champs pour mode de payement
$mntEspTbs 		= $mntEsp->printField("onkeypress='$(\"ibtn_fin\").disabled=true'","if(!this.value) this.value='0'");
$idMntEspTbs	= $mntEsp->getId();

$mntChqTbs 		= $mntChq->printField("onkeypress='$(\"ibtn_fin\").disabled=true'","if(!this.value) this.value='0'");
$idMntChqTbs	= $mntChq->getId();

// champs pour factures
$nomCliTbs      = $nomCli->printField();
$idNomCliTbs    = $nomCli->getId();

$adr1Tbs      = $adr1->printField();
$idAdr1Tbs    = $adr1->getId();

$adr2Tbs      = $adr2->printField();
$idAdr2Tbs    = $adr2->getId();

$adr3Tbs      = $adr3->printField();
$idAdr3Tbs    = $adr3->getId();

$adr4Tbs      = $adr4->printField();
$idAdr4Tbs    = $adr4->getId();

$paramFactTbs = "'&id_vente=$id_vente'+".getCChampAjaxParam($nomCli)."+".getCChampAjaxParam($adr1)."+".getCChampAjaxParam($adr2)."+".getCChampAjaxParam($adr3)."+".getCChampAjaxParam($adr4);


// tbs standards
$dateTbs = date('d/m/Y');
$nom_bourseTbs  = $_SESSION['bourse']['nom_bourse'];

?>
