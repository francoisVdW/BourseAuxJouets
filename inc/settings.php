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
// Listing (pdf) pour restitution / solde : 
// 		1 = g�n�rer une page pazr d�p�t
// 		2 = g�n�rer exemplaire Assoc + exemplaire Deposant 
define('LIST_RETRAIT_NB_EX',2);  // 1 ou 2

// Si on veut produire des PDF pour les bons de d�p�ts et factures
//    --> d�finir PDF_DIR : string path des fichiers PDF
//        NOTE : PDF_DIR doit se terminer par  '/'  
//        NOTE 2 : Sous Unix/Linux veuillez � donner les droits d'acc�s � ce r�pertoire !
// Si on veut utiliser les impression depuis le navigateur
//    --> d�finir PDF_DIR : 0 (ou False)  OU NE PAS DEFINIR (en pla�ant un # en tout d�but de ligne)
define('PDF_DIR', './spool_pdf/');     // crer des pdf dans le "votre bourse"/r�pertoire spool_pdf
#define('PDF_DIR', 0);     // impression directe depuis le navigateur


// EoF
