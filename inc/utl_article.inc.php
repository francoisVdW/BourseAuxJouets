<?php
/**
 * fonction communes page html & ajax pour caisse
 *
 * @package bourse
 * @version $Revision: 187 $
 * @author FVdW
 */
if(defined('UTL_ARTICLE')) return;
define('UTL_ARTICLE',1);
require_once 'fwlib/cchamp.class.php';
$idArt 		= new CChamp('NUM_O_idart');


/**
 * ajax_rech_info_art()
 *
 * @return array()
 */
function ajax_rech_info_art()
{
	global $db;
	$aRetValue = array();
	if (!isset($_POST['id_art']) || !is_numeric($_POST['id_art'])) {
		$aRetValue['a_err'] = "/;id_art invalide [{$_POST['id_art']}]";
		logInfo(__FUNCTION__."() POST[id_art] invalide ou non defini [{$_POST['id_art']}]",__FILE__,__LINE__);
	} else {
		$aRetValue = rech_art($_POST['id_art']);
	}	
	return $aRetValue;	
}


?>