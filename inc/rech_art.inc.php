<?php
/**
 * Recherche d'article par sa description
 *
 * @package: bourse
 * @author : FVdW
 * @version $Id$
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
$patTbs =  $artTbs = $depTbs = '';
$cntTbs = 0;
$msgTbs = false;
if (!empty($_POST['post'])) {
	if (!empty($_POST['pat'])) {
		$patTbs = trim($_POST['pat']);    
		$pat = str_replace("'", "\\'", $patTbs);
	} else {
		$patTbs = '';
		$pat = false;    
	}
	if (!empty($_POST['dep'])) {
		$depTbs = trim($_POST['dep']);
		if (!is_numeric($depTbs)) {
			$msgTbs = 'Le numéro de dépot doit être numérique';
			$id_depot = false;
		} else {
			$id_depot = $depTbs;
		}
	} else {
		$depTbs = '';
		$id_depot = false;    
	}
	if (!empty($_POST['art'])) {
		$artTbs = trim($_POST['art']);    
		if (!is_numeric($artTbs)) {
			$msgTbs = 'Le numéro de l\'article doit être numérique';
			$id_article = false;
		} else {
			$id_article = $artTbs;
		}
	} else {
		$artTbs = '';
		$id_article = false;    
	}
    if (!$msgTbs) {
		if (!$pat && !$id_depot && !$id_article) {
			$msgTbs = 'Il est nécessaire de valoriser un des 3 critères';
		} else {   
			$cnds = array();
			if ($pat) $cnds[] = "a.description LIKE '%{$pat}%'";
			if ($id_depot) $cnds[] = "a.depot_iddepot = $id_depot";
			if ($id_article) $cnds[] = "a.idarticle = $id_article";
			$cnds[] = "d.bourse_idbourse={$_SESSION['bourse']['idbourse']}";
			$conditions = join(' AND ', $cnds);
			// comptage préalable
			$sql = "SELECT count(a.idarticle) AS cnt
		FROM `article` a
		INNER JOIN `depot` d ON a.`depot_iddepot` = d.`iddepot`
		INNER JOIN  `deposant` dp ON dp.`iddeposant` = d.`deposant_iddeposant`
		WHERE $conditions";
			$ret = $db->query($sql);
			if ($ret < 0) {
				// Erreur SQL
				$msgTbs = 'Erreur SQL ! (voir log)';
			} else {
				$cntTbs = $db->data[0]['cnt'];
				if($cntTbs > 100 && !$id_depot) {
					$msgTbs = "Trop d'articles répondant à ce critère ({$cntTbs})";
				} elseif ($cntTbs == 0) {
					$msgTbs = "Aucun article répondant à ce critère";
				} else {
					// entre 1 et 100 articles trouvés --> établi la liste
					$sql = "SELECT a.*, dp.nom, dp.`prenom`, dp.`tel`
		FROM `article` a
		INNER JOIN `depot` d ON a.`depot_iddepot` = d.`iddepot`
		INNER JOIN  `deposant` dp ON dp.`iddeposant` = d.`deposant_iddeposant`
		WHERE $conditions
		ORDER BY d.iddepot, a.idarticle";
					$n = $db->query($sql);
					if($n > 0) {
						foreach ($db->data as $r) {
							$r['vendu'] = ($r['vente_idvente'])? 'Vendu' : '';
							$r['desc_sortable'] = strtolower($r['description']);
							$r['description'] = preg_replace('/('.$patTbs.')/i', '<span class="match">$1</span>', $r['description']);
							$r['hh'] = ($r['happy_hour'])? true : null;
							$articles[]= $r;
						}
					} else {
						// Erreur SQL
						$msgTbs = 'Erreur SQL ! (voir log)';
					}
				}
			}
		}
	}
}

$nowTbs = date("d/m/y à H:i:s");
$TBS->MergeBlock('articles',$articles);

// EoF
