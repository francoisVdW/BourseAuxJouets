<?php
/**
 * Fichier Include
 *
 * Créé le dimanche 27 mars 2005 15:01:35
 *
 * @author FVdW
 * @package CChamp
 * @version $Revision: 784 $
 * @filesource
 */

/*  - - - - - - - - - - - - - - - - - - - - - - -
 $Log: cchamp.class.php $
 Revision 2.13  2007/01/19 21:25:09  Francois
 *** empty log message ***

 Revision 2.11  2006/11/15 18:39:20  Francois
 Ajout propriété $aff_oblig et methode setAffichOblig() qui indique
 qu'il faut afficher '*' rouge à gauche du champ, même si celui-ci n'est pas obligatoire
 TYPE_O|L|X_nom.
 Utile pour champs co-titulaire

 Revision 2.10  2006/11/13 21:36:33  Francois
 pour zone type TXT ou AREA : dans param "liste' du constructeur reconnais
 une valeur : nbre de colonne de la zone de saisie, ou, pour AREA cols*rows

 Revision 2.9  2006/11/08 18:57:25  Francois
 fct read_a_data() accepte un param supplémentaire : $str_pad = texte a accoler aux données si elles existent

 Revision 2.8  2006/09/25 00:49:13  Francois
 changement sting " -->'

 Revision 2.6  2006/08/29 21:57:13  Francois
 documentation maj (phpDoc commentaire de fichier)

 Revision 2.5  2006/08/24 18:34:42  Francois
 Ajout methode getName(), printFieldEx(), isModified()

 Revision 2.3  2006/08/10 19:07:38  Francois
 Correction bug pour CBox
 $this->size : positionné une seule fois dans le constructeur.
 printfield() : uniformisation usage $this->size --> <input size=... maxlength=.. >

 Revision 2.2  2006/08/09 18:07:57  Francois
 Ajout methode getId() : retourne l'id de l'<input>
 Modif printField() <-> displField() l'affichage de l''*' du champ oblig est pris en charge par printField() --> on peut
    utiliser printField()  a la palce de displayField() pour champs de console d'admin.

 Revision 1.2  2005-11-08 22:04:43+01  francois
 Correction msg d'erreur : manque \' --> erreur JS

 Revision 2.4  2005/09/18 22:36:31  francois
 correction affichage des valeurs par défautpour CB (plusieurs valeurs possibles)

 Revision 2.0  2005/09/13 16:43:48  francois
 Pour demo 14 sept 05

 Revision 1.8  2005/08/24 23:21:46  francois
 Cas de groupes de CB : si groupe CChamp->val contient un string formant
 une liste des valeurs cochées.

 Revision 1.7  2005/08/23 23:10:58  francois
 Modif affichage CB: Si CB groupés, alors les noms sont différencés par l'ajout du contenu de value
 comportement simitaire à la génération des Id

 Revision 1.6  2005/08/22 23:45:55  francois
 mise en form PhpDocumentor

 Revision 1.5  2005/08/20 21:49:09  francois
 Methode  printField() : si ctrl est RD ou CB, génère attribut id="nom_du_champ_VALEUR"
 ce qui permet d'accèder au ctrl individuellement par fct getElementById()

 Revision 1.3  2005/08/06 00:58:20  francois
 Ctrl des critères d'éligibilités pour l'accès aux aides LP en PHP ... En chantier

 Revision 1.2  2005/08/02 20:52:57  francois
 ajout commentaires PHP Doc

 Revision 1.15  2005/06/10 23:19:48  francois
 Correction bug dis_test (propriete $val pas MaJ)
 Utilisation balise <tt></tt> pour indiquer champ obligatoire (n'utilise plus feuille de style)

 Revision 1.14  2005/06/06 23:14:42  francois
 Maxlen n'est plus une propriété de la classe (uniquement utilisée par print_field)
 ajout methode displFieldGes() affiche le champ pour l'inteface de  gestion

 Revision 1.13  2005/06/03 16:35:41  francois
 fct echo_JS() : n'imprime plus le code JS exhaustivement : fait référence à un
 fichier externe cchamp.js

 Revision 1.12  2005/06/01 00:07:56  francois
 ctrl date assure un formatage EXACT de ladatejj/mm/aaaa

 Revision 1.11  2005/05/26 23:36:05  francois
 n'affiche pas '*' pour CB obligatoire (n'a pas de sens)

 Revision 1.10  2005/05/24 23:47:53  francois
 affichage de '*' obligatoire effectué tout a lafin de displayField()

 Revision 1.9  2005/05/21 12:45:48  francois
 En chantier : remodeling class cChamp, gestion des erreurs PHP/JS  suite

 Revision 1.8  2005/05/19 23:05:53  francois
 En chantier : remodeling class cChamp, gestion des erreurs PHP/JS  suite

 Revision 1.7  2005/05/14 22:25:42  francois
 En chantier : remodeling class cChamp, gestion des erreurs PHP/JS  suite

 Revision 1.6  2005/05/09 22:29:05  francois
 En chantier : remodeling class cChamp, gestion des erreurs PHP/JS

 Revision 1.5  2005/05/09 16:36:00  francois
 En chantier : remodeling class cChamp, gestion des erreurs PHP/JS

 Revision 1.3  2005/05/02 22:44:07  francois
 fct assurance_exists() : supprimée : les différentes assurances sont stockées dans la db

 Revision 1.2  2005/04/30 00:13:20  francois
 Ajout support <textarea></textarea>

  - - - - - - - - - - - - - - - - - - - - - - - */
