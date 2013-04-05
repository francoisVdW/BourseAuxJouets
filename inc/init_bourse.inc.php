<?php 
/**
 * Include file: init_bourse.inc.php
 *
 * traitement : re initialisation de la db
 *
 * @package bourse
 * @version $Revision: 692 $
 * @author FVdW
 */
 
/** recupère les info de bourse courante pour TBS
 */
 
$nom_bourseTbs = $_SESSION['bourse']['nom_bourse'];
$marge = $_SESSION['bourse']['marge'];
$nombre_caisse = $_SESSION['bourse']['nombre_caisse'];
$msg_fin_depotTBS =   empty($_SESSION['bourse']['msg_fin_depot'])? '': $_SESSION['bourse']['msg_fin_depot'];


// marge
$margeTbs = '';
for($i = 0.05; $i < 0.6; $i+= 0.05) {
    $i = round($i,2);
    $sel = ($i == $marge)? ' selected="selected"':'';
    $margeTbs .= "<option value=\"$i\"$sel>".($i*100)." %</option>";
}
// nombre de caisses
$nombre_caisseTbs = '';
for($i = 1; $i <= 10; $i++ ) {
    $sel = $i == $nombre_caisse? ' selected="selected"':'';
    $nombre_caisseTbs .= "<option value=\"$i\"$sel>$i</option>";
}
