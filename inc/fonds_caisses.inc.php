<?php

/**
 * @author Francois VAN DE WEERDT
 * @copyright 2011
 */

function unserial($s) {
	$aa = explode(';', $s);
	$a = array();
	foreach($aa as $item) {
		list($no, $v) = explode('=', $item);
		$a[$no] = $v;
	}
	return $a;
}

/** Verification droit d'acces au tableau de bord
 */
if($user->get_field('may_gestion') != 'T') {
	echo "<html><body><h1>Erreur de droits</h1>Vous n'avez pas acc_s au tableau de bord<br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
	exit();
}

if(is_array($_POST['fond'])) {
	
	$aFonds = array();
	foreach($_POST['fond'] as $no=>$val) {
		if(!is_numeric($val)) $val=0;	// protection
		$aFonds[] = "$no=$val";
	}
	$s = join(';', $aFonds);		// s de la forme no_caisse=fond, .... 1=10.50;2=20;
	$sql = "UPDATE bourse SET fond_de_caisses='$s' WHERE idbourse=".$user->get_field('bourse_idbourse').' LIMIT 1';
	$db->query($sql);
}


/** Acquisition des fonds de caisses
 */
$sql = "SELECT fond_de_caisses, nombre_caisse FROM bourse WHERE idbourse=".$user->get_field('bourse_idbourse').' LIMIT 1';
$n = $db->query($sql);
if($n == 1) {
	if(empty($db->data[0]['fond_de_caisses'])){
		$aFonds= array_fill(1,$db->data[0]['nombre_caisse'], 0);
	} else {
		$aFonds = unserial($db->data[0]['fond_de_caisses']);
		if(!is_array($aFonds))	{
			logFatal("Erreur unserialize, \$db->data[0]['fond_de_caisses']=".print_r($db->data[0]['fond_de_caisses'],1),__FILE__,__LINE__);
			exit;
		}
	}
} else {
	logFatal("Pas d'information pour la bourse id=".$user->get_field('bourse_idbourse')."\nsql=$sql",__FILE__,__LINE__);
	exit;
}


/** Acquisition de l'etat des caisses
 */

$sql = "SELECT * FROM log_caisse WHERE bourse_idbourse=".$user->get_field('bourse_idbourse'). " AND logout_date IS NULL";
$n = $db->query($sql);

$aCaisse = array();
for($i=1; $i <= $_SESSION['bourse']['nombre_caisse']; $i++){
	$fond = empty($aFonds[$i])? 0 : $aFonds[$i];	
  	$aCaisse[$i] = array('no_caisse'=>$i, 'img'=>'img/gris.gif', 'comment'=>'Fermée', 'fond'=>$fond, 'input'=>"<input type='text' name='fond[$i]' value='$fond' size='8' maxlength='8' />&nbsp;&euro;");
}
foreach($db->data as $r) {
  $aCaisse[$r['no_caisse']]['img'] = 'img/rouge.gif';
  $aCaisse[$r['no_caisse']]['input'] = sprintf('%.02f &euro;', $aCaisse[$r['no_caisse']]['fond']);
  $aCaisse[$r['no_caisse']]['comment'] = 'En activité depuis '.Cdb::date2str($r['login_date'],true). ' sur IP:<i>'.$r['ip'].'</i>';
}
$TBS->MergeBlock('caisseTbs','array', $aCaisse);


?>