if (defined('CCHAMP_CLASS')) return;
define('CCHAMP_CLASS',1);

define('FIELD_MAX_SZ', 256);  // voir taille max varchar mysql


/**
 *  Fonction JS appelee par methode de la classe CChamp
 *   class JS String doit etre inclue !
 */
function echo_js_cchamp()
{
echo "<script type=\"text/javascript\" src=\"js/cchamp.js\"></script>";
}


/**
 * Recupere array des erreur detectées par fct PHP et convertion en Array JS
 *
 * @param array $a_err[] Array des erreurs detectées par fct PHP sous la forme ["id_field;msg d'erreur", id_field;msg erreur", ... ]
 */
function get_JS_a_all_err($a_err=FALSE) {
	/** ctrl param
	*/
	if(!$a_err) return "var a_all_err=new Array();";
	
	// if(!isset($a_err)) logFatal("Erreur Soft: echo_JS_a_all_err() param a_err non defini",__FILE__,__LINE__);
  	if(!is_array($a_err)) logFatal("Erreur Soft: echo_JS_a_all_err() param a_err n'est pas []",__FILE__,__LINE__);

	$s="";
  	if (count($a_err)) {
    	foreach($a_err as $e) { if(trim($e)!="") $s .= "\"$e\",";}
    	$s = substr($s, 0, -1); /// retire derniere ','
  	}
	return "var a_all_err=new Array($s);";
}



/**
 * definition de champs de saisie pour formulaires .
 *
 *  Le nom doit contenir 3 items separé par des '_'
 *  Le nom du champ doit strictement repondre à la syntaxe suivante :
 *  CAT_Obl_libre
 *   - CAT est la categorie ou type les valeurs reconnues sont
 *      o TXT = texte libre
 *      o AREA = area
 *      o NOM = texte nom propre refuse les chiffres et caracteres speciaux
 *      o DATE = date au format jj/mm/aaaa
 *      o NUM = intetger
 *      o MNT = Montant (float)
 *      o TEL = chaine composee de 10 digit
 *      o CP = chaine composee de 5 digit + traitement special pour la corse
 *      o EMAIL = chaine e-mail (ctrl syntaxe)
 *      o PWD = password
 *      o HID = hidden
 *      o CB = check box
 *      o RD = radio
 *      o SEL=select-one
 *   - Obl : indique si le champ est obligatoire (doit être valorise)
 *     Pour SELect : la valeur "??" est considérée comme non valorisee
 *     Pour Check Box : pas de Ctrl
 *   - libre nom libre
 * Par exemple "MNT_O_salaire" : Attend un montant, champ obligatoire *
 * La methode affich() : creera un <input> avec les paramètres correspondant,
 * l'evenement onBlur sera cree avec l'appel de la fct JS qui correspond au type de champ
 * Si le nom est "MNT_O_salaire" --> <input type=text onblur='fmtMnt(this)'>
 * La methode chckPost() : recherche dans $_POST l'entree "MNT_O_sal" et effectue les controles
 * correspondants à ce type de champ
 *
 * Exemple
 * < ?php
 *   $sal=new CChamp("MNT_O_salaire", 500, 0, "100|1000");  // valeur min=100 max=1000 par defaut 500
 *   if (isset($_POST['submit']))
 *       if (!$sal->chkPost()) $a_err[] = $sal->getErr();
 *
 *   // affichage du formulaire + ctrl JS
 * ? >
 * <script language="javascript" src="js/chk.js"></script>
 * <script language="javascript" src="js/cchamp.js"></script>
 * <script>
 *  var a_all_err=new Array();
 *  function chk(form){
 *    a_all_err=new Array();  // pas redondant !
 *    if (chkUnit(form)) return true;
 *    if (a_all_err.length > 0) {
 *      var x=a_all_err[0].split(";");
 *      alert("Erreur : "+x[1]);
 *      var o=document.getElementById("i"+x[0]);
 *      if (o && typeof o=='object') o.focus();
 *      return false;
 *    }
 *    return true;
 *  }
 * </script>
 * <form method=POST onsubmit="return chk(this)">
 *   < ? echo $sal->printField(); ? >
 * </form>
 *
 * @package bourse
 */
