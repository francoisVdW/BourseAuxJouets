<?php
/**
 * Include file: class User
 * 
 * @package User
 * @version $Id$
 * @author unknown
 */
if (defined("USER_CLASS_PHP")) return;
define ("USER_CLASS_PHP",1);


/** DEFINITIONS constantes : nom table, nom champ UID
 */
define('USER_TBL', 'participant');
define('UID_FLD', 'idparticipant');


/**
 * Classe utilisée pour maintenir les info de session et ctrl les droits
 *
 * Structure de la table 'user'
 * CREATE TABLE user (
 *   'id' int NOT NULL auto_increment,  <-- le nom de ce champ peut être redéfini
 *   login varchar(20) NOT NULL default '',
 *   pwd char(32) binary NOT NULL default '',
 *   cookie char(32) binary NOT NULL default '',
 *   session char(32) binary NOT NULL default '',
 *   ip varchar(15) binary NOT NULL default '',
 *   ... Autres champs propres à l'application ....
 *   PRIMARY KEY (id),
 *   UNIQUE KEY username (username)
 *   );
 *
 * rights & prefs : données sérialisee type json key:value; key:values ... sans imbrications
 *
 * @package bourse
 */
class User {
	var $failed = false; // failed login attempt
	var $date; 		// current date GMT
	var $login = '';
	var $uid = 0; 	// the current user's id
	var $cookie=0;
	var $fields=array();
	// private $dbHost, $dbName, $dbUser, $dbPwd;
    private $cdb=false;
	
	/**
    *  Constructeur de classe :
	 * Verifie la connexion de l'utilisateur courant
	 * Positionne les var de _SESSION
	 *
	 * A controler : _SESSION[logged]
   	*/ 
    /*
	function User($dbHost, $dbName, $dbUser, $dbPwd)
	{
		$this->dbHost=$dbHost;
		$this->dbName =$dbName;
		$this->dbUser = $dbUser;
		$this->dbPwd = $dbPwd;
		$this->date = gmdate("'Y-m-d'");
			if (isset($_SESSION['logged']) && $_SESSION['logged']) {
			$this->_checkSession();
		} elseif (isset($_COOKIE['mtwebLogin']) ) {
			$this->_checkRemembered($_COOKIE['mtwebLogin']);
		}
	}    
*/    
    function User(&$cdb) 
    {
    	$this->cdb = $cdb;
		$this->date = gmdate("'Y-m-d'");
			if (isset($_SESSION['logged']) && $_SESSION['logged']) {
			$this->_checkSession();
		} elseif (isset($_COOKIE['mtwebLogin']) ) {
			$this->_checkRemembered($_COOKIE['mtwebLogin']);
		}
    }

