<?php
/**
 * Include file: article
 *
 * Page : affichage détail article
 *
 * @package: bourse
 * @version $Id$
 * @author FVdW
 */
require_once 'inc/utl_article.inc.php';


/** Variables TBS
 */
// Etats
$js_all_errTbs = get_JS_a_all_err($aErr);

// tbs standards
$dateTbs = date('d/m/Y');
$nom_bourseTbs  = $_SESSION['bourse']['nom_bourse'];

$urlArticleTbs = '';
if (!empty($_GET['id_article'])) {
    if (is_numeric($_GET['id_article'])) {
        $urlArticleTbs = "index.php?st=".S_FICHE_ART."&id_art={$_GET['id_article']}";
    }
}
