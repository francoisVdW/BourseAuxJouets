<?php
/**
 * Services Ajax
 *
 * @package: bourse
 * @author : FVdW
 * @version $Id$
 *
 */
require_once 'fwlib/JSON.php';
require_once 'fwlib/sql.class.php';
require_once 'fwlib/log.inc.php';
require_once 'inc/settings.php';

/**
 * toUTF8()
 *
 * @param string $s
 * @return string idem param $s mais SANS ACCENTS
 */
function toUTF8($s)
{
	// return strtr($s,"יטךכאהמןפצûüשח","eeeeaaiioouuuc");
	// return iconv("ISO-8859-1", "UTF-8//TRANSLIT", $s);
	return utf8_encode($s);
}



session_start();

$db = new Cdb(DB_HOST, DB_NAME, DB_USER, DB_PWD);
$db->open();


switch($_POST['op']) {
	case 'insArt':
	   	include('inc/utl_depot_art.inc.php');
	    $aReturn = ajax_insert_art();
	    break;
	case 'readArt':
	   	include('inc/utl_depot_art.inc.php');
	    $aReturn = ajax_read_art();
	    break;
	case 'updArt':
	   	include('inc/utl_depot_art.inc.php');
	    $aReturn = ajax_upd_art();
	    break;
	case 'lockArt':
	   	include('inc/utl_caisse.inc.php');
	    $aReturn = ajax_lock_art();
	    break;
	case 'unlockArt':
	   	include('inc/utl_caisse.inc.php');
	    $aReturn = ajax_unlock_art();
	    break;
	case 'sommeArt':
	   	include('inc/utl_caisse.inc.php');
	    $aReturn = ajax_somme_art();
	    break;
	case 'anVente':
	   	include('inc/utl_caisse.inc.php');
	    $aReturn = ajax_annul_vente();
	    break;
	case 'finVente':
	   	include('inc/utl_caisse.inc.php');
	    $aReturn = ajax_fin_vente();
	    break;
	case 'insFact':
	   	include('inc/utl_caisse.inc.php');
	    $aReturn = ajax_sav_fact('INS');
	    break;
	case 'updFact':
	   	include('inc/utl_caisse.inc.php');
	    $aReturn = ajax_sav_fact('UPD');
	    break;
	case 'rechDesc':
	   	include('inc/utl_caisse.inc.php');
	    $aReturn = ajax_rech_desc();
	    break;
	// article.inc.php    
	case 'rechInfoArt':
		include('inc/utl_article.inc.php');
		$aReturn = ajax_rech_info_art();
		break;
	case 'updDesc':
		include('inc/utl_article.inc.php');
		$aReturn = ajax_upd_desc_art();
		break;

	case 'delArt':
	   	include('inc/utl_depot_art.inc.php');
	    $aReturn = ajax_del_art();
	    break;
	case 'searchDepot':
	   	include('inc/utl_retrait.inc.php');
	    $aReturn = ajax_search_depot();
	    break;
	case 'soldeDepot':
	   	include('inc/utl_retrait.inc.php');
	    $aReturn = ajax_solde_depot();
	    break;
/*	case 'assistDesc':
	   include('inc/utl_depot_art.inc.php';
	    $aReturn = ajax_assist_desc_art();
	    break;
*/	    
	case 'assistFact':
	   include('inc/utl_gestion.inc.php');
	    $aReturn = ajax_assist_fact();
	    break;
	case 'assistDepot':
	   	include('inc/utl_gestion.inc.php');
	    $aReturn = ajax_assist_depot();
	    break;

}
$db->close();
if (isset($aReturn ) && is_array($aReturn)) {
	$json = new Services_JSON();
	$output = $json->encode($aReturn);
	header('Content-Type: text/html; charset=utf-8');
	header("X-JSON: ($output)");
} else echo "array not set ! op={$_POST['op']}";
exit();
?>