	/**
   	* Wrapper pour requete SQL
	*/
	function _query($query)
	{          
    	$n = $this->cdb->query($query);
        if ($n) return $this->cdb->data;
    	else return array();                                          
    /*
    	if(!$link = @mysql_connect($this->dbHost,$this->dbUser,$this->dbPwd, true)) {
			$this->error(__METHOD__."() Error connecting to {$this->dbHost}", __LINE__);
		}
      	if (!mysql_select_db($this->dbName,$link)) {
			$this->error(__METHOD__."() Error selecting {$this->dbName}", __LINE__);
    	}

		if (!$res= mysql_query($query,$link)) {
			// case of error
			$this->error(__METHOD__."() Error executing query\n\$query=[$query]\nmysql Error ".mysql_errno().' :'.mysql_error() , __LINE__);
			@mysql_close($link);
			return false;
		} 
		if(preg_match('/^\s*(SELECT|UPDATE)\s+/msi',$query,$a)) {
			switch( strtolower($a[1])) {
				case 'select':
					$nb=mysql_num_rows($res);
					$ret = array();
					if($nb) {
						while ($row = mysql_fetch_assoc($res)) {
							$ret[] = $row;
						}
						mysql_free_result($res);					
					}
					break;
				case 'update':
					$ret = mysql_affected_rows($link);
					break;
				default:
					// cas IMPOSSIBLE  cf preg_match()
					echo __METHOD__."() token de query non reconnu ! [$query]". __LINE__;
					@mysql_close($link);
					return false;			
			}
		} else {
			$this->error(__METHOD__."() type de query non reconnue ! [$query]", __LINE__);
			@mysql_close($link);
			return false;			
		}
		@mysql_close($link);
		return $ret;    
        */
	}
	private function error($s) {echo "<hr><pre>$s</pre><hr>";}
	/**  
 	* User::checkLogin()	Logging in Users
	*
	*   To allow users to login you should build a web form, after validation
	*   of the form you can check if the user credentials are right with
	*   $user->checkLogin('nom', 'password', remember).
	*   Nom and password should not be constants of course, remember is
	*   a boolean flag which if set will send a cookie to the visitor to
	*   allow later automatic logins.
	*   --
	*   The function uses PEAR::DB's
	*   quote method to ensure that data that will be passed to the database
	*   is safely escaped. I've used PHP's md5 function rather than MySQL's
	*   because other databases may not have that.
	*   --
	*   The WHERE statement is optimized (the order of checks) because nom
	*   is defined as UNIQUE.
	*   --
	*   No checks for a DB_Error object are needed because of the default
	*   error mode set above. If there is a match in the database $result will
	*   be an object, so set our session variables and return true (successful
	*   login). Otherwise set the failed property to true (checked to decide
	*   whether to display a login failed page or not) and do a logout of
	*   the visitor.
	*   --
	*   The logout method just executes session_defaults()
   	*
   	* @param string $login
   	* @param string $password
   	* @param bool $remember use cookies (or do not)
   	* @return
   	*/
	function checkLogin($login, $password, $remember=FALSE)
	{
		// $login = strtoupper($login);
		$sql = "SELECT * FROM ". USER_TBL ." WHERE login=".Cdb::quote($login)." AND pwd=".Cdb::quote(md5($password));
		$r = $this->_query($sql);
		if(!$r || count($r)!=1) {
		  	echo "\n<!-- ".count($r)." utilisateurs trouves \n sql=$sql-->\n";
			$this->failed = true;
			$this->_logout();
			return false;
		} else {
			$row = $r[0];
		    // sauve les info		    
		    $this->login = $login;
			$this->cookie = $row['cookie'];
			$this->uid = $row[UID_FLD];
			if(!$this->uid) {
				$this->failed = true;
				$this->_logout();
				return false;	
			}
			$this->_read_fields($row);   // lecture des autres champs de la table USER_TBL
		  	$this->_setSession($remember); 
		  	return true;
		}
	}
	/** Setting the Session
	*   This method sets the session variables and if requested sends the cookie
	*   for a persistent login, there is also a parameter which determines if this
	*   is an initial login (via the login form/via cookies) or a subsequent
	*   session check.
	* @param bool $remember
	* @param bool $init
	*/
	function _setSession($remember, $init=TRUE)
  	{
		$_SESSION['uid'] = $this->uid;
		$_SESSION['login'] = $this->login;
		$_SESSION['cookie'] = $this->cookie;
		$_SESSION['logged'] = TRUE;
		if ($remember) {
			$this->updateCookie($values['cookie'], TRUE);
		}
		if ($init) {
			$session =Cdb::quote(session_id());
			$ip = Cdb::quote($_SERVER['REMOTE_ADDR']);
			$sql = "UPDATE ".USER_TBL." SET session=$session, ip=$ip WHERE ".UID_FLD."=".$this->uid;
			$this->_query($sql);
		}
	}

	/**
   	* Persistent Logins
 	*   If the visitor requested a cookie will be send to allow skipping the
	*   login procedure on each visit to the site. The following two methods
	*    are used to handle this situation.
	*/
	function updateCookie($cookie, $save)
	{
	    $_SESSION['cookie'] = $cookie;
	    if ($save) {
	      	$cookie = serialize(array($_SESSION['login'], $cookie) );
	      	set_cookie('mtwebLogin', $cookie, time() + 31104000);
	    }
	}

