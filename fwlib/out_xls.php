<?php
/**
 * CXLS
 *
 * @package CXLS
 * @author François VAN DE WEERDT
 * @copyright 2008
 * @version $Id$
 * @access public
 */
class CXLS {

	private $s='';
	private $row=0;
	private $col=0;
	private $nbCol = false;


  /**
   * CXLS::__construct()
   *
   * @param bool $nb_cols
   * @return void
   */
	public function __construct($nb_cols=false)
	{
	    $this->nbCol = ($nb_cols > 0)? $nb_cols : false;
	}

  /**
   * CXLS::xlsBOF() ;  begin of file header
   *
   * @return
   */
	private function xlsBOF() {
	    return pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
	}

  /**
   * CXLS::xlsEOF() : end of file footer
   *
   * @return
   */
	private function xlsEOF() {
	    return  pack("ss", 0x0A, 0x00);
	}

  /**
   * CXLS::writeNumber() : Function to write a Number (double)
   *
   * @param mixed $Value
   * @return void
   */
	public function writeNumber($Value) {
	    $this->s .= pack("sssss", 0x203, 14, $this->row, $this->col, 0x0);
	    $this->s .=pack("d", $Value);
	    $this->next();
	}

  /**
   * CXLS::writeText() ; Function to write a text (xls label)
   *
   * @param mixed $Value
   * @return void
   */
	public function writeText($Value) {
	    $L = strlen($Value);
	    $this->s .=pack("ssssss", 0x204, 8 + $L, $this->row, $this->col, 0x0, $L);
	    $this->s .=$Value;
	    $this->next();
	}


  /**
   * CXLS::next() : passe à la case suivante ... si nb_col defini
   *
   * @return void
   */
	private function next() {
	    if($this->nbCol) {
	        $this->col++;
	        if($this->col >= $this->nbCol) {
	            $this->col=0;
	            $this->row++;
			}
		}
	}
	public function gotoRowCol($r,$c) {$this->row=$r;$this->col=$c;}
	public function newLine() {$this->row++;$this->col=0;}
	public function getRow() {return $this->r;}
	public function getCol() {return $this->c;}
	// ----- end of function library -----

  /**
   * CXLS::output()
   *
   * @return void
   */
	public function output($fileName='data') {
	    $sO = $this->xlsBOF();
	    $sO.= $this->s;
	    $sO.= $this->xlsEOF();

	    $fileName = preg_replace('/\.xls$/i', '', basename($fileName)); // pas de path !

        header("Expires: Mon, 01 Jan 2001 01:00:00 GMT");
        header("Cache-Control: no-cache");
        header("Cache-Control: post-check=0,pre-check=0");
        header("Cache-Control: max-age=0");
        header("Pragma: no-cache");
		header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
		header('Content-type: application/x-msexcel');
		header("Content-Disposition: attachment; filename=$fileName.xls");
		header("Content-Description: PHP/mySQL Generated Data" );
	    exit($sO);
	}
}


/*
 * @param Cdb object reference sur $db
 * colInfo['type'] = 1 à 5 :numeric
 *                 = 12 : datetime
 */
function do_xls(&$db)
{
	$nb_cols = count($db->col_info);
	$xls  = new CXLS($nb_cols);

	// entetes : nom des colonnes
	foreach($db->col_info as $col) {
		$xls->writeText($col['name']);
	}
	// données
	foreach($db->data as $r) {
	    for($i=0; $i<$nb_cols; $i++){
	        $colInfo = $db->col_info[$i];
	        if($colInfo['type'] >= 1 && $colInfo['type'] <= 5) $xls->writeNumber( $r[$colInfo['name']] );
	        else $xls->writeText( $r[$colInfo['name']] );
		}
	}
	$xls->newLine();
	$xls->writeText('Edité le '.date('d/m/y à H:i:s'));
	// Fin
	$xls->output();
}
