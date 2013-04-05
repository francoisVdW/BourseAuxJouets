<?php
/**
 * fonction communes page html & ajax pour caisse
 *
 * @package bourse
 * @version $Revision: 187 $
 * @author FVdW
 */
if(defined('UTL_CAISSE')) return;
define('UTL_CAISSE',1);
require_once 'fwlib/cchamp.class.php';



/** decl champs de saisie
*/
$idArt 	= new CChamp('NUM_O_idart');
$mntEsp = new CChamp('MNT_L_esp',0);
$mntChq = new CChamp('MNT_L_chq',0);

$nomCli = new CChamp('TXT_O_nomcli','',35);
$adr1 = new CChamp('TXT_L_adr1','',35);
$adr2 = new CChamp('TXT_L_adr2','',35);
$adr3 = new CChamp('TXT_L_adr3','',35);
$adr4 = new CChamp('TXT_L_adr4','',35);

/**
 * ajax_lock_art
 *
 * Update sur article demandé : vente_idvente <-- id_vente en cours
 * Si retrour update != 1 alors requete pour dtm l'erreur
 */
function ajax_lock_art()
{
	global $db;
	$aRetValue = array();
	if (!isset($_POST['id_art']) || !is_numeric($_POST['id_art'])) {
		$aRetValue['a_err'] = "/;id_art invalide [{$_POST['id_art']}]";
		logInfo("POST[id_art] invalide ou non defini [{$_POST['id_art']}]",__FILE__,__LINE__);
 	} elseif (!isset($_POST['id_vente']) || !is_numeric($_POST['id_vente'])) {
		$aRetValue['a_err'] = "/; POST[id_vente] invalide [{$_POST['id_vente']}]";
		logInfo("_POST['id_vente'] invalide ou non defini [{$_POST['id_vente']}]",__FILE__,__LINE__);
	} else {
		// pose du lock
		$sql = "UPDATE article SET vente_idvente={$_POST['id_vente']} WHERE idarticle={$_POST['id_art']} AND (vente_idvente IS NULL OR vente_idvente=0) AND NOT EXISTS (select 1 from depot where iddepot=article.depot_iddepot and date_retrait is not null )";
		$n = $db->query($sql);
		if(!$n) {
			// pas de lock posé : verifie existance de l'article
			$sql = "SELECT * FROM article WHERE idarticle={$_POST['id_art']}";
			$r = $db->select_one($sql);
			if($r) {
				if($db->data[0]['vente_idvente']) {
                    $aRetValue['a_err'] = "/;Attention cet article est deja vendu !\narticle {$db->data[0]['depot_iddepot']}-{$_POST['id_art']} : {$db->data[0]['description']}";
		  		} else {
                    $aRetValue['a_err'] = "/;Attention cet article a ete retire !\narticle {$db->data[0]['depot_iddepot']}-{$_POST['id_art']} : {$db->data[0]['description']}";
				}
  			} else {
		    	$aRetValue['a_err'] = "/;Pas d'article trouve sous le n° {$_POST['id_art']}";
			}
		} elseif($n == 1) {
			// OK article 'locké' (réservé)
			$sql = "SELECT * FROM article WHERE idarticle={$_POST['id_art']} AND vente_idvente={$_POST['id_vente']}";
			$r = $db->select_one($sql);
			if($r) {
			    $aRetValue['op'] = 'searchArt'; // indique aux JS : retour read
			    $aRetValue['id_art'] = $_POST['id_art'];
			    $aRetValue['id_depot'] = $r['depot_iddepot'];
			    $aRetValue['code_couleur'] = $r['code_couleur']? $r['code_couleur']:'white';
			    $aRetValue['description'] = stripslashes(utf8_encode($r['description']));
			    $aRetValue['prix_vente'] = sprintf("%.02f",$r['prix_vente']);
		    } else {
				// ERREUR ! lock posé mais ne retrouve pas l'article !
				logInfo("Erreur indeterminée !\nid_art={$_POST['id_art']}, \nid_vente={$_POST['id_vente']} uid={$_SESSION['uid']}\n\$sql=$sql",__FILE__, __LINE__);
				$aRetValue['a_err'] = '/;Erreur Soft ! (voir log)';
			}
  		}
	}
	return $aRetValue;
}


/**
 * ajax_unlock_art
 *
 * Update sur article demandé : vente_idvente <-- 0
 * Si retrour update != 1 alors requete pour dtm l'erreur
 */
