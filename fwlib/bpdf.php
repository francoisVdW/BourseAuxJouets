<?php
include('fwlib/fpdf/fpdf.php');

/**
 * PDF
 * 
 * @package Bourse
 * @author François VAN DE WEERDT
 * @copyright 2009
 * @version $Id$
 * @access public
 */
class bPDF extends FPDF
{
  private $is_depot = True;
	private $nom_deposant='';
	private $tel_deposant='';     
  private $adr_deposant='';
	private $no_depot='?';     
  private $date_depot='';
  private $participant='';   
  private $destinataire = false;
  
	         
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
 	// Written by Larry Stanbery - 20 May 2004
	// Same license as FPDF
	// creates "page groups" -- groups of pages with page numbering
	// total page numbers are represented by aliases of the form {nbX}  
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
  
		var $NewPageGroup;   // variable indicating whether a new group was requested
    var $PageGroups;     // variable containing the number of pages of the groups
    var $CurrPageGroup;  // variable containing the alias of the current page group

    // create a new page group; call this before calling AddPage()
    function StartPageGroup()
    {
        $this->NewPageGroup = true;
    }

    // current page in the group
    function GroupPageNo()
    {
        return $this->PageGroups[$this->CurrPageGroup];
    }

    // alias of the current page group -- will be replaced by the total number of pages in this group
    function PageGroupAlias()
    {
        return $this->CurrPageGroup;
    }

    function _beginpage($orientation, $format)
    {
        parent::_beginpage($orientation, $format);
        if($this->NewPageGroup)
        {
            // start a new group
            $n = sizeof($this->PageGroups)+1;
            $alias = "{nb$n}";
            $this->PageGroups[$alias] = 1;
            $this->CurrPageGroup = $alias;
            $this->NewPageGroup = false;
        }
        elseif($this->CurrPageGroup)
            $this->PageGroups[$this->CurrPageGroup]++;
    }

    function _putpages()
    {
        $nb = $this->page;
        if (!empty($this->PageGroups))
        {
            // do page number replacement
            foreach ($this->PageGroups as $k => $v)
            {
                for ($n = 1; $n <= $nb; $n++)
                {
                    $this->pages[$n] = str_replace($k, $v, $this->pages[$n]);
                }
            }
        }
        parent::_putpages();
    }  
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
  
  
	/**
	 * PDF::Header()
	 * En-tête
	 * 
	 * @return void
	 */
	function Header()
	{                         
		// entete 
		$this->SetFont('Arial','',9);
		$this->SetTextColor(80);
		$this->SetDrawColor(100);
		//Titre
		$this->Cell(165,7,"{$_SESSION['bourse']['nom_assoc']} - {$_SESSION['bourse']['nom_bourse']}", 'B', 0, 'L');
		// N° Page
		$this->Cell(0,7, 'page '.$this->GroupPageNo().'/'.$this->PageGroupAlias(), 'B', 1, 'R');
		$this->SetTextColor(0);
		$this->SetDrawColor(0);          

		if($this->is_depot) {
			// desitinataire
			if($this->destinataire) {
				$this->setXY(10,25-3);
				$this->SetFont('','I',14);
				$this->SetTextColor(80);	// gris
				$this->Write(7,'Exempaire ');    
				$this->ln();
				$this->SetFont('','I',18);
				$this->Write(7,$this->destinataire);    
			}
			// cadre N° depot          
			$this->SetTextColor(0);
			$this->setXY(80,25);
			$this->SetFont('Arial','B',16);
			$this->Cell(0,10,"Dépôt N° {$this->no_depot}",1,2,'C'); 
			if($this->GroupPageNo() < 2) { 
				// Nom deposant, adresse & Tel              
				if (!empty($this->adr_deposant))
					$addr = $this->adr_deposant . "\n";
				else $addr = '';
				$addr .= "Tél. : {$this->tel_deposant}";
				$this->SetFont('','',14);
				$this->MultiCell(0,8,"Déposant : {$this->nom_deposant}");               
				$this->SetFont('','',12);                             
				$this->setX(80);
				$this->MultiCell(0, 7, $addr);
			}      		                		        
		} else {
			// entete Facture

		}
		$this->SetFont('','',10);
		$this->Ln(10);
	}
	   
  
	
	function SetDepot($no_depot, $nom='', $prenom='', $tel='', $adr='', $date_depot='', $participant='') 
	{
		if(empty($no_depot)) {
			$this->is_depot = False;
			return;
		}
		$this->no_depot = $no_depot;
		$this->nom_deposant = $nom.(!empty($prenom)? ' '.$prenom:'');
		$this->tel_deposant = preg_replace("/(\d{2})/","\$1 ", $tel);
		$this->adr_deposant = empty($adr)? false:$adr;   
		$this->date_depot = $date_depot;
		$this->participant = $participant;		
	}
	
	
	/**
	 * PDF::Table()
	 * Tableau amélioré
	 * 
	 * @param array $headers par Ex array( array('text'=> 'w'=> , 'align'=> 'col_name'=> ), ... )
	 * @param array $data
	 * @return void
	 */
	function Table($headers,$data, $trail=false)
	{
		$hdText = array();
		$ttlW = 0;
    	$this->SetDrawColor(100);
	  	//En-tête
		foreach($headers as $header) {
			//Largeurs des colonnes
			$ttlW += $header['w'];
			$this->Cell($header['w'],7,$header['text'],1,0,'C');
		}
	
	  $this->Ln();
	  $this->SetFont('','',10);	   // defaut
      //Données
		if(!empty($data)) foreach($data as $row) {
			foreach($headers as $header) {
				$x1= $this->GetX();
				if (!empty($header['font-size'])) {
					$this->SetFontSize($header['font-size']);
				}
				$this->Cell($header['w'],6, $row[$header['col']],'LRB',0,$header['align']);
				if(!empty($header['barre']) && !empty($row['barre'])) {
					$y= $this->GetY();              
					$x2 = $x1 + $header['w'];
					$this->Line($x1,$y+1, $x2, $y+6-1);
					$this->Line($x1,$y+6-1, $x2, $y+1);
				}
				$this->SetFontSize(10); // defaut
			}
			$this->Ln();
		}
		// pied du tableau      
      
		if(is_array($trail) && !empty($trail)) {
			foreach($headers as $header) {
				$this->Cell($header['w'],6,$trail[$header['col']],1,0,$header['align']);
			}
		} else {
			//Trait de terminaison
			$this->Cell($ttlW,0,'','T');
		}
		$this->Ln();
		$this->SetFont('','',12);
		$this->SetDrawColor(0);	    
	}  

	/**
  *   PDF::setDestinataire()
  *   prepare var this->destinatire 
	*/  
  public function setDestinataire($txt='')
  {                  
  	$this->destinataire = empty($txt)? false:$txt;                      
  }
}
?>
