<?php
/**
 * Module impression listing retour invendu et soldes
 *
 * @package: bourse
 * @author : FVdW
 * @version $Revision: 469 $
 *
 */
require 'fwlib/user.class.php';
require 'fwlib/sql.class.php';
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
if($user->get_field('may_gestion') != 'T') {
	echo "<html><body><h1>Erreur de droits</h1>Vous n'avez pas accès aux <b><i>Factures</i></b><br><center><button onclick='window.close()'>FERMER</button></center></body></html>";
	exit();
}

/** ouverture de la DB
*/
$db = new Cdb(DB_HOST, DB_NAME, DB_USER, DB_PWD);
$db->open();


/**
 * Boucle principale sur depots 
 */
$sql = "SELECT depot.*, deposant.nom, deposant.prenom, deposant.tel FROM depot 
LEFT JOIN deposant ON depot.deposant_iddeposant=deposant.iddeposant
WHERE IFNULL(date_retrait, '') = ''
AND bourse_idbourse={$_SESSION['bourse']['idbourse']}"; 
$n = $db->query($sql);
if (!$n) {
	exit("<html><body><h1>Erreur de donnée</h1>Aucun dépots à lister !....<!-- \n$sql\n--><br><br><center><button onclick='window.close()'>FERMER</button></center></body></html>");
}
$aDepot = $db->data;


// Format tableau
$hd = array( 
	array('text'=>'N°', 'w'=>20, 'align'=>'L', 'col'=>0, 'barre'=>true), 	// id Article 
	array('text'=>'', 'w'=>20, 'align'=>'L', 'col'=>1),				// etat
	array('text'=>'Description', 'w'=>130, 'align'=>'L', 'col'=>2),	// desc + motif retour
	array('text'=>'Prix Vente', 'w'=>20, 'align'=>'R', 'col'=>3),	// prix
);


//Instanciation de la classe dérivée
$pdf=new bPDF();
$pdf->setDestinataire(false); 

foreach($aDepot as $depot) {
  
	$pdf->setDepot($depot['iddepot'], $depot['nom'], $depot['prenom'], $depot['tel']);  
	$pdf->StartPageGroup();
  $pdf->AddPage();  
	/** liste des articles pour ce dépot
	 */
 	$sql = "SELECT IF( IFNULL(article.vente_idvente,0)=0, 'N', IF(retour.comment IS NULL,'Y','R')) AS vendu, retour.comment AS motif_retour, 
article.idarticle, article.depot_iddepot AS iddepot, article.prix_achat, article.prix_vente, article.description
FROM article 
LEFT JOIN retour ON article.retour_idretour=retour.idretour
WHERE depot_iddepot={$depot['iddepot']}
ORDER BY idarticle";
	$nbArticle = $db->query($sql);
	if($nbArticle) {
		$data = array();
		$ttlRbt = 0;
		$nbVendu = 0;
		foreach($db->data as $row){
			switch($row['vendu']){
				case 'Y':
					$etat ='Vendu';
					$ttlRbt += $row['prix_achat'];
					$desc = $row['description'];
					$prix = sprintf('%0.2f €', $row['prix_achat']);
					$nbVendu++;
					break;
				case 'N':
					$etat ='';
					$desc = $row['description'];
					$prix = '';
					break;
				case 'R':
					$etat ='Retourné';
					$desc = $row['description'].' - Motif: '. $row['motif_retour'];
					$prix = '';
					break;					
			}
			$id = $row['iddepot'].'-'.$row['idarticle'];
			$data[] = array($id, $etat, $desc, $prix, 'barre'=>($row['vendu']=='Y'? true:false));
		}
		// total -> tableau pdf (uniquement entete)
		$msgTTL = "Articles déposés : $nbArticle - vendus : $nbVendu";
		$pdf->Table($hd, $data, array('','',$msgTTL, sprintf('%0.2f €', $ttlRbt)));		
	} else {
		$pdf->Cell(0,0,"Aucun article déposé !");
	}
	$pdf->Ln(15);
	$pdf->SetFont('Arial','',15);
	// centrer
	$pdf->Cell((($pdf->w - 120)/2)-10);		
	$pdf->Cell(60,10,'Articles à restituer : '.($nbArticle - $nbVendu),1,0,'L');
	$pdf->Cell(60,10,'A payer : '.sprintf('%0.2f €', $ttlRbt),'RTB',0,'L');
	$pdf->ln();
	$pdf->SetFont('Arial','',12);		
}
$db->close();
$pdf->Output();
?>