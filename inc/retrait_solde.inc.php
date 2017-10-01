<?php
/**
 * Include file: retrait_solde des articles
 *
 * Page du solde des articles
 *
 * @package bourse
 * @version $Id$
 * @author FVdW
 */

/** Verification droit "retrait"
 */
if($user->get_field('may_retrait') != 'T' && $user->get_field('may_gestion') != 'T') {
	echo "<html><body><h1>Erreur de droits</h1>Vous n'avez pas acces au stand <b><i>Restitution</i></b><br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
	exit();
}

/** Ctrl var _GET
*/
if(!(isset($_GET['no_depot']))) {
	logInfo("param _GET[no_depot] non defini", __FILE__, __LINE__);
	echo "<html><body><h1>Erreur</h1>(voir log)<br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
	exit();
} else $no_depot = $_GET['no_depot'];
if(!is_numeric($no_depot)) {
	logInfo("param _GET[no_depot] invalide [$no_depot]", __FILE__, __LINE__);
	echo "<html><body><h1>Erreur</h1>(voir log)<br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
	exit();
}

/** recherche info concernant le dépot et le deposant
*/
$sql = "SELECT d.*, u.* FROM depot d, deposant u
WHERE d.iddepot=$no_depot
AND u.iddeposant=d.deposant_iddeposant";
$n = $db->query($sql);
if(!$n) {
	// cas impossible !
	logInfo("Rechreche info concernant le dépot et le deposant.\n\$n=$n\n\$sql=$sql\n", __FILE__, __LINE__);
	echo "<html><body><h1>Erreur</h1>(voir log)<br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
	exit();
}
$DepotDeposant = $db->data[0];

/** recherche tous les articles de ce depot
*/
$sql = "SELECT article.*, If( ifnull(article.vente_idvente,0)=0, 'N', if(retour.comment is null,'Y','R')) AS vendu, retour.comment as motif_retour 
FROM article 
LEFT JOIN retour ON article.retour_idretour=retour.idretour
WHERE depot_iddepot=$no_depot
ORDER BY idarticle";
$n = $db->query($sql);


/** cumul des articles vendus
*/
$nbrVendu=0;
$ttlRemb=0;

$aArticles = array();
foreach($db->data as $r) {
	$desc = stripslashes($r['description']);
	if($r['vendu'] == 'Y') {
		$nbrVendu++;
		$ttlRemb += $r['prix_achat'];
		$vendu = "Vendu";
		$prix = sprintf("%.02f &euro;",$r['prix_achat']);
		$art = "<span style='font-size:0.85em;padding-left:15px'>{$r['depot_iddepot']}-{$r['idarticle']}</span>"; 
	} elseif($r['vendu'] == 'R'){
		$art = "<b>{$r['depot_iddepot']}-{$r['idarticle']}</b>"; 
 	    $vendu ="<b><i>RETOURN&Eacute;</i></b>";
 	    $prix = "&nbsp;";
		$desc .= "<br /><span style='color:red'>Motif : </span>".stripcslashes($r['motif_retour']);
 	} else {
		$art = "<b>{$r['depot_iddepot']}-{$r['idarticle']}</b>"; 
 	    $vendu ="&nbsp;";
 	    $prix = "&nbsp;";
	}
 	$aArticles[] = "<td>$art</td>
 	    <td>$vendu</td><td>$desc</td><td align='right'>$prix</td>";
}
if ($nbrVendu <= 1) {
    $s = "$nbrVendu article vendu";
} else {
    $s = "$nbrVendu articles vendus";
}
$aArticles[]="<td colspan='2'>&nbsp;</td><td align='right'>$s pour </td><td align='right'><b>".sprintf("%.02f ", $ttlRemb)."&euro;</b></td>";

$nbrArticles = count($db->data);
$nbrRetrait = $nbrArticles - $nbrVendu;
if (!$nbrRetrait) {
    $s = "Pas d'article à restituer";
}elseif ($nbrRetrait == 1) {
    $s = "$nbrRetrait article à restituer";
} else {
    $s = "$nbrRetrait articles à restituer";
}
$aArticles[]="<td colspan='2'>&nbsp;</td><td align='right'>$s</td><td >&nbsp;</td>";
$TBS->MergeBlock('articlesTbs','array', $aArticles);


require_once 'inc/utl_retrait.inc.php';
/** Variables TBS
 */
$coordDeposantTbs = trim('<b>'.$DepotDeposant['nom'].'</b> '.$DepotDeposant['prenom'].'<br />');
if($DepotDeposant['adresse']) $coordDeposantTbs .= $DepotDeposant['adresse'].'<br />';
if($DepotDeposant['adresse2']) $coordDeposantTbs .= $DepotDeposant['adresse2'].'<br />';
if($DepotDeposant['cp']) $coordDeposantTbs .= $DepotDeposant['cp'].' '.$DepotDeposant['commune'];

// -----------------------------------
// boutons pour menu
if($DepotDeposant["date_retrait"] || !$nbrArticles) {
  	$mayClotureTbs=false;
} else {
  	$mayClotureTbs=true;
}

if(isset($_GET['reprint'])) {
	$mayClotureTbs=false;
 	$eRetraitTbs = false;
} else $eGestionTbs=false;
// -----------------------------------
// champs Articles
$msgTbs = $nbrArticles? false : "Aucun articles trouves pour ce depot";
$noDepotTbs = $no_depot;
$uidTbs = $user->uid;

// -----------------------------------
// tbs standards
$dateTbs = date('d/m/Y');


// EoF