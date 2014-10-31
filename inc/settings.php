<?php
/**
 * @package: bourse
 * @author : FVdW
 * @version $Revision: 187 $
 *
 */


// Paramètres d'accès à la DB mysql
define('DB_HOST', 'localhost');
define('DB_NAME', 'bourse');
define('DB_USER', 'root');
define('DB_PWD', '');

//
// Listing (pdf) pour restitution / solde : 
// 		1 = générer une page pazr dépôt
// 		2 = générer exemplaire Assoc + exemplaire Deposant 
define('LIST_RETRAIT_NB_EX',2);  // 1 ou 2

// Si on veut produire des PDF pour les bons de dépôts et factures
//    --> définir PDF_DIR : string path des fichiers PDF
//        NOTE : PDF_DIR doit se terminer par  '/'  
//        NOTE 2 : Sous Unix/Linux veuillez à donner les droits d'accès à ce répertoire !
// Si on veut utiliser les impression depuis le navigateur
//    --> définir PDF_DIR : 0 (ou False)  OU NE PAS DEFINIR (en plaçant un # en tout début de ligne)
# define('PDF_DIR', './spool_pdf/');     // crer des pdf dans le "votre bourse"/répertoire spool_pdf
define('PDF_DIR', 0);     // impression directe depuis le navigateur


// EoF
