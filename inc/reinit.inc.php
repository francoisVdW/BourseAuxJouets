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



/** Verification droit "gestion"
 */
if($user->get_field('may_gestion') != 'T') {
	echo "<html><body><h1>Erreur de droits</h1>Vous n'avez pas acces à la <i>gestion des participants</i></b><br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
	exit();
}

/** Ctrl Post 
 */
if(!is_numeric($_POST['marge'])) {
	echo "<html><body><h1>Erreur de données</h1>_POST[marge] non numérique ({$_POST['marge']})<br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
	exit();    
}
if(!is_numeric($_POST['nombre_caisse'])) {
	echo "<html><body><h1>Erreur de données</h1>_POST[nombre_caisse] non numérique ({$_POST['nombre_caisse']})<br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
	exit();    
}
 
  
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
    $msg_fin_depot = $db->quote($_POST['msg_fin_depot']);
    $sql = "UPDATE bourse SET date_cloture_ventes=NULL, nom_bourse=$nom, marge={$_POST['marge']}, nombre_caisse={$_POST['nombre_caisse']}, fond_de_caisses='', msg_fin_depot=$msg_fin_depot WHERE idbourse={$_SESSION['bourse']['idbourse']} LIMIT 1;";
    // echo $sql."\n<br>";
    $n = $db->query($sql);
} 

/** Recuperation parametrage bourse (idbourse) apres connexion validee
*/
$sql = "SELECT * FROM bourse WHERE idbourse={$_SESSION['bourse']['idbourse']}";
$r = $db->select_one($sql);
if(empty($r)) exit("<html><body><h1>Erreur idbourse !</h1>\$n=$n<br><pre>sql=$sql</pre></body></html>");
$_SESSION['bourse'] = $r;
