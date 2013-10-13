<?php
/**
 * Include file: fin_depot
 *
 * Affiche le bon de dépot des articles
 *
 * @package bourse
 * @version $Revision: 692 $
 * @author FVdW
 */
include('fwlib/bpdf.php');


 
/** Verification droit d'enregistrer les dépots
 */
if($user->get_field('may_depot') != 'T' && $user->get_field('may_gestion') != 'T' ) {
	echo "<html><body><h1>Erreur de droits</h1>Vous n'avez pas acces au stand <b><i>Dépot</i></b><br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
	exit();
}

$msgTbs = '';
/** Ctrl arg get : id_depot
 */
if(!isset($_GET['id_depot'])) {
	echo "<html><body><h1>Erreur Soft</h1>_POST[id_depot] non defini !<br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
	exit();
}
$id_depot = $_GET['id_depot'];
if(!is_numeric($id_depot)) {
	echo "<html><body><h1>Erreur Soft</h1>_POST[id_depot] invalide [$id_depot] !<br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
	exit();
}


/** MaJ table depot : date et uid ... si pas de reprint demandé
*/
if(!isset($_GET['reprint'])) {
	$sql = "UPDATE depot SET date_depot=Now(), idparticipant_depot={$user->uid} WHERE iddepot=$id_depot";
	$n = $db->query($sql);
	if ($n != 1) {
		echo "<html><body><h1>Erreur Soft</h1>Problème DB voir log<br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
		logInfo("Erreur UPDATE depot... \$n=$n\n\$sql=$sql",__FILE__,__LINE__ );
		exit();
	}
} else {
  	// Ctrl : pas possible l'imprimer un bon de depot si retrait effectué
  	$sql = "SELECT count(*) AS cnt FROM depot WHERE iddepot=$id_depot AND date_retrait IS NOT NULL";
  	$r = $db->select_one($sql);
	if (!$r) {
		logInfo("Erreur SELECT count()... retourne 0 rec\n\$sql=$sql",__FILE__,__LINE__ );
		exit ("<html><body><h1>Erreur Soft</h1>Problème DB voir log<br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>");
		
	}
	if ($r['cnt']) {
		// le retrait a été effectué !
		$msgTbs = "Attention : le retrait a été effectué !";
 	}
}


/** Info deposant : jointure avec table depot, participant
*/
$sql = "SELECT a.*, DATE_FORMAT(d.date_depot,'%d/%m/%Y à %Hh%i') AS date_depot, p.nom AS nom_p, p.prenom AS prenom_p
FROM depot d, deposant a, participant p
WHERE d.iddepot=$id_depot AND d.deposant_iddeposant=a.iddeposant
AND p.idparticipant=d.idparticipant_depot";
$n = $db->query($sql);
if ($n != 1) {
	echo "<html><body><h1>Erreur Soft</h1>_POST[id_depot] non defini ! voir log<br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
	logInfo("\$n=$n\n\$sql=$sql",__FILE__,__LINE__ );
	exit();
}

$nomTbs 		= $db->data[0]['nom'];
$prenomTbs		= $db->data[0]['prenom'];
$adr1Tbs		= $db->data[0]['adresse'];
$adr2Tbs		= $db->data[0]['adresse2'];
$cpTbs			= $db->data[0]['cp'];
$communeTbs		= $db->data[0]['commune'];;
$telTbs			= $db->data[0]['tel'];
$date_depotTbs	= $db->data[0]['date_depot']? $db->data[0]['date_depot'] : '';
$nom_partTbs    = trim($db->data[0]['prenom_p'].' '.$db->data[0]['nom_p']);




// Sortie HTML =================================================================


// Etats : Si reprint alors pas d'item de menu pour nouveau depot et reprise (utilisation var TBS : magnet)
if(isset($_GET['reprint'])) {
	$eNouvDepotTbs	= false;
	$eDepotArtTbs	= false;
} else {
  	$eGestionTbs	= false;
}