class CChamp {
	var $name;
  	var $default_val;
	var $cat;
	var $obl=0;
	var $size=0;
	var $displ_nb_col = false;
	var $displ_nb_lin = false;
	var $id='';
	var $min = false;
	var $max = false;
	var $a_opt = array();
	var $ereg = false;
	var $err = '';
	var $val = NULL;
	var $dis_test = FALSE;
	var $aff_oblig = FALSE;  // true pour forcer l'affichage de * rouge a gauche du champ de saise
  /**
   * constructeur CChamp
   * @param string name = nom du champ (separateur '_')
   * @param string default_val = valeur par defaut
   * @param int size = taille max du champ
   * @param string list = liste des options possible separateur ="|" ou pour MNT et NUM valeurs min-max, pour TXT et AREA nbr cols * rows pour affichage
   */
  function CChamp ($name, $default_val='', $size=0, $list='', $disable_test=FALSE)
  {
    /** ctrl syntaxe nom du champ
    */
    if (!preg_match('/^(TXT|AREA|NOM|DATE|NUM|MNT|TEL|CP|EMAIL|PWD|HID|CB|RD|SEL)_(O|L|X)_(.*)$/', $name, $a)) logFatal("CChamp::CChamp() param \$name le nom du champ est invalide [$name]", __FILE__, __LINE__);

    /** catégorie / type de champ
    */
    $this->cat = $a[1];
    /** obligatoire O/N
    */
    $this->obl = $a[2]=='O'? true:false;
    /** taille de l'<input >
    */
    $this->name = $name;
    /** valeur par defaut (affichee dans <input> ou <select> ou <area>)
    */
    $this->default_val = $default_val;
    /** fixe les taille pour les type definis, pour les autres type analyze param $size
    */
	if ($size != 0) {
      if ($size == '') $this->size = 0;
      elseif (!is_numeric($size)) logFatal("CChamp::CChamp() param \$size du champ {$this->name} n'est pas numeric [$size]", __FILE__, __LINE__);
    }
    switch($this->cat) {
    	case 'DATE':
      	case 'TEL':
        	$this->size=10;
       		break;
    	case 'MNT':
        	$this->size=9;
        	break;
    	case 'NUM':
    		$this->size=7;
    		break;
    	case 'CP':
        	$this->size=5;
        	break;
    	case 'PWD':
        	$this->size=20;
        	break;
		case 'TXT':
			if($list) {
				// $list contient qlqchose : c'est la taille à afficher du champ
				if(is_numeric($list)&& $list > 0 && $list < $size) $this->displ_nb_col = $list;
			}
        	$this->size = $size;
        	break;
    	case 'AREA':
			if($list) {
				// $list contient qlqchose : c'est la taille du textarea col*line
				$a = explode('*',$list);
				if(count($a)==2) {
					if(is_numeric($a[0]) && $a[0] > 0 && is_numeric($a[1]) && $a[1] > 0) {
						$this->displ_nb_col = $a[0];
						$this->displ_nb_lin = $a[1];
					}
    			}
			}
        	$this->size = $size;
        	break;
    	default:
        	/** si pas type predefini : analyze param $size avec limite
        	*/
        	if ($size > FIELD_MAX_SZ) {
				logFatal("CChamp::CChamp() la taille du champ {$this->name} est trop grande [$size] : limite=".FIELD_MAX_SZ,__FILE__, __LINE__);
			}
        	$this->size = $size;
        	break;
    }
    
    /** determine l'id du champ de saisie ... sauf pour Radio et Check Box
    */
    if ($this->cat == 'RD' || $this->cat == 'CB') {
    	$this->id = ''; // id pour chaque item
    } else {
    	$this->id = "i$name";
    }
    /** Si $list valorisé : 
    */    
    if ($list != '') {
      	switch ($this->cat) {
	        case 'NUM':
	        case 'MNT':
				$a = explode('|',$list);
          		if (count($a)) {
            		/** valorise min et max
            		*/
            		$this->min = $a[0];
            		if (isset($a[1])) $this->max = $a[1];
          		}
          		break;
	        case 'SEL':
    	    case 'RD':
        	case 'CB':
        		$a = explode('|',$list);
        		if (count($a)) {
            		foreach($a as $l) {
              			/** valorise $this->a_opt[ 'lbl'=> 'val' ]
	              		*/
    	          		$aa = explode('=',$l);
        	      		if (count($aa) > 1) $this->a_opt[] = array('lbl'=>$aa[1], 'val'=>$aa[0]);
            	  		else $this->a_opt[] = array('lbl'=>false, 'val'=>$l);
            		}
          		}
          		break;
        	case 'NOM':
          		$this->ereg = $list;
          		break;
    	}
	}
    $this->val=NULL;
    $this->err='';
    $this->dis_test=$disable_test;
  }
  
