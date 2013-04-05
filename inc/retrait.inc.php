<?php
/**
 * Include file: retrait des articles
 *
 * Page de retrait des articles
 *
 * @package bourse
 * @version $Revision: 187 $
 * @author FVdW
 */


/** Verification droit "retrait"
 */
if($user->get_field('may_retrait') != 'T') {
	echo "<html><body><h1>Erreur de droits</h1>Vous n'avez pas accès au stand <b><i>Restitution</i></b><br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
	exit();
}

require_once 'inc/utl_retrait.inc.php';



/** Variables TBS
 */
$js_all_errTbs 	= get_JS_a_all_err($aErr);

$noDepotTbs 	= $noDepot->printField();
$idNoDepotTbs 	= $noDepot->getId();
$nNoDepotTbs    = $noDepot->getName();
// tbs standards
$dateTbs = date('d/m/Y');
$nom_bourseTbs  = $_SESSION['bourse']['nom_bourse'];

?>
