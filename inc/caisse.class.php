<?php
/**
 * File : caisse.class.php
 *
 * @package bourse
 * @author FVdW (vdw.francois@gmail.com)
 * @copyright 2009
 * @date 23/9/2009
 * @version $Id$
 */

class Caisse {
	private $etat;
	private $no_caisse;
	private $idparticipant;
	private $idvente;
	private $login;
	private $usr_id;
	private $db;
	/**
	 * Caisse::__construct()
	 * 
	 * @param mixed $db : reference sur gestionnaire de DB
	 * @param mixed $usr_id : utilisateur courant (cf login)
	 * @return void
	 */
	public function __construct(&$db, $usr_id) {
		$this->etat = '';
		$this->no_caisse = '';
		$this->idparticipant = '';
		$this->idvente = 0;
		$this->login = '';
		$this->usr_id = $usr_id;
		$this->db = $db;
	}
	
	/**
	 * Caisse::query()
	 * 
	 * @param int $no_caisse
	 * @return void
	 */
	public function query($no_caisse) {	
		$this->no_caisse = $no_caisse;
		$ret = $this->db->select_one("SELECT etat_caisse({$_SESSION['bourse']['idbourse']}, $no_caisse) as ret");	
		// analyse résultat
		// valeurs possibles : 
		//	s=vente;c=2;v=14;u=5;l=gisele 
		//	s=ouverte;c=1;u=12;l=sophie
		//	s=fermee;c=3
		//	s=cloture;c=2
		if(!$ret) {
			// Erreur
			logFatal(__METHOD__."() erreur SQL \nsql=SELECT etat_caisse({$_SESSION['bourse']['idbourse']}, $no_caisse) as ret" ,__FILE__, __LINE__);
		}
		$aa = explode(';', $ret['ret']);
		foreach($aa as $item) {
			if(preg_match('/([scvul])=\s*(.*)\s*$/',$item, $matches)) {
				switch($matches[1]) {
					case 's': 
						$this->etat = $matches[2];
						break;
					case 'c':
						$this->no_caisse= $matches[2];
						break;
					case 'v':
						$this->idvente=$matches[2];
						break;
					case 'u':
						$this->idparticipant = $matches[2];
						break;
					case 'l':
						$this->login = $matches[2];
						break;
					default:
						logInfo(__METHOD__."() $item non reconnu (RegEx)",__FILE__, __LINE__);
						break;	
				}
			} else {
				logInfo(__METHOD__."()  \$matches[1] [{$matches[1]}]",__FILE__, __LINE__);
			}
		}
	}
	
	/**
	 * Caisse::is_owner()
	 * 
	 *  @return bool l'utilisateur courant est l'utilisateur autorisé pour cette caisse
	 */
	public function is_owner() {
		switch($this->etat) {
			case 'fermee': return true;
		 	case 'ouverte':
		 	case 'vente': 
		 		if($this->idparticipant == $this->usr_id) return true;
		 		else return false;
		 		if($this->idparticipant == $this->usr_id) return true;
		 		else return false;
			case 'cloture':
				return false; 
		 	default:
				logInfo(__METHOD__."() etat non reconnu [{$this->etat}]",__FILE__, __LINE__);
				return false;
				break;	
		} 		
	}

	/**
	 * Caisse::get_err_idvente()
	 * 
	 * @return int le numéro de la vente en erreur
	 */
	public function get_err_idvente() {
		switch($this->etat) {
			case 'cloture': 
			case 'fermee': 
		 	case 'ouverte':
		 		return 0;
		 	case 'vente': 
				return $this->idvente;
		 	default:
				logInfo(__METHOD__."() etat non reconnu [{$this->etat}]",__FILE__, __LINE__);
				return false;
				break;	
		} 		
	}

	/**
	 * Caisse::is_closed()
	 * 
	 * @return bool : la caisse est fermée
	 */
	public function is_closed() {
		return $this->etat=='fermee'? true:false;
	}
	
	/**
	 * Caisse::get_etat()
	 * 
	 * @return string etat 
	 */
	public function get_etat(){
		return $this->etat;
	}
	/**
	 * Caisse::get_tbs()
	 * 
	 * @return array (TBS ready)
	 */
	public function get_tbs()
	{
		if(!$this->no_caisse) {
			// Erreur soft !
			exit(__METHOD__.'() Erreur soft ! '.__FILE__.':'.__LINE__);
		}
		$caisse = array(
		'num_caisse'=>$this->no_caisse, 
		'stat'=>'', 
		'info'=>'', 
		'action'=> '', 
		'lib'=>'');
		switch($this->etat) {
			case 'cloture': 
				$caisse['stat'] = 'img/stop.gif';
				$caisse['info'] = 'Ventes clôturées';
				$caisse['action'] = '';
			 	$caisse['lib'] = '';
			 	break;
			case 'fermee':
				$caisse['stat'] = 'img/gris.gif';
				$caisse['info'] = 'Fermée : Prêt pour l\'utilisaton';
				$caisse['action'] = "?st=".S_CAISSE."&no_caisse={$this->no_caisse}";
			 	$caisse['lib'] = 'Ouvrir';
			 	break;
		 	case 'ouverte':
		 		$caisse['info'] = 'En cours d\'utilisation par ';
		 		if($this->idparticipant == $this->usr_id) {
		 			// moi-meme
					$caisse['stat'] = 'img/clign.gif';
					$caisse['info'] .= 'moi-même';
					$caisse['action'] = "?st=".S_CAISSE."&no_caisse={$this->no_caisse}";
				 	$caisse['lib'] = 'Continuer';
		 		} else {
		 			// autre utilisateur
					$caisse['stat'] = 'img/rouge.gif';
					$caisse['info'] .= $this->login;
					$caisse['action'] = '';
				 	$caisse['lib'] = '';
		 		}
			 	break;
		 	case 'vente':
		 		if($this->idparticipant == $this->usr_id) {
		 			// moi-meme
		 			$caisse['info'] = 'Erreur : une vente n\'a pas été clôturée';
					$caisse['action'] = "?st=".S_CAISSE."&no_caisse={$this->no_caisse}&id_vente={$this->idvente}";
				 	$caisse['lib'] = 'Reprendre';
					$caisse['stat'] = 'img/err.gif';
		 		} else {
		 			// autre utilisateur
					$caisse['info'] = 'Une vente est en cours pour '.$this->login;
					$caisse['action'] = '';
				 	$caisse['lib'] = '';
					$caisse['stat'] = 'img/rouge.gif';
		 		}
			 	break;
		}
		return $caisse;		
	}
}



// EoF