  /**
   * Determine si le champ a été modifie
   * 
   *  Condition : default_val != val ET pas d'erreur
   *
   * @author FVdW
   *
   * @return bool
   */
  function isModified()
  {
    if ($this->err != '') return false;
    return $this->val != $this->default_val? true : false;
  }

  	/**
   	* Controle des limites
   	*
   	* Si des valeurs min et max sont definies (@see CChamp::CChamp)
   	* @param int v
   	* @return bool : true si valeur ds les limites
	*/
	function _ctl_limit($v)
	{
    	if ($this->min) {
      		if($v < $this->min) {
        		$this->err="La valeur de ce champ doit etre supperieure ou egale au palier (".$this->min.")";
        		return false;
      		}
      	}
    	if ($this->max) {
      		if($v > $this->max) {
        		$this->err="La valeur de ce champ doit etre inferieure ou egale au plafond (".$this->max.")";
        		return false;
      		}
      	}
		return true;
  	}

	/**
	* Verifie la présence d'une valeur dans $this->a_opt.
	*
	* fct uniquement appelée par $this->_chkVal().
	*
	* @param string $val
	* @return bool trouve
	*/
	function _val_in_list($val)
	{
		/** Si la liste est vide...
    	*/
    	if (!count($this->a_opt)) {
    		return false;
		}
		foreach($this->a_opt as $opt) {
			if ($opt['val'] == $val) return true;
    	}
    	return false;
	}

	/**
	* Controle du Nom
	*
	* Si une expression régulière a été stockées voir param $list
	* alors ctrl si match.
	* Dans tous les cas, teste l'EReg par défaut pour les noms /[A-Za-z\.\-\']/
	*
	* @param int v
	* @return bool : true si valeur match l'une des ereg
	*/
	function _ctl_nom($v)
	{
		/** Première tentative avec ereg par defaut
		 */
		if (preg_match('/[-A-Za-z\.\' ]+$/',$v)) return true;
		/** Si echec, test autre regExp
		 */
		if ($this->ereg)
		  if (preg_match('/'.$this->ereg.'/',$v)) return true;
		/** Erreur ...
		*/
		$this->err="Le format du nom est incorrect";

		return false;
	}