/** Articles de ce dépot
*/
$articles = array();
$sql = "SELECT * FROM article WHERE depot_iddepot=$id_depot";
$n = $db->query($sql);
if (!$n) {
	$msgTbs = "Il n'y a pas d'article enregistré pour ce dépot !";
} else {
	foreach($db->data as $r) {
    	$pa = sprintf("%.02f &euro;", $r['prix_achat']);
    	$articles[] = "<td><b>{$r['depot_iddepot']}-{$r['idarticle']}</b></td><td>".htmlentities($r['description'])."</td><td align='right'>{$pa}</td>";
    }
}

$nbArtTbs = $n;
$TBS->MergeBlock('articlesTbs','array', $articles);
$TBS->MergeBlock('articlesTbs2','array', $articles);

// Msg d'info dépot (imprimé en bas du reçu)
$msg_fin_depotTBS = $_SESSION['bourse']['msg_fin_depot'];



// Sortie PDF ==================================================================
if(defined('PDF_DIR') && PDF_DIR) {
	    
    // Format tableau
    $hd = array( 
      array('text'=>'N°', 'w'=>18, 'align'=>'C', 'col'=>0, 'barre'=>true), 	// id Article 
      array('text'=>'Description', 'w'=>152, 'align'=>'L', 'col'=>1),	// desc 
      array('text'=>'Prix Vente', 'w'=>20, 'align'=>'R', 'col'=>2),	// prix
    );

    $adr = trim($adr1Tbs.' '.$adr2Tbs);
    if(!empty($cpTbs) || !empty($communeTbs))
    	$adr .= "\n".trim($cpTbs.' '.$communeTbs);
      
    $articles = array();
    $i = 0;
    foreach($db->data as $r) {
      $i++;    	
      $articles[] = array("{$r['depot_iddepot']}-{$r['idarticle']}",$r['description'],  sprintf('%.02f €', $r['prix_vente']));
    }

    $ttl = 'TOTAL : '. $i . ' article'. ($i > 1? 's':'');  

    $pdf=new bPDF();
    $pdf->setDepot($id_depot, $nomTbs, $prenomTbs, $telTbs, $adr, $date_depotTbs, $nom_partTbs);

    // Page à conserver ----------------------------------------------  
    $pdf->StartPageGroup();  
    $assoc = empty($_SESSION['bourse']['nom_assoc'])? 'Association': $_SESSION['bourse']['nom_assoc'];
    $pdf->setDestinataire($assoc);                                    
    $pdf->AddPage();
    
    // articles
    $pdf->Table($hd, $articles, array('', $ttl, ''));
    $pdf->ln();
    // Info participant
    $pdf->multiCell(0,5,"Dépôt enregistré par {$nom_partTbs} le {$date_depotTbs}.");
    $pdf->ln();
    $pdf->multiCell(0,5,"Le vendeur déclare avoir pris connaissance du règlement en vigueur.");
    
    $pdf->ln();
    // Signature déposant
    $pdf->SetFont('','I',9);
    $pdf->setX(120);
    $pdf->SetDrawColor(130);
    $pdf->multiCell(80,6,"Signature du déposant\n\n\n\n\n\n", 1, 'L');
    $pdf->SetDrawColor(0);
    $pdf->ln();
    $pdf->SetFont('','',10);
    // 

    // Page à donner au deposant --------------------------------------  
    $pdf->StartPageGroup();
    $pdf->setDestinataire("Déposant");
    $pdf->AddPage();
    
    // articles
    $pdf->Table($hd, $articles, array('',$ttl, ''));
    // Info participant
    $pdf->ln();
    $pdf->multiCell(0,5,"Dépôt enregistré par {$nom_partTbs} le {$date_depotTbs}.");
    $pdf->ln();
    $pdf->multiCell(0,5,"Le vendeur déclare avoir pris connaissance du règlement en vigueur.");
    $pdf->ln();
    $pdf->multiCell(0,5,$msg_fin_depotTBS);  // horaires de vente et restitution

    $pdf->Output(PDF_DIR.'D'.sprintf('%03d',$id_depot).'.pdf'); 
    
    $onloadTbs = 'alert(\'Document en phase d\\\'impression\')';
} else {
	$onloadTbs = 'window.print()';
    
}
?>