function ajax_unlock_art()
{
	global $db;

	$aRetValue = array();
	if (!isset($_POST['id_art']) || !is_numeric($_POST['id_art'])) {
		$aRetValue['a_err'] = "/;id_art invalide [{$_POST['id_art']}]";
		logInfo("POST[id_art] invalide ou non defini [{$_POST['id_art']}]",__FILE__,__LINE__);
 	} elseif (!isset($_POST['id_vente']) || !is_numeric($_POST['id_vente'])) {
		$aRetValue['a_err'] = "/; POST[id_vente] invalide [{$_POST['id_vente']}]";
		logInfo("_POST['id_vente'] invalide ou non defini [{$_POST['id_vente']}]",__FILE__,__LINE__);
	} else {

		// retire le lock
		$sql = "UPDATE article SET vente_idvente=0 WHERE idarticle={$_POST['id_art']} AND vente_idvente={$_POST['id_vente']}";
		$n = $db->query($sql);

		if(!$n) {
			// pas de lock retiré : verifie existance de l'article
			$sql = "SELECT * FROM article WHERE idarticle={$_POST['id_art']}";
			$r = $db->select_one($sql);
			if($r) {
		    	$aRetValue['a_err'] = "/;Cet article n'est pas reserve !\narticle {$db->r['depot_iddepot']}-{$_POST['id_art']} : {$r['description']}";
  			} else {
		    	$aRetValue['a_err'] = "/;Pas d'article trouve sous le n° {$_POST['id_art']}";
			}
		} elseif($n == 1) {
			// OK article 'unlocké' (plus de réservation)
 			$aRetValue['op'] = 'unlockArt'; // indique aux JS : retour read
		    $aRetValue['id_art'] = $_POST['id_art'];
   		} else {
			// ERREUR ! lock posé mais ne retrouve pas l'article !
			logInfo("Erreur indeterminée !\nid_art={$_POST['id_art']}, \nid_vente={$_POST['id_vente']} uid={$_SESSION['uid']}n \$n=$n\n\$sql=$sql",__FILE__, __LINE__);
			$aRetValue['a_err'] = '/;Erreur Soft ! (voir log)';
  		}
	}
	return $aRetValue;
}

/**
 * ajax_somme_art
 *
 */
function ajax_somme_art()
{
	global $db;
	$aRetValue = array();
 	if (!isset($_POST['id_vente']) || !is_numeric($_POST['id_vente'])) {
		$aRetValue['a_err'] = "/; POST[id_vente] invalide (voir log)";
		logInfo("ajax_somme_art(): _POST['id_vente'] invalide ou non defini [{$_POST['id_vente']}]",__FILE__,__LINE__);
	} else {
		$sql = "SELECT Sum(prix_vente) AS somme,  count(*) AS cnt FROM article WHERE vente_idvente={$_POST['id_vente']}";
		$r = $db->select_one($sql);
		if($r) {
 			$aRetValue['op'] = 'sommeArt'; // indique aux JS : retour read
		    $aRetValue['id_vente'] = $_POST['id_vente'];
		    $aRetValue['somme'] = sprintf("%.02f",$db->data[0]['somme']);
		    $aRetValue['nb_art'] = $r['cnt'];
   		} else {
			// Erreur SQL
			logInfo("ajax_somme_art() : Erreur SQL !\nid_vente={$_POST['id_vente']} uid={$_SESSION['uid']}n \$n=$n\n\$sql=$sql",__FILE__, __LINE__);
			$aRetValue['a_err'] = '/;Erreur SQL ! (voir log)';
  		}
	}
	return $aRetValue;
}

/**
 * ajax_annul_vente
 *
 */
