<?php
/**
 * Include file: utl_retrait
 *
 * fonction communes page html & ajax pour retrait
 *
 * @package bourse
 * @version $Revision: 187 $
 * @author FVdW
 */
if(defined('UTL_RETRAIT')) return;
define('UTL_RETRAIT',1);
require_once 'fwlib/cchamp.class.php';


/** decl champs de saisie
*/
$noDepot 	= new CChamp('NUM_O_depot');
$nomDeposant= new CChamp('NOM_O_deposant','',35);

function ajax_search_depot()
{
	global $db;
	$aRetValue = array();
	global $noDepot;
	if (!$noDepot->chkPost()) return array('a_err'=>$noDepot->getErr());

	$sql = "SELECT * FROM depot WHERE iddepot=".$noDepot->getDbVal();
	$r = $db->select_one($sql);
	if(!$r) {
        $aRetValue['a_err']=utf8_encode("/;Il n'y a aucun dépôt sous le numéro ".$noDepot->getVal());
 	} else {
		if(isset($r['date_retrait']) && $r['date_retrait']!='0000-00-00 00:00:00') {
	        $aRetValue['a_err']= utf8_encode("/;Le dépôt numero ".$noDepot->getVal()."  a déjà été retiré le ".$db->date2str($r['date_retrait'],1));
 		} else {
			$aRetValue[$noDepot->getName()] = $noDepot->getVal();
			$aRetValue['date_depot'] = $r['date_depot'];
			$sql = "SELECT * FROM deposant WHERE iddeposant={$r['deposant_iddeposant']}";
			$r = $db->select_one($sql);
			if (!$r) {
	        	$fn = logInfo("ajax_search_depot() : 0 rec\n\$sql=$sql\n", __FILE__,__LINE__);
	        	$aRetValue['a_err']=utf8_encode("/;Erreur de données\n(voir log : $fn)");
	 		} else {
				$aRetValue["deposant"] = utf8_encode('<b>'.$r['nom'].'</b> '.$r['prenom']);
	   		}
   		}
  	}
  	return $aRetValue;
}


function ajax_solde_depot()
{
	global $db;
	$aRetValue = array();
	if(!isset($_POST['no_depot'])) {
	    $fn = logInfo("_POST[no_depot] non défini",__FILE__,__LINE__);
	    return array("a_err"=>"Erreur soft\n(voir log : $fn)");
	} $id_depot = $_POST['no_depot'];
	if(!is_numeric($id_depot)) {
	    $fn = logInfo("_POST[no_depot] invalide",__FILE__,__LINE__);
	    return array("a_err"=>"Erreur soft\n(voir log : $fn)");
	}
	if(!isset($_POST['uid'])) {
	    $fn = logInfo("_POST[uid] non défini",__FILE__,__LINE__);
	    return array("a_err"=>"Erreur soft\n(voir log : $fn)");
	} $id_u = $_POST['uid'];
	if(!is_numeric($id_u)) {
	    $fn = logInfo("_POST[uid] invalide",__FILE__,__LINE__);
	    return array("a_err"=>"Erreur soft\n(voir log : $fn)");
	}

	$sql = "UPDATE depot SET date_retrait=Now(), idparticipant_retrait=$id_u
		WHERE iddepot=$id_depot
		AND date_retrait IS NULL";
	$n = $db->query($sql);
	if($n==0) {
        $aRetValue['a_err'] = "Aucune mise a jour effectuee";
	} else {
	    $aRetValue['stat'] = "0";
	}
	return $aRetValue;
}
?>