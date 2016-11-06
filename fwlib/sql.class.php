<?php
/**
 * Class MySQL wrapper
 * 
 * redesigned for PHP 5 (FVdW)  
 *
 * @package sql_class
 * @version $Id$
 */

/* - - - - - - - - - - - - - - - - - - - - - - -
 Fichier Include
 Créé le mercredi 30 mars 2005 12:09:45
 avec HAPedit 2.6
 par  Auteur inconnu + FVdW


// USAGE :
  $sql = "SELECT * FROM some_table WHERE $where ORDER BY id";
  $db = new Cdb();
  $db->open('localhost', 'db_name', 'root', 'root_pwd');
  $n = $db->query($sql, $result);
  if ($n > 0)
  {
  	// $db->data is a [] with data founds
  }
  $db->close();
  - - - - - - - - - - - - - - - - - - - - - - - */
/**
 * contient les params de connexion à la DB
 *
 * @author GPL Inet - FVdW
 *  
 */
if (defined('SQL_CLASS_PHP')) return;
define('SQL_CLASS_PHP',1);

/**
 * Cdb Wrapper pour requetes MySQL
 *
 * @package sql_class
 * @author GPL Inet - François VAN DE WEERDT
 * @copyright 2008
 * @version $Id: sql.class.php 469 2010-11-11 21:22:36Z francois $
 * @access public
 */
class Cdb
{	
	const REQ_NONE   = 0;
	const REQ_SELECT = 1;
	const REQ_INSERT = 2;
	const REQ_DELETE = 3;
	const REQ_UPDATE = 4;
	
    private $link = false;
    public $data;
    public $col_info;
    private $fatal;
    private $sErr = '';
    public $typeOfRequest;

	private $host = false;
	private $user;
    private $pass;
	private $bdd;
	private $rec_per_page = 10;
	private $last_errno = 0;
	private $transaction = false;
	private $debug=false;      
    
    
    function __get($name) {
    	if($name=='last_errno' || $name == 'last_error') return $this->last_errno;
    	elseif($name=='err') return $this->sErr;
    	else return null;
    }
  /**
   * Cdb::__construct()
   *
   * @param string $host : host name for mysql DB 
   * @param string $db : database name
   * @param string $user dbabase user name
   * @param string $pass 
   * @return void
   */
    function __construct($host, $db, $user, $pass) 
    {
        $this->host = $host;
	    $this->user = $user;
    	$this->pass = $pass;
    	$this->bdd  = $db;
    	$this->last_errno = 0;
	}
  /**
   * Cdb::error()
   *
   * @param mixed $msg
   * @param integer $line
   * @return void
   */
	private function error($msg, $line=0) 
	{
		$this->last_errno = mysqli_errno($this->link);
		$this->sErr = $msg;
		$fn = "error.log";
		if($this->link){
			$sInfoMySQL = "MySQL errno: ".mysqli_errno($this->link).' -> '.mysqli_error($this->link)."\nlink=".print_r($this->link,1);
		} else {
			$sInfoMySQL = 'MySQL error: '.@mysqli_error();
		} 	
		$msg = rtrim($msg, "\n");
		$s = "\n--- ".date("d/m/Y H:i:s ").($line? __FILE__.':'.$line:'')."\n{$msg}\n{$sInfoMySQL}\n";
		$handle = fopen($fn, 'a') or die("<html><body>Cannot open file ($fn)<br><pre>$s</pre></body></pre>");
		fwrite($handle, $s) or die("<html><body>Cannot write to file ($fn)<br><pre>$s</pre></body></pre>");
		fclose($handle);
		if($this->debug) {
			echo "<span style='color:red'><b>Error !</b></span><tt >$s</tt>";
			
		}
		if($this->fatal) {
			$this->close();
			exit("<html><body><pre> {$this->sErr}</pre>... voir error.log</body></html>");
		}
	}
    
