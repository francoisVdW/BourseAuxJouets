<?php
/**
 * Include file: Compte des Restitutions .... après vente
 *
 * @package: bourse
 * @author : FVdW
 * @version $Id$
 *
 */

/** Verification droit "gestion"
 */
if($user->get_field('may_gestion') != 'T') {
	echo "<html><body><h1>Erreur de droits</h1>Vous n'avez pas acces à la fonction <b><i>compte des caisses</i></b><br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
	exit();
}

$sql = "SELECT depot.iddepot, depot.date_retrait, article.* , t.nom, t.prenom
FROM deposant t, depot, article 
WHERE depot.date_retrait IS NULL
AND depot_iddepot = depot.iddepot
AND depot.deposant_iddeposant = t.iddeposant
AND bourse_idbourse = ".$_SESSION['bourse']['idbourse']."
AND (Ifnull(article.vente_idvente,0) = 0 OR Ifnull(article.retour_idretour,0) != 0) 
ORDER BY depot.iddepot, article.idarticle";
$n = $db->query($sql);
if($n < 0) logFatal("\$n=$n\n\$sql=$sql\n",__FILE__, __LINE__);

$aDepot = array();
$aArt = array();
$prev = 0;
foreach($db->data as $r) {
	if($prev['iddepot'] != $r['iddepot'] && $prev) {
    	$aDepot[] = array('iddepot'=>$prev['iddepot'],'nom'=>$prev['nom'], 'prenom'=>$prev['prenom'], 'date_retrait'=>$prev['date_retrait'], 'art'=>$aArt, 'ttl'=>($aArt[0]['id']? count($aArt):null));
    	$aArt = array();
  	}
	$aArt[] = array('id'=>($r['idarticle']? '<span style="font-size:80%">'.$r['iddepot'].'</span>-<b>'.$r['idarticle'].'</b>':null), "code_couleur"=>$r['code_couleur'], 'description'=>$r['description']);
   	$prev = $r;
}
if(count($aArt)) {
	$aDepot[] = array('iddepot'=>$prev['iddepot'],'nom'=>$prev['nom'], 'prenom'=>$prev['prenom'], 'date_retrait'=>$r['date_retrait'], 'art'=>$aArt, 'ttl'=>($aArt[0]['id']? count($aArt):null));
}




//echo "<pre>";print_r($aDepot);echo"</pre>";

/** Variables TBS
 */
$TBS->MergeBlock('retraitTbs','array', $aDepot);
$TBS->MergeBlock('subTbs','array', 'aDepot[%p1%][art]');
// EoF