  	/**
   	* Acquisition de la donnee à partir de $_POST puis passe le ctrl a _chkVal()
   	*
	* @return bool true = OK, false = Erreur
	*/
	function chkPost()
  	{
		$this->err='';
	    if ($this->cat != 'CB') {
    		/** aquisition de la valeur POST pour tous les champs (sauf CeckBox)
      		*/
      		if (isset($_POST[$this->name])) {
        		$v = trim($_POST[$this->name]);
      		} else {
        		// champ pas assigné (pas present dans le _POST)
        		$v=($this->cat=='SEL')? '??': '';
      		}
    	} else {
      		/** traitement particulier pour les CB
			* Les champs de type CB retournent un string contenant les valeurs des CB checked
    		* sous la forme "val1;val5;val6"
    		*/
      		$v = '';
      		foreach ($this->a_opt as $opt) {
        		// plusieurs Items -> plusieurs name : CB_x_name_val1, CB_x_name_val2 ... (comme id)
        		$name = $this->name."_".$opt['val'];
        		if (isset($_POST[$name]))  $v .= trim($_POST[$name]) .';';
      		}
      		if ($v != '') $v = substr($v, 0, -1); // retire dernier ';'
    	}
    	/** Appel fct ctrl valeur des champs
    	*/
    	return $this->_chkVal($v);
	}

	/**
	* Indique champ non valorisé (même pour SELECT).
	*
	* @return bool
	*/
	function isEmpty()
	{
		if (is_null($this->val) || $this->val=='' || ($this->cat=='SEL' && $this->val=='??') )return TRUE;
		else return FALSE;
	}
	
	/**
	 * Affiche le caractère 'obligatoire' SANS tenir compte de l'attribut du nom _O_ / _L_
	 *
	 * @author : FVdW
	 * @date : 15/11/2006
	 *
	 * @param boolean $affich true=force l'affichage du caractère '*' rouge à gauche du champ de saisie
	 *
	 */
	function setAffichOblig($affich){
		if($affich) {$this->aff_oblig=true;}
		else {$this->aff_oblig=false;}
	}
	

	/**
	* Retourne le nombre de choix possibles pour : Select, Radio ou Check-box
	*
	* Si l'obj n'est pas SEL, RD ou CB, retourne -1
	*
	* @return int nbr de choix possibles
	*/
	function countOptions()
	{
	 if (in_array($this->cat, array('SEL','RD','CB')))
	   return count($this->a_opt);
	 else return -1;
	}

	/**
	* Ctrl de la valeur passee en arg. et stocke la donnée ds this->val
	*
	* Valorise this->err avec message d'erreur
	*
	* @return bool true = OK, false = Erreur
	* @param string $v : valeur a tester
	*/
	function _chkVal($v)
	{
	$this->val = $v;

	if ($this->dis_test) {return true;}
	// ctrl taille max (si pas specifié : limite à 255 bytes == FIELD_MAX_SZ)
	if (in_array($this->cat, array('SELECT','RD','CB')))
	  $v = substr($v,0,FIELD_MAX_SZ);
	elseif ($this->size)  // une taille a été specifiee
	  $v = substr($v,0, $this->size);
	else
	  $v = substr($v,0, FIELD_MAX_SZ);

	if ($v == '' || ($v=='??' && $this->cat=='SEL') )  {
	  $this->val=NULL;
	  if ($this->obl)
	    switch($this->cat) {
	      case 'RD':
	        $this->err ="Vous devez effectuer un choix";
	        return false;
	      case 'SEL':
	        $this->err="Sélection obligatoire pour cette liste";
	        return false;
	      case 'CB':
	      case 'HID':
	        // on ignore
	        $this->val = $v;
	        return true;
	      default:
	        $this->err="Vous devez enter une valeur pour ce champ";
	        return false;
	    }
	  else {
	    return true; // champ vide ET pas oblig.
	    }
	  }
	// ctrl
	switch($this->cat) {
	  case 'TXT':
	  case 'HID':
	  case 'PWD':
	  case 'AREA':
	    // on ignore
	    break;
	  case 'RD':
	  case 'SEL':
	    // si liste vide pas de test
	    if (count($this->a_opt))
	      if (!$this->_val_in_list($v)) {
	        $this->err="Valeur illicite ! ($v)";
	        return false;
	      }
	    break;
	  case 'CB':
	    // si groupe de CB inutile de verifier les valeurs
	    break;
	  case 'NUM':
	    if (!is_numeric($v) && (intval($v) != $v)) {
	      $this->err="La valeur de ce champ doit être numérique";
	      return false;
	    }
	    $v = intval($v);
	    if (!$this->_ctl_limit($v)) return false;
	    break;
	  case 'MNT':
	    if(!is_numeric($v)) {
	      $this->err="La valeur de ce champ doit être numérique (avec ou sans décimale)";
	      return false;
	    }
	    $v = floatval($v);
	    if (!$this->_ctl_limit($v)) return false;
	    break;
	  case 'NOM':
	    if (!$this->_ctl_nom($v)) return false;
	    break;
	  case 'TEL':
	    if (!preg_match('/^0[0-9]{9}$/',$v)) {
	      $this->err="Le format du numéro de téléphone est incorrect (que des chiffres, pas d\'espaces ni séparateurs)";
	      return false;
	    }
	    break;
	  case 'EMAIL':
	    if (!preg_match('/^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+'.
	           '@'.
	           '[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.'.
	           '[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$/',
	           $v) ) {
	      $this->err="Le format de cet e-mail est incorrect";
	      return false;
	    }
	    break;
	  case 'DATE':
	    list($d, $m, $y) = preg_split('/[\/\.\-]/', $v,3);
	    if (!checkdate($m, $d, $y)) { // format US
	      $this->err="La date est incorrecte";
	      return false;
	    }
	    $v = "$d/$m/$y";
	    break;
	  case 'CP':
	    if (!preg_match('/^((2[A-B])|([0-9]{2}))[0-9]{3}$/',$v)) {
	      $this->err="Le format du code postal est incorrect";
	      return false;
	    }
	    break;
	  default:
	    $this->err="Erreur logiciele dans _chkVal()". basename(__FILE__) .":".__LINE__." cat=".$this->cat;
	    return false;
	}
	return true;
	}

