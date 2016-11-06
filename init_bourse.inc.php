<?php 
/**
 * Include file: init_bourse.inc.php
 *
 * traitement : re initialisation de la db
 *
 * @package bourse
 * @version $Id$
 * @author FVdW
 */
 
/** recup�re les info de bourse courante pour TBS
 */
                                                         
$nom_assocTbs = $_SESSION['bourse']['nom_assoc']; 
$adr_assocTbs = $_SESSION['bourse']['adr_assoc'];
$nom_bourseTbs = $_SESSION['bourse']['nom_bourse'];
$marge = $_SESSION['bourse']['marge'];
$nombre_caisse = $_SESSION['bourse']['nombre_caisse'];

$hh_start_date = $_SESSION['bourse']['hh_start_date'];

if (!empty($hh_start_date) && !preg_match('/^0000-/', $hh_start_date)) {
	$hh_checkedTbs = 'checked="checked"';
} else {
	$hh_start_date = '';
	$hh_checkedTbs = '';
}
$fr_hh_start_date = preg_replace("/^(\d+)[-](\d+)[-](\d+)(.*)$/", '$3/$2/$1', $_SESSION['bourse']['hh_start_date']);
$fr_hh_start_time = preg_replace("/(\d+)[:](\d+)[:](\d+)$/", '$1:$2', $_SESSION['bourse']['hh_start_date']);

$a = array();
$tbs_select_times = '';
for($i = 8; $i < 22; $i++) {
	$t = "{$i}:00";
	$selected = ($t == $fr_hh_start_time)? 'selected="selected"':'';
	$tbs_select_times .= "<option value=\"{$t}\" {$selected}>{$t}</option>";
	$t = "{$i}:30";
	$selected = ($t == $fr_hh_start_time)? 'selected="selected"':'';
	$tbs_select_times .= "<option value=\"{$t}\" {$selected}>{$t}</option>";
}


$msg_fin_depotTBS =   empty($_SESSION['bourse']['msg_fin_depot'])? '': $_SESSION['bourse']['msg_fin_depot'];


// marge
$margeTbs = '';
for($i = 0.05; $i < 0.6; $i+= 0.05) {
    $i = round($i,2);
    $sel = ($i == $marge)? ' selected="selected"':'';
    $margeTbs .= "<option value=\"$i\"$sel>".($i*100)." %</option>\n";
}
// nombre de caisses
$nombre_caisseTbs = '';
for($i = 1; $i <= 10; $i++ ) {
    $sel = $i == $nombre_caisse? ' selected="selected"':'';
    $nombre_caisseTbs .= "<option value=\"$i\"$sel>$i</option>\n";
}
// Taux r�duction Happy Hour
$hh_ratesTbs = '';
for($i = 0.1; $i < 0.9; $i += 0.05) {
	$pc = $i * 100;
	$sel = $i == $_SESSION['bourse']['hh_rate']? 'selected="selected"':'';
	$hh_ratesTbs .= "<option value=\"$i\" $sel>$pc %</option>\n";
}
