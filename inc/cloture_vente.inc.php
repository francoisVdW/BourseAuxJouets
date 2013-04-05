<?php
/**
 * File : cloture_vente
 *
 * @package bourse
 * @author FVdW (vdw.francois@gmail.com)
 * @copyright 2009
 * @date 22/9/2009
 * @version $Revision: 187 $
 */
 
/** Etat des caisses
*/
define ('FERME',0);
define ('OUVERT',1);
define ('USED',2);
require_once 'inc/caisse.class.php';

if(!$mayGestionTbs) {
	header('Location: ?st='.S_MAIN);
	exit();
}

// ----------------------------------------------------------------------
// MAIN
//
/** Acquisition de l'etat des caisses
 */

/** Verification : caisses, ventes
*/
$canClotureVente=true;
$caisse = new Caisse($db,$user->uid);
for($i=1; $i <= $_SESSION['bourse']['nombre_caisse']; $i++) {
	$caisse->query($i);
	$aCaisse[] = $caisse->get_tbs(); 
	if(!$caisse->is_closed()) $canClotureVente = false;
}

/**
 * Ventes cloturées ? 
 */ 
if($caisse->get_etat() == 'cloture') {
	// Affiche btn listing des Rbt
	$tbsBtnListing=true;
} else {
	$tbsBtnListing=false;	
}

// TBS
$TBS->MergeBlock('caisseTbs','array', $aCaisse);
if($canClotureVente) {
	$tbsBtnCloture=true;
} else {
	$tbsBtnCloture=false;	
}
?>