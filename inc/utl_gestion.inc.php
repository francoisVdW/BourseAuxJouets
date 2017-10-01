<?php
/**
 * fonctions utiles pour factures (ajax)
 *
 * @package bourse
 * @version $Id$
 * @author FVdW
 */
if(defined('UTL_FACT')) return;
define('UTL_FACT',1);


/**
 * ajax_assist_fact
 */
function ajax_assist_fact()
{
	global $db;
	if (!is_numeric($_POST['id_bourse'])) {
	  	$aRetValue['a_err'] = "Erreur soft id_bourse invalide ({$_POST['id_bourse']})";
	  	return $aRetValue;
	}
	$sql = false;
	if(isset($_POST['num'])) {
	  	$num = trim($_POST['num']);
	  	if(!is_numeric($num)) {
			$aRetValue['a_err'] ="Le numero de facture est invalide";
			return($aRetValue);
		}
		$sql = "SELECT * FROM facture WHERE idfacture = $num";
	} else $num=false;

	if(isset($_POST['nom'])) {
		$nom = trim($_POST['nom']);
		if($nom=="") {
			$aRetValue['a_err'] ="Le nom doit etre renseigne";
			return($aRetValue);
		}
		$sql = "SELECT * FROM facture WHERE nom_cli LIKE ".Cdb::quote($nom.'%');
	} else $nom=false;
	
	$aRetValue['match'] = 0;
	if($sql) {
		$n = $db->query($sql);
		if($n) {
            $aRetValue['data'] = "<ul>";
			foreach($db->data as $r) {
				$fact = "Fact. n&deg;{$r['idfacture']} : <b>{$r['nom_cli']}</b> {$r['adr4']}";
  				$aRetValue['data'] .= "<li onclick='go_fact({$r['idfacture']})'>$fact&nbsp;</li>\n";
	  		}
            $aRetValue['data'] .= "</ul>";
            $aRetValue['match'] = 1;
  		}
	} else {
		$aRetValue['a_err'] ="Erreur soft (voir log)";
		logInfo("parametre nom et num NON definis ",__FILE__,__LINE__);
 	}
	return $aRetValue;
}

/**
 * ajax_assist_depot
 */
function ajax_assist_depot()
{
	global $db;
	if (!is_numeric($_POST['id_bourse'])) {
	  	$aRetValue['a_err'] = "Erreur soft id_bourse invalide ({$_POST['id_bourse']})";
	  	return $aRetValue;
	}
	$sql = false;
	if(isset($_POST['num'])) {
	  	$num = trim($_POST['num']);
	  	if(!is_numeric($num)) {
			$aRetValue['a_err'] ="Le numero de depot est invalide";
			return($aRetValue);
		}
		$sql = "SELECT d.*, a.nom FROM depot d, deposant a WHERE d.iddepot = $num AND d.deposant_iddeposant = a.iddeposant";
	} else $num=false;

	if(isset($_POST['nom'])) {
		$nom = trim($_POST['nom']);
		if($nom=="") {
			$aRetValue['a_err'] ="Le nom doit etre renseigne";
			return($aRetValue);
		}
		$sql = "SELECT d.*, a.nom FROM depot d, deposant a WHERE a.nom LIKE ".Cdb::quote($nom.'%')." AND d.deposant_iddeposant = a.iddeposant";
	} else $nom=false;

	$aRetValue['match'] = 0;
	if($sql) {
		$n = $db->query($sql);
		if($n) {
            $aRetValue['data'] = "<ul>";
			foreach($db->data as $r) {
				$depot = "Depot n&deg;{$r['iddepot']} : <b>{$r['nom']}</b>";
  				$aRetValue['data'] .= "<li onclick='go_depot({$r['iddepot']})'>$depot&nbsp;</li>\n";
	  		}
            $aRetValue['data'] .= "</ul>";
            $aRetValue['match'] = 1;
  		}
	} else {
		$aRetValue['a_err'] ="Erreur soft (voir log)";
		logInfo("parametre nom et num NON definis ",__FILE__,__LINE__);
 	}
	return $aRetValue;
}
// EoF