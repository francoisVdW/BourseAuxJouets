<?php
/**
 * Tableau de bord affiche différentes info a propos de l'etat des ventes / depots
 * Etat des caisses
 *
 * @package: bourse
 * @author : FVdW
 * @version $Id$
 *
 */

/** Verification droit d'acces au tableau de bord
 */
if($user->get_field('may_gestion') != 'T') {
	echo "<html><body><h1>Erreur de droits</h1>Vous n'avez pas acc_s au tableau de bord<br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
	exit();
}


/** Acquisition de l'etat des caisses
 */
$aCaisse = array();
$sql = "SELECT * FROM log_caisse WHERE bourse_idbourse=".$user->get_field('bourse_idbourse'). " AND logout_date IS NULL";
$n = $db->query($sql);

$aCaisse = array();
for($i=1; $i <= $_SESSION['bourse']['nombre_caisse']; $i++){
  $aCaisse[$i] = array('no_caisse'=>$i, 'img'=>'img/gris.gif', 'comment'=>'Fermée');
}
foreach($db->data as $r) {
  $aCaisse[$r['no_caisse']]['img'] = 'img/rouge.gif';
  $aCaisse[$r['no_caisse']]['comment'] = 'En activité depuis '.$db->date2str($r['login_date'],true). ' sur IP:<i>'.$r['ip'].'</i>';
}
$TBS->MergeBlock('caisseTbs','array', $aCaisse);


/** nbre de ventes/articles
*/
$sql = "SELECT count( * ) AS cnt
FROM article, depot
WHERE (vente_idvente IS NOT NULL AND vente_idvente !=0)
AND depot.iddepot = article.depot_iddepot
AND depot.date_retrait IS NULL
AND depot.bourse_idbourse=$idBourseTbs";
$n= $db->query($sql);
if($n != 1) logFatal("Erreur SQL \$n=$n\n\$ql=$sql\n",__FILE__,__LINE__);
$nbVendu = $db->data[0]['cnt'];

$sql = "SELECT count(*) AS cnt FROM article, depot WHERE depot.iddepot = article.depot_iddepot AND depot.date_retrait IS NULL AND depot.bourse_idbourse=$idBourseTbs";
$n= $db->query($sql);
if($n != 1) logFatal("Erreur SQL \$n=$n\n\$ql=$sql\n",__FILE__,__LINE__);
$nbArt = $db->data[0]['cnt'];

$nbRestant = $nbArt - $nbVendu;

if($nbArt) $pcVenduTbs = round(($nbVendu * 100) / $nbArt,0);
else $pcVenduTbs = 0;
$pcRestantTbs = 100-$pcVenduTbs;

/** nbre de depot/Restitutions
*/
$sql = "SELECT count(*) AS cnt FROM depot WHERE depot.bourse_idbourse=$idBourseTbs AND EXISTS (select 1 from article where article.depot_iddepot=depot.iddepot)";
$db->query($sql);
if($n != 1) logFatal("Erreur SQL \$n=$n\n\$ql=$sql\n",__FILE__,__LINE__);
$nbDepot = $db->data[0]['cnt'];

$sql = "SELECT count(*) AS cnt FROM depot WHERE date_retrait IS NOT NULL AND date_retrait != 0 AND bourse_idbourse=$idBourseTbs";
$db->query($sql);
if($n != 1) logFatal("Erreur SQL \$n=$n\n\$ql=$sql\n",__FILE__,__LINE__);
$nbSolde = $db->data[0]['cnt'];

$nbDepotRestant = $nbDepot - $nbSolde;
if($nbDepot) $pcSoldeTbs = round(($nbSolde * 100) / $nbDepot,0);
else $pcSoldeTbs = 0;
$pcDepotRestantTbs = 100-$pcSoldeTbs;

if (!empty($_SESSION['bourse']['hh_start_date'])) {
	$hhRateTbs = $_SESSION['bourse']['hh_rate']*100;
	$hhStartDateTbs = $_SESSION['bourse']['hh_start_date'];
} else {
	$hhRateTbs = false;
	$hhStartDateTbs = false;
}
// EoF
