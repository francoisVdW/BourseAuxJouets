<?php
/**
 * @package: bourse
 * @author : FVdW
 * @version $Revision: 187 $
 *
 */


// Param�tres d'acc�s � la DB mysql
define('DB_HOST', 'localhost');
define('DB_NAME', 'baj');
define('DB_USER', 'root');
define('DB_PWD', '');

//
// Listing pour restitution / solde : imprimer exemplaire Assoc + exemplaire Deposant (inc/list_retrait.php)
define('LIST_RETRAIT_NB_EX',2);  // 1 ou 2
// Si on veut produire des PDF pour les bons de d�p�ts et facture 
//    --> d�finir PDF_DIR : string path des fichiers PDF
//        NOTE : PDF_DIR doit se terminer par  '/'  
// Si on veut utiliser les impression depuis les pages HTML
//    --> d�finir PDF_DIR : 0 (ou False)  OU NE PAS DEFINIR
define('PDF_DIR', './spool_pdf/');     
?>