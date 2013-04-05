<?php
/**
 * Module impression listing retour invendu et soldes
 *
 * @package: bourse
 * @author : FVdW
 * @version $Revision: 469 $
 *
 */
include_once 'fwlib/user.class.php';
include_once 'fwlib/sql.class.php';
require_once 'fwlib/log.inc.php';
require_once 'inc/settings.php';
include_once 'fwlib/fpdf/fpdf.php';


/**
 * PDF
 * 
 * @package Bourse
 * @author François VAN DE WEERDT
 * @copyright 2009
 * @version $Revision: 469 $
 * @access public
 */
class PDF extends FPDF
{
	private $nom_deposant;
	private $tel_deposant;
	private $no_depot;
	/**
	 * PDF::Header()
	 * En-tête
	 * 
	 * @return void
	 */
	function Header()
	{
	    //Police Arial gras 15
	    $this->SetFont('Arial','',12);
	    //Titre
	    $this->Cell(100,15,$_SESSION['bourse']['nom_assoc'].' - '.$_SESSION['bourse']['nom_bourse'].' - '.date('d/m/Y') ,0,0,'L');
		$this->setXY(80,30);
	    $this->SetFont('Arial','B',16);
	    $this->Cell(0,10,"Dépôt N° {$this->no_depot}",1,2,'C');
	    $this->SetFont('Arial','',15);
		$this->MultiCell(0,8,"Déposant : {$this->nom_deposant}\nTél. {$this->tel_deposant}");
		$this->setXY(0,50);
		$this->SetFont('Arial','',10);
		$this->Ln(20);
	}
	
	
	function NouveauDepot($no_depot, $nom, $prenom, $tel){
		$this->no_depot = $no_depot;
		$this->nom_deposant = $nom.(!empty($prenom)? ' '.$prenom:'');
		$this->tel_deposant = preg_replace("/(\d{2})/","\$1 ", $tel);
		$this->AddPage();
	}
	
	//Tableau amélioré
	/**
	 * PDF::Table()
	 * 
	 * @param array $headers par Ex array( array('text'=> 'w'=> , 'align'=> 'col_name'=> ), ... )
	 * @param array $data
	 * @return void
	 */
	function Table($headers,$data, $trail)
	{
		$hdText = array();
		$ttlW = 0;
	    //En-tête
		foreach($headers as $header) {
			//Largeurs des colonnes
			$ttlW += $header['w'];
			$this->Cell($header['w'],7,$header['text'],1,0,'C');
		}
	
	    $this->Ln();
	    $this->SetFont('','',10);
	    //Données
	    foreach($data as $row)
	    {
			foreach($headers as $header) {
				if(!empty($header['barre']) && !empty($row['barre'])) {
					$this->SetDrawColor(130,130,130);
					$y= $this->GetY();
					$x1= $this->GetX();
					$x2 = $x1 + $header['w'];
					$this->Line($x1,$y+1, $x2, $y+6-1);
					$this->Line($x1,$y+6-1, $x2, $y+1);
					$this->SetDrawColor(0,0,0);
				}
				$this->Cell($header['w'],6,$row[$header['col']],'LR',0,$header['align']);
			}	
	        $this->Ln();
	    }
	    if(is_array($trail)) {
			foreach($headers as $header) {
		    	$this->Cell($header['w'],6,$trail[$header['col']],1,0,$header['align']);
		    }	
	    } else {	
	    	//Trait de terminaison
	    	$this->Cell($ttlW,0,'','T');
	    }
	    $this->Ln();
	    $this->SetFont('','',12);	    
	}
}



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
$pdf=new PDF();

foreach($aDepot as $depot) {
	$pdf->NouveauDepot($depot['iddepot'], $depot['nom'], $depot['prenom'], $depot['tel']);  // Add pg
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