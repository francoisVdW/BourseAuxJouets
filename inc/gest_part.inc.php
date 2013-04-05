<?php
/**
 * Include file: gest_part.inc.php
 *
 * Page de gestion des participants
 *
 * @package bourse
 * @version $Revision: 187 $
 * @author FVdW
 */

// Decl champs pour <form> deposant
require_once 'inc/utl_gest_part.inc.php';


/** Verification droit "gestion"
 */
if($user->get_field('may_gestion') != 'T') {
	echo "<html><body><h1>Erreur de droits</h1>Vous n'avez pas acces à la <i>gestion des participants</i></b><br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
	exit();
}

/** Acquisition des info participants
 */
$aCaisse = array();
$sql = "SELECT * FROM participant WHERE bourse_idbourse=".$_SESSION['bourse']['idbourse'];
$n = $db->query($sql);

$TBS->MergeBlock('participantsTbs','array', $db->data);
?>