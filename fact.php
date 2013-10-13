<?php
/**
 * @package: bourse
 * @author : FVdW
 * @version $Revision: 187 $
 *
 */

/**
 * Module des factures
 *
 * @package: bourse
 * @author : FVdW
 *
 */
include 'fwlib/tbs_class_php5.php';
include 'fwlib/user.class.php';
include 'fwlib/sql.class.php';
require 'fwlib/log.inc.php';
require 'inc/settings.php';
require 'fwlib/bpdf.php';

session_start();

/** Connexion et ctrl login/pwd - Session
*/
if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
	echo "<html><body><h1>Erreur :</h1>Vous n'êtes pas connecté ! <br /><center><button onclick='window.close()'>FERMER</button></center></body></html>";
	exit();
}
$user = new User(DB_HOST, DB_NAME, DB_USER, DB_PWD);

/** Ctrl droits
*/
if($user->get_field('may_caisse') != 'T' && $user->get_field('may_gestion') != 'T') {
	echo "<html><body><h1>Erreur de droits</h1>Vous n'avez pas accès aux <b><i>Factures</i></b><br><center><button onclick='window.close()'>FERMER</button></center></body></html>";
	exit();
}

/**  Ctrl variable _GET
 */
if(!isset($_GET['idfacture'])) logFatal("_GET[idfacture] non defini",__FILE__,__LINE__);
$idfacture = $_GET['idfacture'];
if(!is_numeric($idfacture)) logFatal("\$idfactrue invalide [$idfacture]",__FILE__,__LINE__);



/** ouverture de la DB
*/
$db = new Cdb(DB_HOST, DB_NAME, DB_USER, DB_PWD);
$db->open();

/** Recupère le n° et date vente + info facture
*/
$sql = "SELECT v.*, f.* FROM vente v, facture f WHERE f.vente_idvente=v.idvente AND f.idfacture=$idfacture AND v.bourse_idbourse=".$_SESSION['bourse']['idbourse'];
$r = $db->select_one($sql);
if (!$r) {
	exit("<html><body><h1>Erreur de donnée</h1>Il n'existe pas de facture sous le n&deg; $idfacture<br><br><center><button onclick='window.close()'>FERMER</button></center></body></html>");
}
// Init var TBS (utilisées pour pdf si demandé)
$r = $db->data[0];
$idvente    	= $r['idvente'];
$dateVenteTbs 	= $r['date_vente'];
$noCaisseTbs    = $r['no_caisse'];
$nomCliTbs 		= $r['nom_cli'];
$adr1Tbs 		= $r['adr1'];
$adr2Tbs 		= $r['adr2'];
$adr3Tbs 		= $r['adr3'];
$adr4Tbs 		= $r['adr4'];
$ttlTbs  		= $r['mnt_esp'] + $r['mnt_chq'] + $r['mnt_autr'];
$nomAssocTbs 	= $_SESSION['bourse']['nom_assoc']? $_SESSION['bourse']['nom_assoc']: ' ';
$nomBourseTbs 	= $_SESSION['bourse']['nom_bourse']? $_SESSION['bourse']['nom_bourse']: ' ';
$bourseTbs 		= $_SESSION['bourse']['idbourse'];

/** Recupère les articles de cette vente
*/
$sql = "SELECT * FROM article WHERE vente_idvente=$idvente";
$n = $db->query($sql);
if ($n == 0) {
	exit("<html><body><h1>Erreur de donnée</h1>Il n'existe d'articles vendus pour la facture n&deg; $idfacture<br><br><center><button onclick='window.close()'>FERMER</button></center></body></html>");
}


$db->close();

// Sortie PDF
if(defined('PDF_DIR') && PDF_DIR) {
		define('FACT_TOP', 45); 
    // Format tableau
    $hd = array( 
      array('text'=>'N° Art', 'w'=>20, 'align'=>'C', 'col'=>0), 	// id Article
      array('text'=>'Qte', 'w'=>10, 'align'=>'C', 'col'=>1),  
      array('text'=>'Description', 'w'=>130, 'align'=>'L', 'col'=>2),	// desc 
      array('text'=>'Prix', 'w'=>20, 'align'=>'R', 'col'=>3),	// prix
    );
    $adr = trim($adr1Tbs.' '.$adr2Tbs);
    if(!empty($cpTbs) || !empty($communeTbs))
    	$adr .= "\n".trim($cpTbs.' '.$communeTbs);
      
    $articles = array();
    $i = 0;
    $ttl = 0;
    foreach($db->data as $r) {
      $i++;    	
      $articles[] = array("{$r['depot_iddepot']}-{$r['idarticle']}",1,$r['description'],  sprintf('%.02f €', $r['prix_vente']));
      $ttl += $r['prix_vente'];
    }
    // Ajout ligne VIDE
		$articles[] = array('', '', '', '- - - - - - - -');  

    $pdf=new bPDF();           
    $pdf->StartPageGroup();  
    $pdf->setDepot(False);				// ce n'est pas un depot
    $pdf->setDestinataire(False);	// ce n'est pas un depot                                    
    $pdf->AddPage();
    
    // Debut Facture    
    $pdf->SetFont('Arial','B',20);
    $pdf->setXY(60,30);         
    $pdf->Cell(80, 9, "Facture n° {$idfacture}", 1, 1, 'C');
    $pdf->SetFont('','',12);     
       
    // Coord Assoc       
    $pdf->setXY(10, FACT_TOP);  
    $pdf->MultiCell(80, 7, "{$nomAssocTbs}\n{$_SESSION['bourse']['adr_assoc']}", 0, 'L');
		    
    // Coord Client  
    $adr_client = trim($adr1Tbs);
    if(trim($adr2Tbs)) $adr_client .= "\n".$adr2Tbs;
    if(trim($adr3Tbs)) $adr_client .= "\n".$adr3Tbs;
		if(trim($adr4Tbs)) $adr_client .= "\n".$adr4Tbs;    
      
    $pdf->setXY(100, FACT_TOP);
    $pdf->MultiCell(80, 7, "FACTURE A:\n{$nomCliTbs}\n{$adr_client}", 0, 'L');
	  $pdf->ln();

    $pdf->Cell(200,7, "Date : ".date('d/m/Y')." - Caisse N° {$noCaisseTbs} - Vente N° {$idvente}",0,1,'L'); 
		$pdf->ln();    
    
    $pdf->Table($hd, $articles, array('','', 'TOTAL : ',sprintf('%.02f €', $ttl)));         

		$pdf->ln();
    $pdf->Cell(0,7, "TVA non applicable, article 293B du CGI.");                              
  
    
    $pdf->Output(PDF_DIR.'Fact'.sprintf('%03d',$idfacture).'.pdf');   
    $onloadTbs = 'alert(\'Document en phase d\\\'impression\')';
}

// sortie HTML
$TBS = new clsTinyButStrong;
$TBS->LoadTemplate("tbs/fact.html") ;
$TBS->MergeBlock('articlesTbs','array', $db->data);
$TBS->Show(TBS_OUTPUT) ;

?>