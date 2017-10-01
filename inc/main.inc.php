<?php
/**
 * Include file: main.inc.php
 *
 * Page d'affichage du menu / accueil
 *
 * @package bourse
 * @version $Id$
 * @author FVdW
 */
 
/** Etat des caisses
*/
define ('FERME',0);
define ('OUVERT',1);
define ('USED',2);

require_once 'inc/caisse.class.php';


// ----------------------------------------------------------------------
// MAIN
//

/** Verification : caisses
*/
$caisse = new Caisse($db,$user->uid);
for($i=1; $i <= $_SESSION['bourse']['nombre_caisse']; $i++) {
	$caisse->query($i);
	$aCaisse[] = $caisse->get_tbs(); 
	
}
$TBS->MergeBlock('caisseTbs','array', $aCaisse);


/** Verification des clôtures : Depots & Ventes
*/
$alertClotureTbs = false;

/** Verification : depot pas cloturé !
*/
$sql = "SELECT depot.iddepot, count( article.idarticle ) AS nb
FROM depot
LEFT JOIN article ON depot_iddepot = iddepot
WHERE idparticipant_depot ={$user->uid}
AND date_depot IS NULL
AND date_retrait IS NULL
AND (
article.vente_idvente =0
OR article.vente_idvente IS NULL
)
AND depot.bourse_idbourse =".$_SESSION['bourse']['idbourse']."
GROUP BY depot.iddepot";
$n = $db->query($sql);
if ($n) {
	// Il existe des depots NON cloturés
	$alertClotureTbs = "<tr><td><b>Les dépôts suivants n'ont pas été clôturés !</b></td><td>&nbsp;</td></tr>\n";
	foreach($db->data as $r) {
		if($r['nb']) {
			$s = " pour {$r['nb']} article".($r['nb']>1? 's':'');
			$s.=" <a href='?st=".S_DEPOT_ART."&id_depot={$r['iddepot']}'>Reprendre</a>";
  		} else {
			$s =" <a href='?st=".T_ANNULDEPOT."&id_depot={$r['iddepot']}' title='Aucun articles pour ce dépot'>Annuler</a>";
		}
		$alertClotureTbs .= "<tr><td colspan='2'>Dépot n&deg;{$r['iddepot']} : $s</td><tr>\n";
	}
}
// EoF