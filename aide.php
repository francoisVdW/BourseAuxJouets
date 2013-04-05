<?php
/**
 * @package: bourse
 * @author : FVdW
 * @version $Revision: 187 $
 *
 */
include_once 'fwlib/tbs_class_php5.php';

if(!isset($_GET['typ'])) logFatal("GET[typ] non defini",__FILE__,__LINE__);
$typ = $_GET['typ'];
/** lecture du fichier de contenu de l'aide demandée
*/
switch($typ) {
  case 'caisse';
    $a = @file('hlp/caisse.html');
    break;
  default:
    $a = array("<b>Attention</b> l'item [$typ] n'est pas reconnu !");
}
/** mise en forme de la var $contentTbs
 */
if($a && is_array($a)) {
   $contentTbs = implode("\n", $a);
} else {
  $contentTbs = "Erreur de lecture de l'aide";
}

// sortie HTML
$TBS = new clsTinyButStrong;
$TBS->LoadTemplate("tbs/aide.html") ;
$TBS->Show(TBS_OUTPUT) ;
?>