   /**
    * Cdb::turn_debug() active/desactive le mode debug
    * 
    * @param bool $on
    * @return void
    */
   public function turn_debug($on=true)
   {
       $this->debug = true;	
   } 
  /**
   * Cdb::set_page()
   *
   * @param integer $rec_per_page
   * @return void
   */
    function set_page($rec_per_page=10) {$this->rec_per_page = $rec_per_page;}
  /**
   * Cdb::open() Etablissement de la connexion avec la DB
   *
   * @param bool $fatal : false does'nt die if any SQL error, true die if any error 
   * @return bool success
   */
	function open($fatal=false) { 
		if(!$this->host) {
			$this->fatal = true;
			$this->error(__METHOD__."() host name not defined");
			return false;
        }
        $this->typeOfRequest = self::REQ_NONE;
    	
    	$this->fatal = $fatal;
    	$this->typeOfRequest = self::REQ_NONE;
    	$this->data = array();		

    	if($this->link) return true; // deja connecte
		if(!$this->link = mysqli_connect($this->host,$this->user,$this->pass,$this->bdd)) {
			$this->fatal = true;
			$this->error(__METHOD__."Erreur de connexion mysqli à {$this->host} DB {$this->bdd} ".mysqli_error($this->link), __LINE__);
			return false;	
		}		
		/*        
    	if(!$this->link = mysql_connect($this->host,$this->user,$this->pass,true)) {
			$this->fatal = true;
			$this->error(__METHOD__."Error connecting to {$this->host}", __LINE__);
			return false;	
		}
      	if (!mysql_select_db($this->bdd,$this->link)) {
			$this->fatal = true;
			$this->error(__METHOD__."() Error selecting ".$this->bdd, __LINE__);          
			return false;	
    	}   
        */    
        return true; // OK
    }
    
    
  /**
   * Cdb::do_mysql_query()
   *
   * @param string $query
   * @return Resource id $res
   */
    private function do_mysql_query($query)
    {
		// ctrl opened data base
		if (!$this->link) {
			$this->error(__METHOD__."() Error DB not opened", __LINE__);
			return false;	// not used (always 'fatal')			
		}
		//execute the query
		if($this->debug) echo '<hr>'.__METHOD__."() SQL = <br />[<tt style='background-color:#FFFF60'>$query</tt>]<br />";
		if (!$res = mysqli_query($this->link, $query)) {
			// case of error
			$this->error(__METHOD__."() Erreur mysqli à l'execution'\n\$query=[$query]\n", __LINE__);
			return false;
		}
		$this->last_errno = 0; 
		return $res;		
	}

    /**
     * Execute une requete sur la DB ouverte
     * Si select, stoke les resultats dans this->data
     *
     * @param $string sql requete à executer
     * @return mixed : int Si $sql = select : nbr enreg trouves
     *                     Si $sql = insert : last inserted id
     *                     Si $sql = delete,update : nbr rec affectes
     *         false en cas d'erreur (depend de $this->fatal)
     */
    public function query($sql)
	{
		$this->data=array();
		$this->col_info=array();
		$res = $this->do_mysql_query($sql);
		if($res === false) {
			if($this->transaction) {
				// si transaction en cours ... RollBack
				mysqli_rollback($this->link);                
				$this->transaction = false;
			}
			return false;	
		}
		
		// analyse de la requete pour retour du resultat de l'operation
		list($kw, $void) = preg_split('/[ \r\n\t]+/',ltrim(strtolower($sql)," \r\n\t"),2);
		switch($kw) {
			case 'show':
			case 'select': 
				$nb = mysqli_num_rows($res);                
				if($nb) {
				    // recuperation info colonnes  
					$nb_fields = mysqli_field_count($this->link);                    
  					for($i = 0; $i < $nb_fields; $i++) { 
                    	$o = mysqli_fetch_field_direct ($res , $i);   
  					    $this->col_info[] = array('name'=>$o->name, 'len'=>$o->length, 'type'=>$o->type);
					}
				    // copie des données
					while ($row = mysqli_fetch_assoc($res)) {
						$this->data[] = $row;
					}
					mysqlI_free_result($res);

				} 
				
				$this->typeOfRequest = self::REQ_SELECT;
				// retourne nbr rows
				return $nb;
			case 'insert':
				$this->typeOfRequest = self::REQ_INSERT;
				// retourne last ID
				return mysqli_insert_id($this->link);
			case 'update':
				$this->typeOfRequest = self::REQ_UPDATE;
				// retourne nbr rec MaJ
				return mysqli_affected_rows($this->link);
			case 'delete':
				$this->typeOfRequest = self::REQ_DELETE;
				// retourne nbr rec effacés
				return mysqli_affected_rows($this->link);
			default:
				$this->typeOfRequest = self::REQ_NONE;
				return true;
		}
	}
	