function ajax_annul_vente()
{
	global $db;
	$aRetValue = array();
 	if (!isset($_POST['id_vente']) || !is_numeric($_POST['id_vente'])) {
		$aRetValue['a_err'] = "/; POST[id_vente] invalide (voir log)";
		logInfo("ajax_annul_vente() : _POST['id_vente'] invalide ou non defini [{$_POST['id_vente']}]",__FILE__,__LINE__);
	} else {
		$id_vente = $_POST['id_vente'];
		/** ctrl vente NON cloturee
		*/
		$sql = "SELECT date_vente FROM vente WHERE idvente=$id_vente";
		$r = $db->select_one($sql);
		if(!$r) {
			$aRetValue['a_err'] = "/; Erreur SQL (voir log)";
		  	logInfo("ajax_annul_vente() : \$0 rec pour sql=$sql\n",__FILE__,__LINE__);
		  	return $aRetValue;
 		}
		if($r['date_vente']) {
			/** la vente a ete cloturee
			*/
            if($_POST['ret']=='raz') {
				$aRetValue['a_err'] = "/; Impossible d'annuler une vente deja cloturee";
			}
  		} else {
	  		/** Un raz a ete demande sur une vente non cloturee
	  		*/
	  		// 1- unlock des articles
			$sql = "UPDATE article SET vente_idvente=0 WHERE vente_idvente=$id_vente";
			$db->query($sql);
			// 2- annulation ou raz de la vente
			if($_POST['ret']=='raz') {
				// raz
				$sql = "UPDATE vente SET date_vente=NULL WHERE idvente=$id_vente";
			} else {
				// annulation
				$sql = "DELETE FROM vente WHERE idvente=$id_vente AND date_vente IS NULL";
	  		}
			$db->query($sql);
		}
  		if($_POST['ret']=='menu') {
			// cloture de la caisse
			if(!isset($_POST['id_log']) || !is_numeric($_POST['id_log'])) {
				$aRetValue['a_err'] = "/; POST[id_log] invalide (voir log)";
				logInfo("Erreur : id_log invalide : [{$_POST['id_log']}]",__FILE__,__LINE__);
	  		}
			$sql ="UPDATE log_caisse SET logout_date=Now() WHERE idlog_caisse=".$_POST['id_log'];
			$db->query($sql);
		}

		$aRetValue['op'] = 'anVente'; // indique aux JS : retour annulation vente
		$aRetValue['ret'] = $_POST['ret']; // retourne au Menu ou RaZ ou unload
	}
	return $aRetValue;
}

/**
 * ajax_fin_vente
 *
 */
function ajax_fin_vente()
{
	global $db;
	$aRetValue = array();
 	if (!isset($_POST['id_vente']) || !is_numeric($_POST['id_vente'])) {
		$aRetValue['a_err'] = "/; POST[id_vente] invalide (voir log)";
		logInfo("ajax_fin_vente() : _POST['id_vente'] invalide ou non defini [{$_POST['id_vente']}]",__FILE__,__LINE__);
	} elseif(!isset($_POST['esp']) || !is_numeric($_POST['esp'])) {
		$aRetValue['a_err'] = "/; POST[esp] invalide (voir log)";
		logInfo("ajax_fin_vente() : _POST['esp'] invalide ou non defini [{$_POST['esp']}]",__FILE__,__LINE__);
	} elseif(!isset($_POST['chq']) || !is_numeric($_POST['chq'])) {
		$aRetValue['a_err'] = "/; POST[chq] invalide (voir log)";
		logInfo("ajax_fin_vente() : _POST['chq'] invalide ou non defini [{$_POST['chq']}]",__FILE__,__LINE__);
	} else {
		// assing var
		$id_vente = $_POST['id_vente'];
		$chq = $_POST['chq'];
		$esp = $_POST['esp'];
		// traitement SQL
		// Verifie validite des payements
		$sql = "SELECT Sum(prix_vente) AS somme FROM article WHERE vente_idvente=$id_vente";
		$r = $db->select_one($sql);
		if(!$r) {
			// Erreur SQL
			logInfo("ajax_somme_art() : Erreur SQL !\nid_vente=$id_vente\n0 rec trouves\n\$sql=$sql",__FILE__, __LINE__);
			$aRetValue['a_err'] = '/;Erreur SQL ! (voir log)';
			return $aRetValue;
		}
		$ttl = round($r['somme'],2);
		if($ttl != ($chq+$esp)) {
			$aRetValue['a_err'] = "/;La somme des cheques($chq) + especes($esp) ne correspond pas au total ($ttl)";
			return $aRetValue;
		}
		$sql = "UPDATE vente SET mnt_esp=$esp, mnt_chq=$chq, date_vente=Now() WHERE idvente=$id_vente";
		$n = $db->query($sql);
		if($n != 1) {
			logInfo("ajax_fin_vente() : echec enregistrement vente !\n\$n=$n\n\$sql=$sql",__FILE__,__LINE__);
			$aRetValue['a_err'] = '/;L\'enregistrement de la vente a echoue ! \n()voir log))';
			return $aRetValue;
  		}
		$aRetValue['op'] = 'finVente'; // indique aux JS : retour read
	    $aRetValue['id_vente'] = $_POST['id_vente'];
	}
	return $aRetValue;
}


/**
 * ajax_sav_fact
 *
 */
