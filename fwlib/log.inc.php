<?php
/**
 * Include file : Login des erreur
 *
 * @package bourse
 * @version $Id$
 * @author FVdW
 * @filesource
 */
if (defined('LOG_INC')) return;
define('LOG_INC',1);
if (!defined('LOG_PATH')) define('LOG_PATH', 'log/'); // see const.inc.php

define ('FATAL', 1);
define ('INFO', 2);


/**
 * Fonction bas niveau ecriture dans fichier texte du message + toutes les infos PHP
 *
 * fichier nomé par time-stamp
 * info issues de $GLOBALS
 *
 * @param string $msg le message a afficher
 * @param string $file__ nom du fichier source (cf __FILE__)
 * @param int $line__ n° de ligne dans fichier source (cf__LINE__)
 * @param const $type : INFO/FATAL
 * @param bool $no_var
 * @return string nom du fichier d'erreur
 */
function __log($msg, $file__,$line__, $type, $no_var)
{
   /** preparation du string a envoyer
   */
   $d = date("d/m/Y à H:i:s");
   if (!$no_var ) {
     $a = $GLOBALS;
     unset($a['HTTP_POST_VARS']);   // retire les redondances
     unset($a['HTTP_GET_VARS']);
     unset($a['HTTP_COOKIE_VARS']);
     unset($a['HTTP_SERVER_VARS']);
     unset($a['HTTP_SESSION_VARS']);
     unset($a['HTTP_ENV_VARS']);
     unset($a['_ENV']);
     unset($a['HTTP_POST_FILES']);
     unset($a['FILES']);
     unset($a['REQUEST']);
     $gl = "\n\$GLOBALS: ".print_r($a,1)."\n";
   } else $gl = '';

   $s = "\n--------------------------------------\nDate: $d\n$msg";
   if($line__ !== false) $s.= "\nFichier $file__ : $line__";
   $s.= $gl;

   /** verif presence repetoire log
   */
   if (!is_dir(LOG_PATH)) {
     if(!@mkdir(LOG_PATH,0755) && !is_dir(LOG_PATH))
     exit("__log() : Impossible de creer [".LOG_PATH."/]<br><h3>Le message était :</h3><div align='left'><pre>".htmlentities($s)."</pre></div></body></html>");
   }
  /** Ecriture dans fichier log 
  */
  $sFile = ($type==INFO? "inf":"err").date("md_His").'.txt';
  if (!$handle = fopen(LOG_PATH.$sFile, 'a')) {
   exit("__log() : Impossible d'ouvrir [".LOG_PATH.$sFile."]<br><h3>Le message était :</h3><div align='left'><pre>".htmlentities($s)."</pre></div></body></html>");
  }
  if (fwrite($handle, $s)===false) {
   exit("__log() : Impossible d'écrire dans [".LOG_PATH.$sFile."]<br><h3>Le message était :</h3><div align='left'><pre>".htmlentities($s)."</pre></div></body></html>");
  }  
  fclose($handle);
  return $sFile;
}


/**
 * Log d'un message
 *
 * @see __log()
 *
 * @param string $msg
 * @param string $file
 * @param integer $line
 * @return string : nom du fichier de log
 */
 function logInfo($msg, $file="", $line=false, $no_var=false)
 {
   return __log($msg,$file,$line, INFO, $no_var);
 }
 
/**
 * Effectue le log de l'erreur et exit() avec message std
 *
 * @see __log()
 *
 * @param string $msg
 * @param string $file
 * @param integer $line
 */
function logFatal($msg, $file="", $line=false) {
  flush();
  $sRef =__log($msg,$file,$line,FATAL, false);
  $sRef = basename($sRef,"txt");
  exit ("<html><body style='margin-top:30px;background-color:#C8C8C8;color#000'><p style='text-align:center;color:red;font-weight:bold'>Une erreur a été détectée.</p><p>L'erreur a été enregistrée sous la référence <tt>$sRef</tt></p></body></html>");
}
?>