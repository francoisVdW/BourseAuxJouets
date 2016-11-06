<?php
/**
 * Include file: reinit.inc.php
 *
 * traitement : re initialisation de la db
 *
 * @package bourse
 * @version $Id$
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
	if (!$lbl) $lbl ="La variable $post_var_name";
    if (!empty($_POST[$post_var_name])) {
		$v = str_replace(',', '.', trim($_POST[$post_var_name]));
		if (is_numeric($v)) {
			return $v; 
		} else {
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

// Happy Hour
if (empty($_POST['fr_hh_start_date'])) {
	$hh_rate = 0;
	$hh_start_date = '';
} else {
	$hh_rate = ctrl_num('hh_rate', 'Le taux de réduction <em></em>Happy Hour</em>');
	$fr_hh_start_date = trim($_POST['fr_hh_start_date']);
	if (empty($fr_hh_start_date)) {
		$hh_start_date = false;
	} else {
		// ctrl validité
		if (!preg_match('/^\d+[\/]\d+[\/]\d+/', $fr_hh_start_date)) {
			echo "<html><body><h1>Erreur Ctrl date/heure <em>Happy Hour</em></h1>La date/heure de début du <em>happy hour</em> est invalide !<br><a href='?st='". S_MAIN ."'>Retour au menu</a>\n<!-- $hh_start_date vs ".date('Y-m-d H:i:59')." --></body></html>";
			exit();
		}
		$hh_start_date = preg_replace('/^(\d+).(\d+).(\d+)/', '$3-$2-$1', $fr_hh_start_date). ' '. $_POST['hh_start_time'].':00';
		if ($hh_start_date < date('Y-m-d H:i:59') ) {
			echo "<html><body><h1>Erreur Ctrl date/heure <em>Happy Hour</em></h1>La date/heure de début du <em>happy hour</em> ($hh_start_date) est dépassée !<br><a href='?st='". S_MAIN ."'>Retour au menu</a>\n<!-- $hh_start_date vs ".date('Y-m-d H:i:59')." --></body></html>";
			exit();
		}
		}
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
    $nom_assoc = $db->quote($nom_assoc);
    $adr_assoc = $db->quote($_POST['adr_assoc']);
    $nom_bourse = $db->quote($nom_bourse);
    $msg_fin_depot = $db->quote($_POST['msg_fin_depot']);
    if (!$hh_start_date) {
		$hh_start_date = 'Null';
	} else {
		$hh_start_date = "'$hh_start_date'";
	}
    $sql = "UPDATE bourse SET 
    	date_cloture_ventes=NULL,
      nom_assoc=$nom_assoc,
      adr_assoc=$adr_assoc,
      nom_bourse=$nom_bourse, 
      marge=$marge, 
      nombre_caisse=$nb_caisse, 
      fond_de_caisses='', 
      msg_fin_depot=$msg_fin_depot,
      hh_rate = $hh_rate,
      hh_start_date = $hh_start_date,
      hh_started = 0
    WHERE idbourse={$_SESSION['bourse']['idbourse']} LIMIT 1;";   
    $n = $db->query($sql); 
} 

/** Recuperation parametrage bourse (idbourse) apres connexion validee
*/
$sql = "SELECT * FROM bourse WHERE idbourse={$_SESSION['bourse']['idbourse']}";
$r = $db->select_one($sql);
if(empty($r)) exit("<html><body><h1>Erreur idbourse !</h1>\$n=$n<br><pre>sql=$sql</pre></body></html>");
$_SESSION['bourse'] = $r;
// EoF