  /**
   * Cdb::select_one() execute un SELECT et retourne le 1er enregistrement
   * NB :Cdb::data[] ne contient aussi QUE le 1er enregistrement ! 
   *
   * @param string $sql
   * @return array : Si trouve assoc [] avec donnée enreg; si non trouvé [] vide
   */
	public function select_one($sql)
	{
		$this->data=array();
		// ctrl $sql == 'SELECT ...'
		if (!preg_match('/\s*select\s+/msi',$sql)){
			$this->fatal=true;
			$this->error(__METHOD__."Cette requete n'est pas un 'SELECT' [$sql]", __LINE__);
		} 
		// ajoute limite (si pas deja présente)
		if(!preg_match('/\slimit\s[0-9]+/msi', $sql)) {
			$sql .= ' LIMIT 1';
		}
				
		$res = $this->do_mysql_query($sql);
		if($res === false) return false;
		$nb=@mysqli_num_rows($res);
		$this->typeOfRequest = self::REQ_SELECT;
		if(!$nb) return array();
		// Attention, si +sieurs résultats, ne retourne QUE le 1er
		$this->data[0] = mysqli_fetch_assoc($res);        
		return $this->data[0];
	}

    /**
     * Liste les enum possible pour une table.champ donné sur la DB ouverte
     *
     * @param $string $table nom de la table
     * @param $string $field nom du champ 
     * @return array liste des enum possibles 
     */
    public function enum_list($table, $field)
	{
		$res = $this->do_mysql_query("SHOW COLUMNS FROM `$table` LIKE '$field'");
		if($res === false) return false;
		if(mysqli_num_rows($res) > 0) {
        	list(,$fields) = mysqli_fetch_row($res);
        	if (preg_match('/[\(]([^\)]+)[\)]/', $fields, $regs)) {
          		return explode(",",str_replace("'","",$regs[1]));
        	} 
      	}
      	return array();
    } 

    /**
     * Ferme la connexion
     */
    public function close() {
    	if($this->transaction) $this->commit_transaction();
      	if($this->link) {
        	@mysqli_close($this->link);
        	$this->link=0;
      	}
    }

   /**
    * Quote the given string so it can be safely used within string delimiters
    * in a query.
    *
    * @param $string mixed Data to be quoted
    * @return mixed "NULL" string, quoted string or original data
    */
    static public function quote($str = null)
    {
    	switch (strtolower(gettype($str))) {
	        case 'null': return 'NULL';
	        case 'integer': return $str;
	        case 'string':
	        default:
				$str = preg_replace('/(SELECT\s.*\sFROM\s|RENAME\s|INSERT\sINTO\s|UPDATE\s.*\sSET\s|DELETE\s+FROM\s|DROP\s+TABLE\s|\/\*)/msi','',$str);
				// 2nd protection contre injection
				$injections = array('>','<','=','?','\\','&','|','-','+','$','#','*');
				$str = str_replace($injections, '', $str);
				return "'".addslashes($str)."'";
		}
    }
   /**
    * Conversion date my_sql vers date "française"
    *
    * @param string $iso_date : format d'entree accepté : date ou datetime
    * @return $string  "jj/mm/aaaa" si OK, "" si $date format incorrect
    */
     public function date2str($iso_date = null, $affich_hrs=false)
     {
		if($iso_date=='0000-00-00' || $iso_date=='0000-00-00 00:00:00') return '';
		$s = preg_replace('/([0-9]{4})\-([0-9]{2})\-([0-9]{2})/','$3/$2/$1',$iso_date);
		if (!$affich_hrs) return substr($s,0,10);
		if(strlen($s) > 10)
			list($d, $h) = explode(' ', $s);
			if(!empty($h))
				$s = $d . ' <span style="font-size:0.85em">'.$h.'</span>';
		return $s;
     }

