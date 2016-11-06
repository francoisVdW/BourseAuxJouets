<?php
/**
 * Include file: depot
 *
 * Page de saisie des depots
 * @package: bourse
 * @author : FVdW
 * @version $Id$
 *
 */

/**
 * AVANT include "utl_depot_art.inc.php" acquisition des codes couleurs possibles
 */
if(!isset($_SESSION['optCouleur'])) {
    $aCode_couleur = $db->enum_list('article', 'code_couleur');
    if( is_array($aCode_couleur) ) {
        $_SESSION['optCouleur']= join('|', $aCode_couleur);

    } else {
      logFatal("Codes couleurs non trouvés : poour 'article', 'code_couleur'",__FILE__,__LINE__);
    }
}
require_once 'inc/utl_depot_art.inc.php';


/** Verification droit d'enregistrer les dépots
 */
if($user->get_field('may_depot') != 'T') {
    echo "<html><body><h1>Erreur de droits</h1>Vous n'avez pas accès au stand <b><i>Dépot</i></b><br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
    exit();
}



// -----------------------------------------------------------------------------
/** Nouveau depot (_REQUEST[id_depot] non defini)
 *            OU
 *  Edition depot existant (_REQUEST[id_depot] existe)
 */
if(isset($_REQUEST['id_depot'])) {
    $id_depot = $_REQUEST['id_depot'];
    if(!is_numeric($id_depot)) {
        echo "<html><body><h1>Erreur Soft</h1>param id_depot est invalide !<br>\$id_depot = [$id_depot]<br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
        exit();
    }
} else {
    $id_depot = FALSE;
}
// -----------------------------------------------------------------------------
$aArticles=array();
if($id_depot === FALSE) {
    // creation d'un dépot
    $sql = "INSERT INTO depot (bourse_idbourse, idparticipant_depot, deposant_iddeposant) VALUES (".$user->get_field('bourse_idbourse').",".$user->uid.", $id_deposant)";
    $id_depot = $db->query($sql);
    if($id_depot <= 0) {
        echo "<html><body><h1>Erreur de DB</h1>la requete <pre>$sql</pre>retourne \$id_depot=$id_depot<br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
        exit();
    }
} else {
    // recupere id_deposant
    $sql = "SELECT deposant_iddeposant FROM depot WHERE iddepot = $id_depot";
    $n = $db->query($sql);
    if($n != 1) {
        echo "<html><body><h1>Erreur de DB</h1>la requete <pre>$sql</pre>retourne \$n=$n<br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
        exit();
    }
    $id_deposant = $db->data[0]['deposant_iddeposant'];
    // lecture des articles du dépot
    $sql = "SELECT * FROM article WHERE depot_iddepot = $id_depot";
    $n = $db->query($sql);
    if($n < 0) {
        echo "<html><body><h1>Erreur de DB</h1>la requete <pre>$sql</pre>retourne \$n=$n<br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
        exit();
    }
    foreach($db->data as $r) {
        $aArticles[] = "<tr id='a".$r['idarticle']."'>".
        "<td style='background-color:".$r['code_couleur']."'><b>".$r['depot_iddepot'].'-'.$r['idarticle']."</b></td>".
        "<td class=\"price\">".sprintf('%0.2f', $r['prix_vente'])."</td>".
        "<td>".$r['description']."</td>".
        "<td>".(empty($r['happy_hour'])? '&nbsp;': '<img src="img/hh.gif" class="no-action" alt="Happy Hour" title="Happy Hour" />')."</td>".
        "<td class=\"price\">". sprintf('%0.2f', $r['prix_achat'])."</td>".
        "<td><img src='img/edit.gif' onclick='modif(".$r['idarticle'].")' title='Modifier' />".
        "<img src='img/del.gif' onclick='suppr(".$r['idarticle'].")' title='Supprimer' /></td></tr>";
    }
}
$nbArtTbs = count($aArticles);
$TBS->MergeBlock('articlesTbs','array', $aArticles);

// -----------------------------------------------------------------------------
// lecture des info déposant dans la db
$sql = "SELECT * FROM deposant WHERE iddeposant=$id_deposant";
$r = $db->select_one($sql);
if(!$r) {
    exit("<html><body><h1>Erreur de DB</h1>la requete <pre>$sql</pre>retourne 0 resultats<br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>");
}
$nomTbs     = $r['nom'];
$prenomTbs  = $r['prenom'];
$adr1Tbs    = $r['adresse'];
$adr2Tbs    = $r['adresse2'];;
$cpTbs      = $r['cp'];;
$communeTbs = $r['commune'];;
$telTbs     = $r['tel'];;


// -----------------------------------------------------------------------------
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


// -----------------------------------------------------------------------------
// TBS

$descTbs        = $description->printField("onchange='this.value=this.value.clrText()' tabindex='1'");
$paTbs          = $prix_achat->printField();
if (empty($_SESSION['bourse']['hh_start_date'])) {
	$happyhourTbs = false;
	$next_tab = 2;
} else {
	$happyhourTbs = $happy_hour->printField("tabindex='2'");
	$next_tab = 3;
}
$pvTbs          = $prix_vente->printField("onchange=\"calc()\" tabindex='{$next_tab}'");
$prev_id_artTbs = $prev_id_art->printField();
$couleursTbs    = $code_couleur->printHidden();

$idPaTbs            = $prix_achat->getId();
$idPvTbs            = $prix_vente->getId();
$idDescTbs          = $description->getId();
$idPrev_id_artTbs   = $prev_id_art->getId();
$idHappy_hourTbs	= $happy_hour->getId();
$idCouleursTbs      = $code_couleur->getId();


$margeTbs   = $_SESSION['bourse']['marge'];
// pour Ajax
$a =array();
$a[] = getCChampAjaxParam($description);
$a[] = getCChampAjaxParam($happy_hour);
$a[] = getCChampAjaxParam($code_couleur);
$a[] = getCChampAjaxParam($prix_achat);
$a[] = getCChampAjaxParam($prix_vente);
$a[] = getCChampAjaxParam($prev_id_art);
$paramUpdaterInsTbs = "var param='op=insArt&id_depot=$id_depot'+".implode('+', $a).";";
$paramUpdaterUpdTbs = "var param='op=updArt&id_depot=$id_depot'+".implode('+', $a).";";

$js_all_errTbs = get_JS_a_all_err($aErr);
$idBourseTbs = $_SESSION['bourse']['idbourse'];
?>