	/**
	* Retourne message d'erreur formaté.
	*
	* message sous la forme "id_du_champ;message_d_erreur"
	*
	* @return string : message d'erreur formaté, Si pas d'erreur retourne ""
	*/
	function getErr($msg_simple=FALSE) {
	    if($msg_simple) return $this->err;
	    else {
	 		if ($this->err=='') return '';
	 		return $this->id.';'.$this->err;
		}
	}

	/**
	* Retourne l'id (<tag id=... >) de l'objet.
	*
	* Rem : ce n'est pas l'id du <span> qui encardre le champ et le label du champ
	* il s'agit de l'id de l'<input>, <textarea> ou <select>
	*
	* @return string id, false pour les Id multiples RD et CB
	*/
	function getId() {
	  return $this->id;
	}
	/**
	* Retourne le 'name' (<tag name=... >) de l'objet.
	*
	* Rem : ce n'est pas le 'name' du <span> qui encardre le champ et le label du champ
	* il s'agit de l'id de l'<input>, <textarea> ou <select>
	*
	* @return string id, false pour les Id multiples RD et CB
	*/
	function getName() {
	  return $this->name;
	}

	/**
	* Produit une chaine HTML <input type="hidden"> avec le nom du controle corrrespondant
	* la valeur etant passée en paramètre
	*
	* @param string $value : valeur du champ (si omise prend la valeur par défaut)
	* @param string $more : attributs suppléementaires optionnels
	*/
	function printHidden($value='??', $more='') {
	if ($value == '??') {
	  $value = is_null($this->val)? $this->default_val:$this->val;
	}
	$id= $this->id==''? '':"id=\"{$this->id}\"";
	return "<input type=\"hidden\" name=\"{$this->name}\" $id value=\"$value\" $more />";
	}


