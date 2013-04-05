<?php
/**
 * Include file: depot
 *
 * @package: bourse
 * @author : FVdW
 * @version $Revision: 187 $
 *
 */
 
 
/** Verification droit d'enregistrer les d�pots
 */
if($user->get_field('may_depot') != 'T') {
	echo "<html><body><h1>Erreur de droits</h1>Vous n'avez pas acc�s au stand <b><i>D�pot</i></b><br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
	exit();
}
require_once 'inc/utl_deposant.inc.php';

// affichage
$nomTbs = $nom->printFieldEx('Nom');
$prenomTbs = $prenom->printFieldEx('Pr�nom');
$telTbs = $tel->printFieldEx('T�l�phone');

switch ($_SESSION['bourse']['adresse_deposant']) {
	case "OPTION":
	case "MANDATORY":
		$adr1Tbs = $adr1->printFieldEx('Adresse');
		$adr2Tbs = $adr2->printFieldEx('Suite...');
		$communeTbs = $commune->printFieldEx('Commune');
		$cpTbs = $cp->printFieldEx('Code Postal');
	    break;
	case "NONE":
		// affichage
		$adr1Tbs = '';
		$adr2Tbs = '';
		$communeTbs = '';
		$cpTbs = '';
	    break;
}

//$eMainTbs = S_MAIN;
$stTbs = T_SAUV_DEPOSANT;
$js_all_errTbs = get_JS_a_all_err($aErr);

?>
