<?php
/**
 * Affiche tous les articles
 * 
 * @package: bourse
 * @author : FVdW
 * @version $Revision: 187 $
 *
 */
// require_once 'inc/utl_article.inc.php';

require_once 'fwlib/cchamp.class.php';

$description= new CChamp('TXT_O_desc', '', 250);
$comment= new CChamp('TXT_O_comment', '', 250);	// motif annulation vente

function err($msg){
	exit("<html><body onload=\"if(typeof parent.loading=='function')parent.loading(false);\">$msg</body></html>");

}

/**
 * rech_art()
 *
 * @param integer $idArt
 * @return array
 */
function rech_art($idArt)
{
	global $db;
	$aRetValue = array();
	
	$sql = "SELECT a.code_couleur, a.description, a.idarticle, a.prix_achat, a.prix_vente, 
	d.date_depot, d.date_retrait, d.iddepot, d.date_retrait,
	dd.nom, dd.prenom, dd.tel,
	v.date_vente, p1.nom AS part_vente, v.idvente,
	p2.nom AS part_depot,
	p3.nom AS part_retrait,
	r.idretour, p4.nom AS part_retour, r.date AS date_retour, r.comment AS motif_retour
FROM article AS a
LEFT OUTER JOIN vente AS v ON a.vente_idvente=v.idvente 
LEFT OUTER JOIN participant AS p1 ON v.participant_idparticipant=p1.idparticipant
LEFT OUTER JOIN retour AS r on a.retour_idretour=r.idretour 
LEFT OUTER JOIN participant AS p4 ON r.participant_idparticipant=p4.idparticipant,
depot AS d 
LEFT OUTER JOIN participant AS p3 ON d.idparticipant_retrait=p3.idparticipant,
deposant AS dd,
participant AS p2
WHERE a.idarticle = $idArt
AND a.depot_iddepot = d.iddepot
AND d.deposant_iddeposant = dd.iddeposant
AND d.idparticipant_depot=p2.idparticipant";	
	$r = $db->select_one($sql);
	if(!$r) {
		logInfo(__FUNCTION__."() POST[id_art] non trouve [{$_POST['id_art']}]\nsql=$sql\n",__FILE__,__LINE__);
		return array('a_err'=>"Article $idArt non trouvé");
	}
	$aRetValue['op'] = 'rechInfoArt'; 
	$aRetValue['id_art'] = $idArt;
    $aRetValue['no_art'] = $r['iddepot'].'-'.$idArt;
    $aRetValue['desc_art'] = stripslashes($r['description']);
    $aRetValue['nom_depos'] = $r['nom'];
    $aRetValue['prenom_depos'] = $r['prenom']? $r['prenom']:'';
    $aRetValue['tel_depos'] = $r['tel']? $r['tel']:'';    
    $aRetValue['px_vente'] = $r['prix_vente'];
    $aRetValue['px_achat'] = $r['prix_achat'];
    $aRetValue['no_depot'] = $r['iddepot']? $r['iddepot']:'';
    $aRetValue['date_depot'] = $db->date2str($r['date_depot'],true);
    $aRetValue['part_depot'] = $r['part_depot']? $r['part_depot']:'';
    $aRetValue['no_vente'] = $r['idvente']? $r['idvente']:'';
    $aRetValue['date_vente'] = $db->date2str($r['date_vente'],true);
    $aRetValue['part_vente'] = $r['part_vente']? $r['part_vente']:'';
    $aRetValue['date_retrait'] = $db->date2str($r['date_retrait'],true);
    $aRetValue['part_retrait'] = $r['part_retrait']? $r['part_retrait']:'';
    $aRetValue['code_couleur'] = $r['code_couleur']? $r['code_couleur']:'';
    $aRetValue['flgMayAnnulTbs'] = (!$r['idretour'] && $r['idvente'])? true:false;	// flag TBS pour indiquer si il faut afficher le <tr> annulation vente
    $aRetValue['date_retour'] = $db->date2str($r['date_retour'],1);
    $aRetValue['part_retour'] = $r['part_retour'];
    $aRetValue['motif_retour'] = stripcslashes($r['motif_retour']);
    
    return $aRetValue;	
}
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
//
//        M A I N  (  )
//
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
/** Verification droit 
 */
