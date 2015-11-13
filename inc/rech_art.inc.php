<?php
/**
 * Recherche d'article par sa description
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

//
// Recherche d'article par sa description
//

$articles = array();
$patTbs = '';
$cntTbs = 0;
$msgTbs = false;
if (!empty($_POST['pat'])) {
    $patTbs = trim($_POST['pat']);
    $pat = str_replace("'", "\\'", $patTbs);
    $conditions = "description LIKE '%{$pat}%";
    // comptage préalable
    $sql = "SELECT count(a.idarticle) AS cnt
FROM `article` a
INNER JOIN `depot` d ON a.`depot_iddepot` = d.`iddepot`
INNER JOIN  `deposant` dp ON dp.`iddeposant` = d.`deposant_iddeposant`
WHERE $conditions'
AND d.bourse_idbourse={$_SESSION['bourse']['idbourse']}";
    $ret = $db->query($sql);
    if ($ret < 0) {
        // Erreur SQL
        $msgTbs = 'Erreur SQL ! (voir log)';
    } else {
        $cntTbs = $db->data[0]['cnt'];
        if($cntTbs > 100) {
            $msgTbs = "Trop d'articles répondant à ce critère ({$cntTbs})";
        } elseif ($cntTbs == 0) {
            $msgTbs = "Aucun article répondant à ce critère";
        } else {
            // entre 1 et 100 articles trouvés --> établi la liste
            $sql = "SELECT a.*, dp.nom, dp.`prenom`, dp.`tel`
FROM `article` a
INNER JOIN `depot` d ON a.`depot_iddepot` = d.`iddepot`
INNER JOIN  `deposant` dp ON dp.`iddeposant` = d.`deposant_iddeposant`
WHERE $conditions'
AND d.bourse_idbourse={$_SESSION['bourse']['idbourse']}
ORDER BY d.iddepot, a.idarticle";
            $n = $db->query($sql);
            if($n > 0) {
                foreach ($db->data as $r) {
                    $r['vendu'] = ($r['vente_idvente'])? 'Vendu' : '';
                    $r['description'] = preg_replace('/('.$patTbs.')/i', '<span class="match">$1</span>', $r['description']);
                    $articles[]= $r;
                }
            } else {
                // Erreur SQL
                $msgTbs = 'Erreur SQL ! (voir log)';
            }
        }
    }
}

$nowTbs = date("d/m/y à H:i:s");
$TBS->MergeBlock('articles',$articles);

// EoF
