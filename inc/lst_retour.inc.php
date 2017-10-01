<?php
/**
 * Affiche tous les articles retournés
 * 
 * @package: bourse
 * @author : FVdW
 * @version $Id$
 *
 */
$sql = 'SELECT 
    a.depot_iddepot
    , a.idarticle
    , a.description
    , a.prix_achat
    , r.prix_vente
    , r.comment
    , r.date
    , r.no_caisse 
FROM
    retour r 
    INNER JOIN article a 
        ON a.retour_idretour = r.idretour 
WHERE a.retour_idretour IS NOT NULL 
ORDER BY r.date';

$n = $db->query($sql);
if ($n < 1) {
	$nTbs = '';   
} else {
	foreach($db->data as &$r) {                         
    	//  dans le TBS utiliser htmlconv=no (déjà fait !)
    	$r['description'] =  mb_convert_encoding($r['description'], 'UTF-8');
    	$r['comment'] = mb_convert_encoding($r['comment'], 'UTF-8');
	}
	$nTbs = $n; 
}
$nowTbs = date("d/m/y à H:i:s");
$TBS->MergeBlock('main', $db->data);

// EoF