   /**
    * Conversion date "française" vers date my_sql
    *
    * @param $string str format d'entree accepté : jj/mm/aaaa 
    * @param $int upper_limit (default=false) si true, ajoute "23:59:59" à la date 
    *     (utile pour limite suppérieures)
    * @return $string  " 'aaaa-mm-dd " si OK, -1 si $date format incorrect
    */
     public function date2mysql($str, $upper_limit=false)
     {
    	if (preg_match('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})/', $str, $regs))
			return "'".$regs[3]."-".$regs[2]."-".$regs[1].($upper_limit? " 23:59:59":"")."'";
		else return -1;
    }
    
  /**
   * Cdb::select_page() execute un select sur la db avec une limite Cdb::rec_per_page et un offset 
   *  
   * Les données retournées sont dans Cdb::data comme pour Cdb::query
   * retourne un array avec les éléments nécessaires pour la navigation entre les pages  
   *
   * @param string $query obligatoirement de la forme SELECT ... FROM ... sans LIMIT ! 
   * @param integer $page : offset exprimé en pages (de Cdb::rec_per_page))
   * @return object Cnav
   */
    public function select_page($query, $page=0)
	{		
		// ctrl $sql == 'SELECT ...'
		if (!preg_match('/\s*select\s+/msi',$query)){
			$this->fatal=true;
			$this->error(__METHOD__."() Cette requete n'est pas un 'SELECT' [$query]", __LINE__);
		}
		if(!is_numeric($page) || $page < 0) {
			$this->fatal=true;
			$this->error(__METHOD__." Arg \$page invalide [$page]", __LINE__);			
		} 
/** @todo : utiliser  SQL_CALC_FOUND_ROWS
 * $query = preg_replace('/\s*select\s+/msi', 'SELECT SQL_CALC_FOUND_ROWS ', $query);
 * $query .=  LIMIT $offset,$rec_per_page";
 * 
 * Nbr max =
 * mysql_query( "SELECT FOUND_ROWS() nbr_max" );
 * Etc... 
 */ 
		// 1St count nbr of retreived record
		$query_cnt = preg_replace("/^\s*select\s.*\sfrom\s/msi", "SELECT count(*) AS cnt FROM ",$query);
		$res = $this->do_mysql_query($query_cnt);
		if($res === false) return false;

		if(mysqli_num_rows($res) != 1) {
			$this->error(__METHOD__." La requete [$query_cnt] a retourne ".mysqli_num_rows($res)." rec.",__LINE__);
		}
		$row = mysqli_fetch_assoc($res);
		$ttl_rec = $row['cnt'];

		if($ttl_rec <= $this->rec_per_page) {
		    $first_page = $last_page = $page = 0;
		} else {
			$last_page = ceil($ttl_rec / $this->rec_per_page) -1;
			$first_page = 0;
			if($page > $last_page) $page = $last_page;
			elseif ($page < 0) $page = 0;
		}
		
		$offset = $page * $this->rec_per_page;
		
		$res = $this->do_mysql_query("$query LIMIT $offset,{$this->rec_per_page}");
		if($res === false) return false;
		$this->data = array();
		$nb=@mysqli_num_rows($res);
		if($nb) {
			while ($row = mysqli_fetch_assoc($res)) {
				$this->data[] = $row;				
			}
			mysqli_free_result($res);					
		} 
		$this->typeOfRequest = self::REQ_SELECT;
		return new Cnav($query, $ttl_rec, $page, $last_page, $this->rec_per_page, $nb); 
	}
	
  /**
   * Cdb::start_transaction()
   *
   * @return void
   */
	public function start_transaction()
	{
		if(!$this->transaction) {      
/*        
			$this->do_mysql_query('START transaction ');
*/
			mysqli_begin_transaction($this->link);            
			$this->transaction = true;
		}	
	}

  /**
   * Cdb::commit_transaction()
   *
   * @return void
   */
	public function commit_transaction()
	{
		if($this->transaction) {
/*        
			$this->do_mysql_query('COMMIT');
*/
			mysqli_commit($this->link);            
			$this->transaction = false;
		}
	}

  /**
   * Cdb::rollback_transaction()
   *
   * @return void
   */
	public function rollback_transaction()
	{
		if($this->transaction) {
/*
			$this->do_mysql_query('ROLLBACK');
*/
			mysqli_rollback($this->link);                        
			$this->transaction = false;
		}
	}

