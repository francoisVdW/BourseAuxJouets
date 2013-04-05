<?php
/**
 * Include file: caisse
 *
 * Page de saisie des ventes : la caisse !
 *
 * @package: bourse
 * @version $Revision: 187 $
 * @author FVdW
 */

require_once 'inc/utl_article.inc.php';

/** Verification droit "caisse"
 *
if($user->get_field('may_caisse') != 'T') {
	echo "<html><body><h1>Erreur de droits</h1>Vous n'avez pas accès au stand <b><i>Caisse</i></b><br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
	exit();
}
*/
/** Variables TBS
 */
// Etats
$js_all_errTbs 	= get_JS_a_all_err($aErr);

// tbs standards
$dateTbs = date('d/m/Y');
$nom_bourseTbs  = $_SESSION['bourse']['nom_bourse'];

?>