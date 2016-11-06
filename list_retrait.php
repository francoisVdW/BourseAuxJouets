<?php
/**
 * Module impression listing retour invendu et soldes
 *
 * @package: bourse
 * @author : FVdW
 * @version $Id$
 *
 */
require 'fwlib/user.class.php';
require 'fwlib/sql.class.php';
require 'fwlib/log.inc.php';
require 'inc/settings.php';
require 'fwlib/bpdf.php';


session_start();

/*
 * Traite UN d�pot
 */
function pdf_depot($pdf, $depot, $nbArticle, $dbData, $signature=false)
{
    // Format tableau
    $hd = array( 
        array('text'=>'N�', 'w'=>17, 'align'=>'L', 'col'=>0, 'barre'=>true), 	// id Article 
        array('text'=>'', 'w'=>15, 'align'=>'L', 'col'=>1, 'font-size' => 9),	// etat
        array('text'=>'Description', 'w'=>141, 'align'=>'L', 'col'=>2),			// desc + motif retour
        array('text'=>'Prix Net', 'w'=>17, 'align'=>'R', 'col'=>3),				// prix
    );

	$pdf->setDepot($depot['iddepot'], $depot['nom'], $depot['prenom'], $depot['tel']);  
	$pdf->StartPageGroup();
    $pdf->AddPage();  

	if($nbArticle) {
		$data = array();
		$ttlRbt = 0;
		$nbVendu = 0;
		foreach($dbData as $row){
			switch($row['vendu']){
				case 'Y':
					if ($row['prix_vente'] < $row['prix_vente_ori']) {
						$etat = 'Happy Hr';
					} else {
						$etat ='Vendu';
					}
					$ttlRbt += $row['prix_achat'];
					$desc = $row['description'];
					$prix = sprintf('%0.2f �', $row['prix_achat']);
					$nbVendu++;
					break;
				case 'N':
					$etat ='';
					$desc = $row['description'];
					$prix = '';
					break;
				case 'R':
					$etat ='Retour.';
					$desc = $row['description'].' - Motif: '. $row['motif_retour'];
					$prix = '';
					break;					
			}
			$id = $row['iddepot'].'-'.$row['idarticle'];
			$data[] = array($id, $etat, $desc, $prix, 'barre'=>($row['vendu']=='Y'? true:false));
		}
		// total -> tableau pdf (uniquement entete)
		$msgTTL = "Articles d�pos�s : $nbArticle - vendus : $nbVendu";
		$pdf->Table($hd, $data, array('','',$msgTTL, sprintf('%0.2f �', $ttlRbt)));		
	} else {
		$pdf->Cell(0,0,"Aucun article d�pos� !");
	}
	$pdf->Ln(10);
	$pdf->SetFont('Arial','',13);                 
    if ($signature) {              
        $pdf->Cell((($pdf->w - 180)/2)-10);		
        $pdf->Cell(60,10,'Articles � restituer : '.($nbArticle - $nbVendu),1,0,'L');
        $pdf->Cell(50,10,'A payer : '.sprintf('%0.2f �', $ttlRbt),'RTB',0,'L');
        $x = $pdf->getX();    
        $y = $pdf->getY();              
        if ($y+30+2 < $pdf->PageBreakTrigger) {
        	// Ne trace le cadre de la signature QUE s'il reste assez de place
        	$pdf->Cell(70,30,'',1,0); // trace cadre vide
        } else {
        	// trace cadre "r�duit"
            $h = $pdf->PageBreakTrigger - ($y+30+2);
            if ($h > 20) $pdf->Cell(70,$h,'',1,0); // trace cadre vide  
        }             
        $pdf->setXY($x, $y+5);	// positionne texte 'Signature...'
        $pdf->SetFont('Arial','',9);           
        $pdf->SetTextColor(100); 
        $pdf->Cell(60,0, 'Signature du vendeur');
        $pdf->SetTextColor(0);
    } else {
        // centrer
        $pdf->Cell((($pdf->w - 120)/2)-10);		
        $pdf->Cell(60,10,'Articles � restituer : '.($nbArticle - $nbVendu),1,0,'L');
        $pdf->Cell(60,10,'A payer : '.sprintf('%0.2f �', $ttlRbt),'RTB',0,'L');
    }    		
	$pdf->SetFont('Arial','',12);
}


/** ouverture de la DB
*/
$db = new Cdb(DB_HOST, DB_NAME, DB_USER, DB_PWD);
$db->open();

/** Connexion et ctrl login/pwd - Session
*/
if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
	echo "<html><body><h1>Erreur :</h1>Vous n'�tes pas connect� ! <br /><center><button onclick='window.close()'>FERMER</button></center></body></html>";
	exit();
}
$user = new User($db);

/** Ctrl droits
*/
if($user->get_field('may_gestion') != 'T') {
	echo "<html><body><h1>Erreur de droits</h1>Vous n'avez pas acc�s aux <b><i>Factures</i></b><br><center><button onclick='window.close()'>FERMER</button></center></body></html>";
	exit();
}


/**
 * Boucle principale sur depots 
 */
$sql = "SELECT depot.*, deposant.nom, deposant.prenom, deposant.tel FROM depot 
LEFT JOIN deposant ON depot.deposant_iddeposant=deposant.iddeposant
WHERE IFNULL(date_retrait, '') = ''
AND bourse_idbourse={$_SESSION['bourse']['idbourse']} ORDER BY iddepot"; 
$n = $db->query($sql);
if (!$n) {
	exit("<html><body><h1>Erreur de donn�e</h1>Aucun d�pots � lister !....<!-- \n$sql\n--><br><br><center><button onclick='window.close()'>FERMER</button></center></body></html>");
}
$aDepot = $db->data;

//Instanciation de la classe d�riv�e
$pdf=new bPDF();
 

foreach($aDepot as $depot) {
	/** liste des articles pour ce d�pot
	 */
 	$sql = "SELECT IF( IFNULL(article.vente_idvente,0)=0, 'N', IF(retour.comment IS NULL,'Y','R')) AS vendu, retour.comment AS motif_retour, 
article.idarticle, article.depot_iddepot AS iddepot, article.prix_achat, article.prix_vente, article.prix_vente_ori, article.description
FROM article 
LEFT JOIN retour ON article.retour_idretour=retour.idretour
WHERE depot_iddepot={$depot['iddepot']}
ORDER BY idarticle";
	$nbArticle = $db->query($sql);      
    if (LIST_RETRAIT_NB_EX > 1) {
        $pdf->setDestinataire('D�posant');     
        pdf_depot($pdf, $depot, $nbArticle, $db->data);

        $pdf->setDestinataire($_SESSION['bourse']['nom_assoc']);     
        pdf_depot($pdf, $depot, $nbArticle, $db->data, true);	// exemplaire assoc. + cadre signature
    } else {
		$pdf->setDestinataire(false);
        pdf_depot($pdf, $depot, $nbArticle, $db->data);    
    }   
}
$db->close();
$pdf->Output();
?>
