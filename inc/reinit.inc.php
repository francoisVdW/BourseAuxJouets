<?php
/**
 * Include file: reinit.inc.php
 *
 * traitement : re initialisation de la db
 *
 * @package bourse
 * @version $Revision: 692 $
 * @author FVdW
 */

function ctrl_nom($post_var_name, $lbl='') 
{
    if(!empty($_POST[$post_var_name]))  {
    	$v = trim($_POST[$post_var_name]);
      if($v) return $v;
    }
    if(!$lbl) $lbl ="La variable $post_var_name";
    echo "<html><body><h1>Erreur de données</h1>$lbl doit être spéifié<br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
    exit();     
}

function ctrl_num($post_var_name, $lbl='')
{
		if(!$lbl) $lbl ="La variable $post_var_name";
    if(!empty($_POST[$post_var_name]))  {
    	$v = trim($_POST[$post_var_name]);
      if(is_numeric($v)) return $v; 
      else {
      	$msg = "n'est pas numérique";    
      }
    } else {                                             
    	$msg = "doit être spéifié";
    }
   	echo "<html><body><h1>Erreur de données</h1>$lbl $msg<br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";        
    exit();     
}

/** Verification droit "gestion"
 */
if($user->get_field('may_gestion') != 'T') {
	echo "<html><body><h1>Erreur de droits</h1>Vous n'avez pas acces à la <i>gestion des participants</i></b><br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
	exit();
}

/** Ctrl Post 
 */   
$marge = ctrl_num('marge', 'La marge');
$nb_caisse = ctrl_num('nombre_caisse', 'La nombre de caisses');
$nom_assoc = ctrl_nom('nom_assoc', 'Le nom de l\'association');
$nom_bourse = ctrl_nom('nom_bourse', 'Le nom de la bourse');
 
  
/** Controle pwd
*/
$sql = "SELECT count(*) AS cnt FROM participant WHERE bourse_idbourse={$_SESSION['bourse']['idbourse']} AND idparticipant={$_SESSION['uid']} AND pwd=MD5('{$_POST['pwd']}')";
$r = $db->select_one($sql);
if(empty($r)) {
	echo "<html><body><h1>Erreur Ctrl pwd ...</h1><br><a href='?st='". S_MAIN ."'>Retour au menu</a>\n<!-- $sql --></body></html>";
	exit();
}
if($r['cnt'] != 1) {
	echo "<html><body><h1>Erreur de contrôle</h1>Mot de passe invalide</i></b><br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
	exit();
}



/** RaZ de la DB
 */
if(!empty($_POST['ventes'])) {
    $sql = 'TRUNCATE TABLE facture;TRUNCATE TABLE log_caisse;TRUNCATE TABLE retour;TRUNCATE TABLE vente';
    $a = explode(';', $sql);
    foreach($a as $qry) {
        // echo $qry."\n<br>";
        if($db->query($qry) === false) exit("Erreur SQL $qry");
    } 

    if(!empty($_POST['depots'])) {
        $sql = 'TRUNCATE TABLE deposant;TRUNCATE TABLE depot;TRUNCATE TABLE article';
        $a = explode(';', $sql);
        foreach($a as $qry) {
            // echo $qry."\n<br>";
            if($db->query($qry) === false) exit("Erreur SQL $ $qry");
        }     
    }
}    
if(!empty($_POST['nom_bourse'])){
    $nom = $db->quote($_POST['nom_bourse']);
    $nom_assoc = $db->quote($nom_assoc);
    $adr_assoc = $db->quote($_POST['adr_assoc']);
    $nom_bourse = $db->quote($nom_bourse);
    $msg_fin_depot = $db->quote($_POST['msg_fin_depot']);
    
    $sql = "UPDATE bourse SET 
    	date_cloture_ventes=NULL,
      nom_assoc=$nom_assoc,
      adr_assoc=$adr_assoc,
      nom_bourse=$nom_bourse, 
      marge=$marge, 
      nombre_caisse=$nb_caisse, 
      fond_de_caisses='', 
      msg_fin_depot=$msg_fin_depot 
    WHERE idbourse={$_SESSION['bourse']['idbourse']} LIMIT 1;";   
    $n = $db->query($sql);
} 

/** Recuperation parametrage bourse (idbourse) apres connexion validee
*/
$sql = "SELECT * FROM bourse WHERE idbourse={$_SESSION['bourse']['idbourse']}";
$r = $db->select_one($sql);
if(empty($r)) exit("<html><body><h1>Erreur idbourse !</h1>\$n=$n<br><pre>sql=$sql</pre></body></html>");
$_SESSION['bourse'] = $r;