	/**
	* Produit une chaine HTML correspondant au controle demandé
	*
	* @param string $more : attribut supplémentaires
	* @param string $onBlur : JS à ajouter pour l'Evt onBlur
	*/
	function printField($more='',$onblur='') {
	$maxlen=false;
	$size=false;
	switch ($this->cat) {
	  case 'SEL':
	    $type='select';
	    break;
	  case 'RD':
	    $type='radio';
	    break;
	  case 'CB':
	    $type='checkbox';
	    break;
	  case 'HID':
	    $type='hidden';
	    break;
	  case 'PWD':
	    $type="password";
	    $size=10;
	    $maxlen=$this->size;
	    break;
	  case 'TEL':
	  case 'DATE':
	  case 'CP':
	  case 'MNT':
	  case 'NUM':
	    $type='text';
	    $maxlen=$this->size;$size=$this->size;
	    break;
	  case 'NOM':
	    $type='text';
	    $size = ($this->size && $this->size < 20)? $this->size : 25;
	    $maxlen = $this->size? $this->size:40;
	    break;
	  case 'EMAIL':
	    $type='text';
	    $size = ($this->size && $this->size < 30)? $this->size : 35;
	    $maxlen = $this->size? $this->size:80;
	    break;
	  case 'AREA':
	    $type='area';
	    if (!$this->size) {$size=FIELD_MAX_SZ;$maxlen=FIELD_MAX_SZ;}
	    else {$size = $this->size; $maxlen = $this->size; }
	    break;
	  case 'TXT':
	  default:
	    $type='text';
				if($this->displ_nb_col==false) {
	        $size = ($this->size && $this->size < 20)? $this->size : 35;
				} else {
				  $size = $this->displ_nb_col;
				}
	    $maxlen = $this->size? $this->size:50;
	    break;
	}
	$dflt = is_null($this->val)? $this->default_val:$this->val;
	$oblig = ($this->aff_oblig || ($this->obl && $this->cat != 'CB' && $this->cat != 'HID'))? "<tt style=\"color:red;font-weight:bolder\">*&nbsp;</tt>":"<tt>&nbsp;&nbsp;</tt>";

	$s=$oblig;
	switch ($type) {
	  // - - - - - - - - - - - - - - - - -
	  case 'select':
	    $s .= "<select name=\"".$this->name."\" size=\"1\" id=\"{$this->id}\" $more>";
	    foreach ($this->a_opt as $opt) {
	      if ($opt['lbl'] !== false) {
	        $lbl = htmlentities($opt['lbl'], ENT_COMPAT,"ISO-8859-15");
	        $val = $opt['val'];
	      } else {
	        $lbl = htmlentities($opt['val'], ENT_COMPAT,"ISO-8859-15");
	        $val = $opt['val'];
	      }
	      $s.= "<option value=\"$val\"";
	      $s.=($dflt==$val)? " selected>":">";
	      $s.="$lbl</option>";
	    }
	    $s.= "</select>";
	    break;
	  // - - - - - - - - - - - - - - - - -
	  case 'radio':
	    //$s="";
	    foreach ($this->a_opt as $opt) {
	      $v = $opt['val'];
	      $s.= "<input type=\"radio\" name=\"".$this->name."\" value=\"$v\" id=\"".$this->name."_".$v."\"";
	      $s.= ($dflt==$v)? " checked":"";
	      $s.= " $more />".($opt['lbl']===false? $opt['val'] : $opt['lbl']);
					$s .= '&nbsp;';
	    }
	    break;
	  // - - - - - - - - - - - - - - - - -
	  case 'checkbox' :
	    $a_dflt = explode(";",$dflt);
	    foreach ($this->a_opt as $opt) {
	      $v = $opt['val'];
	      // plusieurs Items -> plusieurs name : name_val1, name_val2 ... (comme id)
	      $name = $this->name."_".$v;
	      $s.= "<input type=\"checkbox\" name=\"".$name."\" value=\"$v\" id=\"".$this->name."_".$v."\"";
	      $s.= (in_array($v, $a_dflt))? " checked":"";
	      $s.= " $more /> ". ($opt['lbl']===false? $opt['val'] : $opt['lbl']);
					$s .= '&nbsp;';
	    }
	    break;
	  // - - - - - - - - - - - - - - - - -
	  case 'password':
	    $fmt="onBlur='this.value=this.value.clrText();$onblur'";
	    $sz_max="size=\"$size\" maxlength=\"$maxlen\"";
	    $s .= "<input type=\"password\" name=\"".$this->name."\" id=\"{$this->id}\" value=\"$dflt\" $sz_max $more $fmt />";
	    break;

	  // - - - - - - - - - - - - - - - - -
	  case 'hidden':
	    return "<input type=\"hidden\" name=\"".$this->name."\" id=\"{$this->id}\" value=\"$dflt\" $more />";
	    break;
	  // - - - - - - - - - - - - - - - - -
	  case 'area':
				if($this->displ_nb_col!=FALSE && $this->displ_nb_lin!=FALSE) {
				  $rows = $this->displ_nb_lin;
				  $cols = $this->displ_nb_col;
				} else {
	        $rows = ceil($maxlen/56);
					$cols = 56;
				}
	    $s .= "<textarea name=\"".$this->name."\" id=\"{$this->id}\" cols=\"$cols\" rows=\"$rows\" >$dflt</textarea>";
	    break;
	  // - - - - - - - - - - - - - - - - -
	  default:
	    $fmt='onBlur="';
	    switch ($this->cat) {
	      case 'TEL':   $fmt .= "fTel(this);"; break;
	      case 'CP':    $fmt .= "fCP(this);"; break;
	      case 'DATE':  $fmt .= "fDate(this);"; break;
	      case 'NOM':   $fmt .= "fNom(this);"; break;
	      case 'EMAIL': $fmt .= "fEmail(this);"; break;
	      case 'NUM':   $fmt .= "fNum(this);"; break;
	      case 'MNT':   $fmt .= "fMnt(this);"; break;
	      case 'TXT':   $fmt .= "this.value=this.value.clrText();"; break;
	    }
	    $fmt .= $onblur.'"';

	    $sz_max="size=\"$size\" maxlength=\"$maxlen\"";
	    $s .= "<input type=\"text\" name=\"".$this->name."\" value=\"$dflt\" id=\"{$this->id}\" $sz_max $more $fmt />";
	    break;
	}
	return $s;
	}
		/**
	* Produit une chaine HTML correspondant au controle demandé + formattage simple
	*
	* NB le CSS doit contenir :
	* form label
	* {
	*   display: inline;
	*   float: left;
	*   width: 80px;
	* }
	*
	* @param string $label : texte à gauche du champ
	* @param string $more : attribut supplémentaires
	* @param string $onBlur : JS à ajouter pour l'Evt onBlur
	*/
	function printFieldEx($label='',$more='',$onblur='') {
		return "<label for=\"{$this->name}\">$label</label>".$this->printField($more,$onblur);
	}

