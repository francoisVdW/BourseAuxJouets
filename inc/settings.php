<?php
/**
 * @package: bourse
 * @author : FVdW
 * @version $Revision: 187 $
 *
 */


// Paramètres d'accès à la DB mysql
define('DB_HOST', 'localhost');
define('DB_NAME', 'baj');
define('DB_USER', 'root');
define('DB_PWD', '');

//
// Listing pour restitution / solde : imprimer exemplaire Assoc + exemplaire Deposant (inc/list_retrait.php)
define('LIST_RETRAIT_NB_EX',2);  // 1 ou 2
// Si on veut produire des PDF pour les bons de dépôts et facture 
//    --> définir PDF_DIR : string path des fichiers PDF
//        NOTE : PDF_DIR doit se terminer par  '/'  
//        NOTE 2 : Sous Unix/Linux veuillez à donner les droits d'accès à ce répertoire !
// Si on veut utiliser les impression depuis les pages HTML
//    --> définir PDF_DIR : 0 (ou False)  OU NE PAS DEFINIR (en plaçant un # en tout début de ligne)
define('PDF_DIR', './spool_pdf/');     
?>