  /**
   * Cnav::get_link()
   *
   * @return
   */
	public function get_link() {return $this->link;}

	public function is_select($sql) {
		// analyse de la requete pour retour du resultat de l'operation
		return preg_match('/^\s*(select|show)\s/i',$sql)? true:false;
	}
	
}


/**
 * Cnav : classe pour pagination des resultats et navigation entre les pages
 * 
 * Exemple 
 *    	$nav = $db->select_page($qry,$page); // Cdb::page() retourne un objet Cnav (si OK)		
 *		if($nav) {
 *			$sDataTable = '';
 *			foreach($db->data as $row) {
 *				$sDataTable .= "<tr><td>{$row['c1']}</td><td>{$row['c2'])</td><td>{$row['cN'])</td></tr>",
 *			}
 *			$remain = $nav->get_empty_tr();
 * 			// fill the table in order to keep the nbr_rec_per_page height (even for last page) 
 *			for($i=0; $i < $remain;$i++) {
 *				$sDataTable .= "<tr class='no_data'><td colspan='3'>&nbsp;</td></tr>";
 *			}
 *			$btnNavigation = $nav->nav_bar("");  // string 4 btn navigation "<<  < nbr_records  >  >>"
 *	} else die ('No data found !');
 *	
 *
 * @package sql_class
 * @author François VAN DE WEERDT
 * @copyright 2008
 * @version $Id: sql.class.php 469 2010-11-11 21:22:36Z francois $
 * @access public
 */
class Cnav 
{
	private $qry, $first, $prev, $next, $last, $page, $current, $ttl, $page_sz, $count;
  /**
   * Cnav::__construct()
   *
   * @param string $qry
   * @param integer $cur
   * @param integer $lst
   * @param integer $page_sz
   * @param integer $count  
   * @return void
   */
	function __construct($qry, $ttl, $cur, $lst=0, $page_sz=0, $count=0)
	{
		$this->qry = $qry;
		$this->current = $cur;
		$this->ttl = $ttl;
		$this->first = 0;
		$this->next = $cur+1 < $lst? $cur+1: $lst;
		$this->prev = $cur-1 > 0? $cur-1: 0;
		$this->last = $lst;
		$this->page_sz = $page_sz;
		$this->count = $count;
	}
	
  /**
   * Cnav::encode()
   *
   * @param mixed $qry
   * @param mixed $page
   * @return
   */
	private function encode($qry) 
	{
		if(!$this->qry) return false;
		return base64_encode(gzcompress("$qry",9));
	}
	
  /**
   * Cnav::get_empty_tr()
   *
   * @return integer
   */
	function get_empty_tr() {
		return $this->page_sz - $this->count;
	}		
  /**
   * Cnav::error()
   *
   * @param string $msg
   * @param bool $fatal
   * @return void
   */
	private function error($msg='', $fatal=false) {
		$fn = "error.log";
		$handle = fopen($fn, 'a') or die("Cannot open file ($fn)");
		fwrite($handle, "\n--- ".date("d/m/Y H:i:s")."\n".$msg."\n") or die("Cannot write to file ($fn)");
		fclose($handle);
		if($fatal) exit("<html><body><pre>$msg</pre>... voir log</body></html>");		
	}
	