function ajax_sav_fact($act)
{
	global $db;
	global $nomCli,$adr1, $adr2, $adr3, $adr4;
	$aRetValue = array();

 	if (!isset($_POST['id_vente']) || !is_numeric($_POST['id_vente'])) {
		$aRetValue['a_err'] = "/; POST[id_vente] invalide (voir log)";
		logInfo("ajax_fin_vente() : _POST['id_vente'] invalide ou non defini [{$_POST['id_vente']}]",__FILE__,__LINE__);
	}
	elseif (!$nomCli->chkPost()) $aRetValue['a_err'] = $nomCli->getErr();
	elseif (!$adr1->chkPost()) 	$aRetValue['a_err'] = $adr1->getErr();
	elseif (!$adr2->chkPost()) 	$aRetValue['a_err'] = $adr2->getErr();
	elseif (!$adr3->chkPost()) 	$aRetValue['a_err'] = $adr3->getErr();
	elseif (!$adr4->chkPost()) 	$aRetValue['a_err'] = $adr4->getErr();
	else {
		if($act=='UPD') {
		 	if (!isset($_POST['id_fact']) || !is_numeric($_POST['id_fact'])) {
				$aRetValue['a_err'] = "/; POST[id_fact] invalide (voir log)";
				logInfo("ajax_sav_fact() : _POST['id_fact'] invalide ou non defini [{$_POST['id_fact']}]",__FILE__,__LINE__);
	  		}
	  		$id_fact = $_POST['id_fact'];
  		}
	}
	if(count($aRetValue)) return $aRetValue;
	
	$id_vente = $_POST['id_vente'];
	if($act=='UPD') {
		$sql = "UPDATE facture SET nom_cli=".$nomCli->getDbVal().",".
		"adr1=".$adr1->getDbVal().",".
		"adr2=".$adr2->getDbVal().",".
		"adr3=".$adr3->getDbVal().",".
		"adr4=".$adr4->getDbVal()." WHERE idfacture=$id_fact";
		$n = $db->query($sql);
		if($n != 1) {
			$aRetValue['a_err'] = "/; Mise a jour non effectuee\n(voir log)";
			logInfo("ajax_sav_fact() : MaJ non effectuee\n\$n=$n\$sql=$sql",__FILE__,__LINE__);
		} else {
			$aRetValue['id_fact'] = $id_fact;
  		}
	} else {
		$sql = "INSERT INTO facture (vente_idvente, nom_cli, adr1, adr2, adr3, adr4)
		VALUES ($id_vente, ".$nomCli->getDbVal().",".
		$adr1->getDbVal().",".
		$adr2->getDbVal().",".
		$adr3->getDbVal().",".
		$adr4->getDbVal().")";
		$id_fact = $db->query($sql);
		if(!$id_fact) {
			$aRetValue['a_err'] = "/; Insert non effectue\n(voir log)";
			logInfo("ajax_sav_fact() : Insert non effectue\n\$n=$id_fact\n\$sql=$sql",__FILE__,__LINE__);
		} else {
			$aRetValue['id_fact'] = $id_fact;
  		}
	}
	return $aRetValue;
}

/**
 * ajax_rech_desc
 *
 */
function ajax_rech_desc()
{
	global $db;
	$aRetValue = array();
	if(isset($_POST['strict']) && $_POST['strict']) $strict = true;
	else $strict = false; 
 	if (!isset($_POST['pat']) || !trim($_POST['pat'])) {
		$aRetValue['a_err'] = "/; POST[pat] invalide (voir log)";
		logInfo("ajax_rech_desc(): _POST['pat'] vide ou non defini [{$_POST['pat']}]",__FILE__,__LINE__);
	} else {
		$pat = mysql_escape_string(utf8_decode(trim($_POST['pat'])));
	
		// recherche simple
		$sql = "SELECT idarticle, description, vente_idvente FROM article WHERE description like '$pat%'";		
		$n = $db->query($sql);
		if(!$n) {
			// 2e tentative tous les mots dans l'ordre  				
			// traitement des accent --> regexp = [ée] pour é
			$pat = str_replace(array('à','é','è','ê','î','ô','û','ù','%','&'),array('[àa]', '[ée]', '[èe]', '[êe]', '[îi]', '[ôo]', '[ûu]', '[ùu]', '', ''),$pat);
			$aPat = explode(' ',$pat); 
			$patEReg = "'(". join(').*(',$aPat).")'";
			// separe les mots, et cree string (mot1)|(mot2)...
			$sql = "SELECT idarticle, description, vente_idvente FROM article WHERE description REGEXP $patEReg";
			$n = $db->query($sql);
		}	
		if($n > 30) {
		  	$aRetValue['nb_opts']=1;
		  	$aRetValue['opts'] = "/==Trop de reponses";
		} elseif ($n == 0) {
		  	$aRetValue['nb_opts']=1;
		  	$aRetValue['opts'] = "/==Aucun article trouve";
  		} elseif ($n > 0) {
			$a = array();
			if($n==1) $a[] = "/==Un article trouve";
			else $a[] = "/==$n articles trouves";
			foreach ($db->data as $r) {
				if($r['vente_idvente'] && $strict) $a[]= "/==".utf8_encode($r['description'])." (Vendu)";
				else $a[]= $r['idarticle']."==".utf8_encode($r['description']);
	  		}
	  		$aRetValue['opts']= join(';;',$a);
		  	$aRetValue['nb_opts']=$n+1;
   		} else {
			// Erreur SQL
			$aRetValue['a_err'] = '/;Erreur SQL ! (voir log)';
  		}
	}
	return $aRetValue;
}

/**
 * annul_vente()
 * 
 * @return void
 */
function annul_vente()
{
	global $db;
    if(!isset($_GET['idvente'])) logFatal("_GET[idvente] non defini",__FILE__,__LINE__);
    $idvente = $_GET['idvente'];
    if(!is_numeric($idvente))  logFatal("\$idvente invalide [$idvente]",__FILE__,__LINE__);
    $sql = "DELETE FROM vente
	WHERE idvente=$idvente
	AND date_vente IS NULL
	AND participant_idparticipant={$user->uid}
	AND NOT EXISTS (select 1 from article where vente_idvente=$idvente)";
    $db->query($sql);	
}

/**
 * annul_depot()
 * 
 * @return void
 */
function annul_depot()
{
	global $db;
	global $user;
    if(!isset($_GET['id_depot'])) logFatal("_GET[id_depot] non defini",__FILE__,__LINE__);
    $iddepot = $_GET['id_depot'];
    if(!is_numeric($iddepot))  logFatal("\$iddepot invalide [$iddepot]",__FILE__,__LINE__);
    $sql = "DELETE FROM depot
	WHERE iddepot=$iddepot
	AND date_depot IS NULL
	AND date_retrait IS NULL
	AND idparticipant_depot={$user->uid}
	AND NOT EXISTS (select 1 from article where depot_iddepot=$iddepot)";
    $db->query($sql);	
}

/**
 * deverouille_caisse()
 * 
 * @return void
 */
function deverouille_caisse()
{
	global $db;
	
	if (!isset($_GET['id_log'])) logFatal("_GET[id_log] non défini", __FILE__,__LINE__);
	$id_log = $_GET['id_log'];
	if(!is_numeric($id_log))  logFatal("\$id_log invalide [$id_log]",__FILE__,__LINE__);
	$sql = "SELECT last_idvente FROM log_caisse WHERE idlog_caisse=$id_log";
	$last_idvente = $db->select_one($sql);
	
	$sql = "UPDATE log_caisse SET logout_date = Now() WHERE idlog_caisse=$id_log";
	$db->query($sql);
	
	if($last_idvente) {
		// Vérifie état de la dernière vente pour cette caisse
		$sql = "SELECT ifnull(date_vente,'/') AS date_vente FROM vente WHERE idvente=$last_idvente"; 		
		$date_vente = $db->select_one($sql);
		if($date_vente != '/' && $date_vente) {			
			// Vente non cloturée : Articles vendus ? 
			$sql = "SELECT count(*) AS cntFROM article WHERE vente_idvente=$last_idvente";	
			$cnt = $db->select_one($sql);
			if(!$cnt) {
				$db->query("DELETE FROM VENTE WHERE idvente=$last_idvente");
			}
		}
	}	
} 


/**
 * cloture_vente()
 * 
 * @return void
 */
function cloture_vente()
{
	
	/** Verification : caisses, ventes
	*/
	global $db;
	global $user;
	$canClotureVente=true;
	$caisse = new Caisse($db,$user->uid);
	for($i=1; $i <= $_SESSION['bourse']['nombre_caisse']; $i++) {
		$caisse->query($i);
		if(!$caisse->is_closed()) return; // au moins une caisse non fermée
	}
	$sql = "UPDATE bourse SET date_cloture_ventes=Now() WHERE idbourse={$_SESSION['bourse']['idbourse']}";
	$db->query($sql);
}
	

?>