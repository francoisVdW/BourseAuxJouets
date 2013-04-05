<?php
/**
 * Affiche tous les articles
 * 
 * @package: bourse
 * @author : FVdW
 * @version $Revision: 187 $
 *
 */

/** Verification droit d'acces au tableau de bord
 */
if($user->get_field('may_gestion') != 'T') {
	exit("<html><body><h1>Erreur de droits</h1>Vous n'avez pas acc_s au tableau de bord<br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>");
}


/** Info ARTICLES 
 */
/* 
$sql = "SELECT d.date_depot, dd.nom, v.date_vente, a.* FROM article a LEFT OUTER JOIN vente v ON v.idvente=a.vente_idvente,
depot d, deposant dd
WHERE a.depot_iddepot=d.iddepot
AND dd.iddeposant=d.deposant_iddeposant";
$n = $db->query($sql);
*/
$nowTbs = date("d/m/y à H:i:s");
$TBS->MergeBlock('main',$db->get_link(),'SELECT d.iddepot, dd.nom, dd.prenom, dd.tel, d.date_depot FROM depot d, deposant dd WHERE d.deposant_iddeposant=dd.iddeposant AND d.bourse_idbourse='.$_SESSION['bourse']['idbourse'].' ORDER BY d.iddepot');
$TBS->MergeBlock('sub',$db->get_link(),"SELECT v.date_vente, a.* FROM article a LEFT OUTER JOIN vente v ON v.idvente=a.vente_idvente WHERE (a.depot_iddepot='%p1%') ORDER BY a.idarticle");
?>