if($user->get_field('may_caisse') != 'T' && $user->get_field('may_gestion')!= 'T' && $user->get_field('may_admin')!= 'T') {
	echo "<html><body><h1>Erreur de droits</h1>Vous n'avez pas accès à cette fonction<br /><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
	exit();
}


// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
//  prépare palette de couleurs	
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
if(!isset($_SESSION['optCouleur'])) {
	$aCode_couleur = $db->enum_list('article', 'code_couleur');
	if( is_array($aCode_couleur) ) {
		$_SESSION['optCouleur']= join('|', $aCode_couleur);

	} else {
	  logFatal("Codes couleurs non trouvés : poour 'article', 'code_couleur'",__FILE__,__LINE__);
	}
}


// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
//  _GET	
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
if(!isset($_REQUEST['id_art'])) {
	// Exit !
	err("Erreur _Request['id_art']");
}
$idArt = $_REQUEST['id_art'];
if(!is_numeric($idArt))  {
	// Exit !
	err("Erreur _Request['id_art'] invalide [$idArt]");
}
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
//  Opération	
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
if(isset($_GET['op'])) {
	$op = trim($_GET['op']);
} else $op=false;

switch($op) {
	case 'updDescArt':
		$desc = $db->quote(substr($_POST['TXT_O_desc'],0,250));
		if(!$desc) break;
		$sql = "UPDATE article SET description=$desc WHERE idarticle=$idArt";
		$db->query($sql);
		break;
	case 'retourVente':
		$motif = $db->quote(substr($_POST['TXT_O_comment'],0,250));
		if(!$motif) break;
		$sql = "INSERT INTO retour (participant_idparticipant, date, comment) VALUES ({$user->uid}, Now(), $motif)";
		$id = $db->query($sql);
		if(!$id) {
			logInfo("Erreur SQL : sql=$sql",__FILE__,__LINE__);
		} else {
			$sql = "UPDATE article SET retour_idretour=$id WHERE idarticle=$idArt";
			$n= $db->query($sql);
			if($n != 1) {
				logInfo("Erreur SQL : n=$n, sql=$sql",__FILE__,__LINE__);
			}			
		}
		break;
	case 'setColArt':
		$col = $db->quote($_GET['col']);
		if(!$col) break;
		$sql = "UPDATE article SET code_couleur=$col WHERE idarticle=$idArt";
		$db->query($sql);
		break;
}



$r = rech_art($idArt);
if(isset($r['a_err'])) {
	// Exit !
	exit("<html><body onload='parent.loading(false);parent.alert(\"{$r['a_err']}\")'><b><span style='color:red'>Erreur : </span>{$r['a_err']}</b></body></html>");
}
$description->default_val = $r['desc_art'];
$comment->default_val = '';

// Couleurs
$sTblCouleurs ='<table width="100%" border="0" cellspacing="2"><tr>';
$cnt=0;
foreach(explode('|',$_SESSION['optCouleur']) as $coul) {
  $cnt++;
  $sTblCouleurs .= "<td width='33%' style='border:1px black solid;background-color:$coul' onclick='setCol(\"$coul\")'>&nbsp;&nbsp;</td>";
  if($cnt >= 3) {
	$sTblCouleurs .= "</tr>\n<tr>";
	$cnt = 0;
  }
}
// pas de couleur
if($cnt >= 2) {
	$sTblCouleurs .= "</tr>\n<tr>";
	$cnt = 0;
}	
$cnt +=2;
$sTblCouleurs .= "<td colspan='2' onclick='setCol(\"\")' style='border:1px black solid;'><span style='font-size:9px;'>Sans !</span></td>";

// termine le tableau
while($cnt < 3) {
  $sTblCouleurs .= "<td width='33%'>&nbsp;&nbsp;</td>";
  $cnt++;
}
$sTblCouleurs .= "</tr></table>\n";


?>