<?php
/**
 * Include file: main.inc.php
 *
 * Page d'affichage du menu / accueil
 *
 * @package: bourse
 * @author : FVdW
 * @version $Id$
 *
 */

/** Etat des caisses
*/
define ('FERME',0);
define ('OUVERT',1);
define ('USED',2);


/** Verification droit "caisse"
 */
if($user->get_field('may_caisse') != 'T' && $user->get_field('may_gestion') != 'T') {
	echo "<html><body><h1>Erreur de droits</h1>Vous n'avez pas acc_s au stand <b><i>Caisse</i></b><br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
	exit();
}


/** Acquisition de l'etat des caisses
 */
$aCaisse = array();
$sql = 'SELECT log_caisse.* , vente.idvente , vente.date_vente FROM log_caisse '
        . ' LEFT JOIN vente ON vente.idvente = log_caisse.last_idvente '
        . ' WHERE log_caisse.bourse_idbourse = '.$user->get_field('bourse_idbourse').' AND logout_date IS NULL'
		. ' ORDER BY idlog_caisse ASC';
$n = $db->query($sql);

for($i=1; $i <= $_SESSION['bourse']['nombre_caisse']; $i++) {
  $caisse = array('num_caisse'=>$i, 'stat'=>'img/gris.gif', 'info'=>'Fermée : Prêt pour l\'utilisaton', 'dispo'=>1, 'act'=>'&nbsp;');
  foreach ($db->data as $r) {
	 if($r['no_caisse'] == $i) {
		$caisse['stat'] = 'img/rouge.gif';
		$caisse['dispo'] = 0;
		$caisse['info'] = 'Utilisateur : '.lect_participant($r['participant_idparticipant']).' connecté depuis le : '.$db->date2str($r['login_date'], true).'<br>Derniere vente le '.$db->date2str($r['date_last_op'], true).' sur '.$r['ip'];
		$caisse['act'] = "<img src='img/lock_open.gif' style='cursor:pointer' border='0' title='Déverouiller' alt='Déverouiller' onclick='unlock({$r['idlog_caisse']})' />";
		break;
  	}
  }
  $aCaisse[] = $caisse;
}
$TBS->MergeBlock('caisseTbs','array', $aCaisse);
// EoF