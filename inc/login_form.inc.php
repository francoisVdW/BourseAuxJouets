<?php
/**
 * Include file: login_form class User
 *
 * Formulaire de login
 *
 * @package bourse
 * @version $Revision: 187 $
 * @author FVdW
 */
require_once 'fwlib/cchamp.class.php';
$clogin	= new CChamp("TXT_O_login", '', 20);
$cpwd	= new CChamp("PWD_O_pwd", '', 20);

$aErr = array();
if(isset($_POST['sbmt_login'])) {
	/** Acqusition et ctrl des données du _POST
	*/
	if (!$clogin->chkPost()) $aErr[] = $clogin->getErr();
	if (!$cpwd->chkPost()) $aErr[] = $cpwd->getErr();
	if(!count($aErr)) {
	    if(!$user->checkLogin($clogin->getVal(),$cpwd->getVal())) {
            // afficher form login + msg login non valide
            $aErr[] = "/;Login invalide";
        }
        else return; // fin de ce script
	}
}

$js_all_errTbs = get_JS_a_all_err($aErr);

$loginTbs = $clogin->printFieldEx('Nom de connexion');
$pwdTbs = $cpwd->printFieldEx('Mot de passe');

$TBS = new clsTinyButStrong ;
$TBS->LoadTemplate('tbs/login.html') ;
$TBS->Show(TBS_OUTPUT) ;
exit();
?>
