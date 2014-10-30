<?php
/**
 *  Export data format XLS
 *
 * @package bourse
 * @author François Van de Weerdt (vdw.francois@gmail.com)
 * @copyright 2008
 * @version $Revision: 692 $
 * --------------------------------
 * @date 14/10/2008 : 14:44
 */

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
//
//     M A I N  (  )
//
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
require_once 'fwlib/user.class.php';
require_once 'inc/settings.php';
require_once 'fwlib/sql.class.php';
require_once 'fwlib/log.inc.php';
require_once 'fwlib/out_xls.php';

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Ctrl session /  connexion
session_start();


// ouverture de la DB
$db = new Cdb(DB_HOST, DB_NAME, DB_USER, DB_PWD);
$db->open();

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Connexion et ctrl login/pwd - Session
if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
	// Si pas connecte : Erreur
	exit("<html><title>Erreur de connexion</title><body>Erreur : connexion invalide</body></html>");
	}
$user = new User($db);
if($user->get_field('may_gestion') != 'T') {
	exit("<html><title>Erreur de connexion</title><body><h1>Erreur de droits</h1>Vous n'avez pas acces au tableau de bord</body></html>");
}
$nomParticipant = $user->get_field('nom');
$nomBourse = $_SESSION['bourse']['nom_bourse'];

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Ctrl param
if(!isset($_REQUEST['doc'])) {
	exit("<html><title>Erreur</title><body>Erreur : parametre manquant... <!-- doc non definbi --></body></html>");
}
$doc = trim($_REQUEST['doc']);// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -


$aFondsCaisses = null;
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// XLS


switch($doc) {
	case 'cpt_caisse':
		// Fond de caisses
		$sql = "SELECT fond_de_caisses FROM bourse WHERE idbourse ={$_SESSION['bourse']['idbourse']}";
		$n = $db->query($sql);
		if($n != 1) logFatal("\$n=$n\n\$sql=$sql\n",__FILE__, __LINE__);
		$aFondsCaisses = @unserialize($db->data[0]['fond_de_caisses']);
		if (!is_array($aFondsCaisses)) if($n != 1) logFatal("Erreur unserialize()  [{$db->data[0]['fond_de_caisses']}]",__FILE__, __LINE__);
		
		$sql = "SELECT no_caisse AS Caisse, 
		NULLIF(mnt_esp,0) AS Espece, 
		NULLIF(mnt_chq,0) AS Cheque, 
		NULLIF(mnt_autr, 0) AS autre,
		IFNULL(mnt_esp,0)+IFNULL(mnt_chq,0)+IFNULL(mnt_autr,0) AS Total, 
		get_fond({$_SESSION['bourse']['idbourse']}, no_caisse) AS fond,
		count(article.idarticle) AS 'Nbre Articles', DATE_FORMAT(vente.date_vente,'%d/%m/%Y %H:%i') AS date
		FROM vente, article
		WHERE bourse_idbourse =".$_SESSION['bourse']['idbourse']."
		AND vente.idvente=article.vente_idvente
		AND date_vente IS NOT NULL
		GROUP BY idvente ORDER BY no_caisse, idvente";
		break;
	case 'lst_art':
		$sql = "SELECT 
		d.date_depot AS 'Date du dépôt', 
		dd.nom AS 'Déposant', 
		Concat(d.iddepot,'-',a.idarticle) AS 'N°',
		a.description AS 'Description',
		a.prix_achat AS 'Achat',
		IF(v.date_vente, a.prix_vente, Null) AS 'Vente',
		v.date_vente AS 'Date Vente'
	FROM article a 
	LEFT OUTER JOIN vente v ON v.idvente=a.vente_idvente,
		depot d, deposant dd
	WHERE a.depot_iddepot=d.iddepot
	AND dd.iddeposant=d.deposant_iddeposant
	ORDER BY d.iddepot,a.idarticle";
		break;
	default:
		$sql = false;
		break;
}
$n = $db->query($sql);
if($n < 0) logFatal("\$n=$n\n\$sql=$sql\n",__FILE__, __LINE__);
$db->close();
if($n>0) do_xls($db, $aFondsCaisses);

// EoF
?>