	/** Checking Persistent Login Credentials
	*    If the user has chosen to let the script remember him/her then a cookie
	*     is saved, which is checked via the following method.
	*     --
	*     This function should not trigger any error messages at all.
	*     To make things more secure a cookie value is saved in the cookie not
	*     the user password. This way one can request a password for areas which
	*     require even higher security.
	*/
	function _checkRemembered($cookie)
	{
		list($login, $cookie) = @unserialize($cookie);
	    if (!$login or !$cookie) return;
	    $login = $this->db->quote($login);
	    $cookie = $this->db->quote($cookie);
	    $sql = "SELECT * FROM ".USER_TBL." WHERE " .
	      "(login=$login) AND (cookie=$cookie)";

	    $rows = $this->_query($sql);
		if($rows && count($rows)==1) {
			$r = $rows[0];
			$this->login = $r['login'];
			$this->cookie = $r['cookie'];
			$this->uid = $r[UID_FLD];
			$this->_read_fields($r);   // lecture des autres champs de la table USER_TBL
			$this->_setSession(TRUE);
		}
	}

	/** Ensuring Valid Session Data
	*    So this is the final part, we check if the cookie saved in the session
	*    is right, the session id and the IP address of the visitor.
	*    The call to setSession is with a parameter to let it know that this is
	*    not the first login to the system and thus not update the IP and session
	*    id which would be useless anyway
	*/
	function _checkSession()
	{
		$login = Cdb::quote($_SESSION['login']);
		$cookie = Cdb::quote($_SESSION['cookie']);
		if ($cookie=="NULL") 	$sCookie ="is NULL";
		else 					$sCookie = "=$cookie";
		$session = Cdb::quote(session_id());
		$ip = Cdb::quote($_SERVER['REMOTE_ADDR']);
		$sql = "SELECT * FROM ".USER_TBL." WHERE " .
		  "(login=$login) AND (cookie $sCookie) AND " .
		  "(session=$session) AND (ip=$ip)";
		$rows = $this->_query($sql);
		//DEBUG
		// logInfo("_checkSession() r=$r\nsql=$sql", __fILE__,__LINE__);
		//DEBUG
		if ( $rows && count($rows)==1) {
			$r = $rows[0];
			$this->login = $r['login'];
			$this->uid = $r[UID_FLD];
			$this->cookie = $r['cookie'];
			$this->_read_fields($r);   // lecture des autres champs de la table USER_TBL
			$this->_setSession(false);
		}
		else $this->_logout();
	}

	/**
	* _read_fields (private) : lecture des champs non defini dans spect minimale
	* et stocke les donnees dans this->fields
	*
	* @param array $row
	*/
	function _read_fields(&$row)
	{                              
	    $this->fields = array();
	    foreach($row as $k => $v) {
	        if(in_array($k, array(UID_FLD, 'login', 'pwd', 'session', 'cookie', 'ip'))) {
	            continue;
			}
			$this->fields[$k] = $v;			
		}
	}

	/**
	* Retourne la valeur d'un  champ (quelconque et non defini
	*  dans spec minimale) stockee dans la db et associee à $key
	*
	* @param string $key : nom du champ cherche
	* @return mixed : si touvé string (vide si pas de valeur), false si non definie
	*/
	function get_field($key)
	{
	    if(isset($this->fields[$key]) ) {
	        return $this->fields[$key];
		} else {
		    return FALSE;
		}
	}

	/**
	*  Fermeture de session
	*/
	function _logout()
	{
		//DEBUG
		//echo"_logout()<br>";
		//DEBUG
	    $this->prefs = array();
	    $this->rights = array();
	    $this->nom='';
	    $this->coockie=0;
		$_SESSION['uid'] = FALSE;
		$_SESSION['login'] = FALSE;
		$_SESSION['cookie'] = FALSE;
		$_SESSION['logged'] = FALSE;
	}
}