	/**
	* retourne la valeur de cet obj
	*
	* @author FVdW
	*
	* @return mixed valeur
	*/
	function getVal()
	{
		if ($this->isEmpty()) return "";
		else return $this->val;
	}

	/**
	* retourne la valeur de cet obj : si val est non empty retourne val sinon default_val
	*
	* @author FVdW
	*
	* @return mixed valeur
	*/
	function getAnyVal()
	{
		if (!$this->isEmpty()) return $this->val;
		else return $this->default_val;
	}

	/**
	* Retourne la valeur de cet obj : formatee pour insert/update dans DB
	*
	* Si cat == MNT ou INT : retourne une chaine type digit - Si empty retourne Null
	* si cat == DATE : retourne 'AAAA-MM-DD' - Si empty retourne Null
	* Autres cas retourne une chaine encadree par '' et escape - Si empty retourne ''
	*
	* @author FVdW
	*
	* @return string DB formated value
	*/
	function getDbVal()
	{
	    switch($this->cat) {
	        case "MNT":
	            if($this->isEmpty()) return "Null";
	            else return sprintf("%f", $this->val);
	            break;
	        case "NUM":
	            if($this->isEmpty()) return "Null";
	            else return sprintf("%d", $this->val);
	            break;
			case "DATE":
			    // la date est stockée sous la forme j/m/a
			    if($this->isEmpty()) return "Null";
				else {
				    list($d,$m,$y) = explode($this->val);
				    return sprintf("%4d-%02d-%02d", $y,$m,$d);
				}
			    break;
			default:
	            if($this->isEmpty()) return "''";
	            else {
	          		$str = preg_replace('/(SELECT )|(INSERT )|(DELETE )|(UPDATE )|(DROP )|( UNION )|( OR )|(\/\*)/','',$this->val);
	          		$str = str_replace('\\','',$str); // jamais de '\' dans les strings
	          		return "'".addslashes($str)."'";
				}
		}
		if (!$this->isEmpty()) return $this->val;
		else return $this->default_val;
	}
} //class

?>