  /**
   * Cnav::get_first_pg()
   *  Si 1ere page accessible retourne son n° 
   *  false si pas accessible   
   *
   * @return mixed numero de la 1ere page / fase 
   */
	private function get_first_pg() {
		if($this->current == $this->first) return false;
		return 0;
	}
  /**
   * Cnav::get_prev_pg()
   *  Si page précédente accessible retourne son n° 
   *  false si pas accessible   
   *
   * @return string encoded nav info (query_string)
   */
	private function get_prev_pg() {
		if($this->current == $this->first) return false;
		return $this->prev;
	}
  /**
   * Cnav::get_next_pg()
   *  Si page suivante accessible retourne son n° 
   *  false si pas accessible   
   *
   * @return string encoded nav info (query_string)
   */
	private function get_next_pg() {
		if($this->current == $this->last) return false;
		return $this->next;
	}
  /**
   * Cnav::get_last_pg()
   *  Si Dernière page accessible retourne son n° 
   *  false si pas accessible   
   *
   * @return string encoded nav info (query_string)
   */
	private function get_last_pg() {
		if($this->current == $this->last) return false;
		return $this->last;
	}

	
  /**
   * Cnav::get_nav_bar()
   *
   * @param string $url_param
   * @return string : html <form method='post'> avec 4 boutons pour navigation par page dans les résultats 
   */
	public function get_nav_bar($url_param='') {
		if($url_param) {
			$url_param = ltrim($url_param, '?');
			$url_param = rtrim($url_param, '&');
		} 
			
		$offset = ($this->current * $this->page_sz)+1;
		$a = min($offset + $this->page_sz -1, $this->ttl);
		// boutons premiere page et page précédente
		$first = $this->get_first_pg();
		if($first !== false) {
			$btn_first = "<button onclick=\"$('ipg_nav').value=$first;$('if_nav').submit();\"> &lt;&lt; </button>";
			$prev = $this->get_prev_pg();
			$btn_prev = "<button onclick=\"$('ipg_nav').value=$prev;$('if_nav').submit();\"> &lt; </button>";
		} else {
			$btn_first ="<button disabled='true' onclick='void(0)'> &lt;&lt; </button>";
			$btn_prev ="<button disabled='true' onclick='void(0)'> &lt; </button>";
		}
		
		// boutons page suivante & derniere page 
		$last = $this->get_last_pg();
		if ($last !== false) {
			$btn_last = "<button onclick=\"$('ipg_nav').value=$last;$('if_nav').submit();\"> &gt;&gt; </button>";
			$next = $this->get_next_pg();	
			$btn_next = "<button onclick=\"$('ipg_nav').value=$next;$('if_nav').submit();\"> &gt; </button>";
		} else {
			$btn_last ="<button disabled='true' onclick='void(0)'> &gt;&gt; </button>";
			$btn_next ="<button disabled='true' onclick='void(0)'> &gt; </button>";
		}
		
		// compteur depages
		if($this->ttl <= $this->page_sz) {
		    $sCompteur = $this->ttl.' Enregistrement'.($this->ttl > 1? 's':'');
		} else {
		    $sCompteur = "Enregistrements $offset &agrave; $a (sur {$this->ttl})";
		}
		
		return "\n<!-- Navigation --><form name='fnav' id='if_nav' method='post' action='?$url_param'>
  $btn_first&nbsp;
  $btn_prev&nbsp;
  <i>$sCompteur</i>&nbsp;
  $btn_next&nbsp;
  $btn_last
  <input type='hidden' name='pg' value='' id='ipg_nav' />
  <input type='hidden' name='qry' value='". ($this->ttl <= $this->page_sz? '':chunk_split($this->encode($this->qry)))."' />
  <input type='hidden' name='nav_bar' value='NAV_BAR' />
</form><!-- fin navigation -->\n";
	}

  /**
   * Cnav::get_post_data()
   *  Lecture des var Post de la nav bar
   *  Retourne 2 valeur : la query et le n° de page demande  
   *
   * @return mixed array(qry, page) ou false si nav bar pas utilisée
   */
  	public function get_post_data() 
	  {
	  	// Est-ce que la nav bar a été utilisée (click sur un des 4 boutons) ?
  		if(!isset($_POST['nav_bar'])) return false;
  		
		if(!isset($_POST['qry'])) {
			// Erreur !
			self::error(__METHOD__."() _POST[qry] non defini\n".__FILE__.':'.__LINE__);
			return false;	
		}
		if(!isset($_REQUEST['pg'])) {
			// Erreur !
			self::error(__METHOD__."() _POST[pg] non defini\n".__FILE__.':'.__LINE__);
			return false;	
		}
		$qry = @gzuncompress(base64_decode($_POST['qry']));
		if(!$qry) {
			self::error(__METHOD__."() _POST[qry] invalide\nqry=[{$_POST['qry']}]\n".__FILE__.':'.__LINE__);
			return false;	
		}
		$page = $_POST['pg'];
		if(!is_numeric($page)) {
			self::error(__METHOD__."() _POST[pg] invalide\npg=[$page]\n".__FILE__.':'.__LINE__);		
			return false;	
		}
		return array($qry,$page);
  	}	

}

// EoF
?>
