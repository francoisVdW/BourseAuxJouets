<?php
/**
 * Include file: Compte des Caisses
 *
 * @package: bourse
 * @author : FVdW
 * @version $Id$
 *
 */

/** Verification droit "gestion"
 */
if($user->get_field('may_gestion') != 'T') {
	echo "<html><body><h1>Erreur de droits</h1>Vous n'avez pas acces à la fonction <b><i>compte des caisses</i></b><br><a href='?st='". S_MAIN ."'>Retour au menu</a></body></html>";
	exit();
}

/*
// Fond de caisses
$sql = "SELECT fond_de_caisses FROM bourse WHERE idbourse ={$_SESSION['bourse']['idbourse']}";
$n = $db->query($sql);
if($n != 1) logFatal("\$n=$n\n\$sql=$sql\n",__FILE__, __LINE__);
$aFondsCaisses = @unserialize($db->data[0]['fond_de_caisses']);
if (!is_array($aFondsCaisses)) if($n != 1) logFatal("Erreur unserialize()  [{$db->data[0]['fond_de_caisses']}]",__FILE__, __LINE__);
*/

// Ventes - Articles
$sql = "SELECT 
	no_caisse, 
	round(mnt_esp*100 )/100 AS esp, 
	round(mnt_chq*100 )/100 AS chq, 
	round(mnt_autr*100)/100 AS autr,
	get_fond({$_SESSION['bourse']['idbourse']}, no_caisse) AS fond,
	count(article.idarticle) AS nb_article
FROM vente, article
WHERE bourse_idbourse ={$_SESSION['bourse']['idbourse']}
AND vente.idvente=article.vente_idvente
AND date_vente IS NOT NULL
GROUP BY idvente
ORDER BY no_caisse, idvente";
$n = $db->query($sql);
if($n < 0) logFatal("\$n=$n\n\$sql=$sql\n",__FILE__, __LINE__);

/** calcule les sommes des especes / chèques /autres  + detail des chq
*/
$aCaisse = array();
$cur_caisse = -1;
foreach($db->data as $r) {
	if($cur_caisse != $r['no_caisse']) {
		if($cur_caisse != -1) {
			// fin de la serie prec
			$aCaisse[] = array('no_caisse'=>$cur_caisse, 'esp'=>$esp, 'ttl_esp'=>$esp+$fond, 'chq'=>$chq, 'autr'=>$autr, 'aChq'=>$aChq , 'ttl'=>$esp+$chq+$autr, 'nb_vente'=>$nb_vente, 'nb_article'=>$nb_article, 'fond'=>$fond);
  		}
  		// nouvelle serie
		$aChq = array();
		$cur_caisse = $r['no_caisse'];
		$esp = 0;
		$chq = 0;
		$autr= 0;
		$nb_vente = 0;
		$nb_article = 0;
		$fond = $r['fond'];
		
	}
	$nb_vente++;
	$nb_article += $r['nb_article'];
	$esp  += $r['esp'];
	$chq  += $r['chq'];
	$autr += $r['autr'];
	if ($r['chq'] > 0) $aChq[] = array('no_caisse'=>$r['no_caisse'], 'mnt'=>$r['chq']);
}

if($cur_caisse != -1) {	
	$aCaisse[] = array('no_caisse'=>$cur_caisse, 'esp'=>$esp, 'ttl_esp'=>$esp+$fond, 'chq'=>$chq, 'autr'=>$autr, 'aChq'=>$aChq, 'ttl'=>$esp+$chq+$autr, 'nb_vente'=>$nb_vente, 'nb_article'=>$nb_article, 'fond'=>$fond);
	}
else {
	$aCaisse[] = array('no_caisse'=>'--', 'esp'=>0, 'ttl_esp'=>0, 'chq'=>0, 'autr'=>0, 'aChq'=>array(), 'ttl'=>0, 'nb_vente'=>0, 'nb_article'=>0, 'fond'=>$fond);
}

/** Variables TBS
 */
$nbr = $TBS->MergeBlock('caisseTbs','array', 'aCaisse');
if ($nbr > 0) $TBS->MergeBlock('subTbs','array', 'aCaisse[%p1%][aChq]');
// EoF