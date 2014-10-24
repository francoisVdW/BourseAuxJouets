/*
SQLyog Ultimate v11.01 (64 bit)
MySQL - 5.1.30-community : Database - bourse
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `article` */

DROP TABLE IF EXISTS `article`;

CREATE TABLE `article` (
  `idarticle` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vente_idvente` int(10) unsigned NOT NULL,
  `depot_iddepot` int(10) unsigned NOT NULL,
  `prix_achat` float DEFAULT NULL,
  `prix_vente` float DEFAULT NULL,
  `code_couleur` enum('White','Cyan','Lawngreen','Yellow','Orange','Pink','Orchid') DEFAULT 'White',
  `description` varchar(255) DEFAULT NULL,
  `retour_idretour` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`idarticle`),
  KEY `article_FKIndex1` (`depot_iddepot`),
  KEY `article_FKIndex2` (`vente_idvente`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `bourse` */

DROP TABLE IF EXISTS `bourse`;

CREATE TABLE `bourse` (
  `idbourse` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nom_bourse` varchar(50) DEFAULT NULL,
  `nom_assoc` varchar(50) DEFAULT NULL COMMENT 'Raison Sociale de l''assoc.',
  `adr_assoc` tinytext COMMENT 'Adresse Assoc (pour Factures)',
  `date_deb` datetime DEFAULT NULL,
  `date_fin` datetime DEFAULT NULL,
  `date_cloture_ventes` datetime DEFAULT NULL,
  `marge` float NOT NULL,
  `adresse_deposant` enum('MANDATORY','NONE','OPTION') NOT NULL DEFAULT 'OPTION' COMMENT 'adresse deposant requise ?',
  `nombre_caisse` int(3) unsigned NOT NULL DEFAULT '1',
  `fond_de_caisses` varchar(255) DEFAULT NULL COMMENT 'array no_caisse=mnt; par Ex 1:10;2=20;3=10',
  `msg_fin_depot` tinytext COMMENT 'Texte imprimé en bas des recus de depots',
  PRIMARY KEY (`idbourse`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

/*Table structure for table `deposant` */

DROP TABLE IF EXISTS `deposant`;

CREATE TABLE `deposant` (
  `iddeposant` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(35) DEFAULT NULL,
  `prenom` varchar(25) DEFAULT NULL,
  `tel` varchar(10) DEFAULT NULL,
  `adresse` varchar(25) DEFAULT NULL,
  `adresse2` varchar(25) DEFAULT NULL,
  `cp` char(5) DEFAULT NULL,
  `commune` varchar(25) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `idbourse` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`iddeposant`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `depot` */

DROP TABLE IF EXISTS `depot`;

CREATE TABLE `depot` (
  `iddepot` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bourse_idbourse` int(10) unsigned NOT NULL,
  `idparticipant_depot` int(10) unsigned NOT NULL,
  `deposant_iddeposant` int(10) unsigned NOT NULL,
  `date_depot` datetime DEFAULT NULL,
  `idparticipant_retrait` int(10) unsigned DEFAULT NULL,
  `date_retrait` datetime DEFAULT NULL,
  PRIMARY KEY (`iddepot`),
  KEY `depot_FKIndex1` (`deposant_iddeposant`),
  KEY `depot_FKIndex2` (`bourse_idbourse`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `description` */

DROP TABLE IF EXISTS `description`;

CREATE TABLE `description` (
  `iddescription` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bourse_idbourse` int(10) unsigned NOT NULL,
  `raccourci` char(6) NOT NULL DEFAULT '',
  `description` varchar(50) NOT NULL,
  PRIMARY KEY (`iddescription`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COMMENT='descriptions normalisées';

/*Table structure for table `facture` */

DROP TABLE IF EXISTS `facture`;

CREATE TABLE `facture` (
  `idfacture` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vente_idvente` int(10) unsigned NOT NULL,
  `nom_cli` varchar(35) NOT NULL,
  `adr1` varchar(35) DEFAULT NULL,
  `adr2` varchar(35) DEFAULT NULL,
  `adr3` varchar(35) DEFAULT NULL,
  `adr4` varchar(35) DEFAULT NULL,
  PRIMARY KEY (`idfacture`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Factures : relation 0-1 avec vente';

/*Table structure for table `log_caisse` */

DROP TABLE IF EXISTS `log_caisse`;

CREATE TABLE `log_caisse` (
  `idlog_caisse` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bourse_idbourse` int(10) unsigned NOT NULL,
  `no_caisse` int(10) unsigned NOT NULL,
  `login_date` datetime NOT NULL,
  `logout_date` datetime DEFAULT NULL,
  `participant_idparticipant` int(10) unsigned NOT NULL,
  `ip` varchar(20) NOT NULL,
  `last_idvente` int(10) unsigned DEFAULT NULL,
  `date_last_op` datetime DEFAULT NULL,
  PRIMARY KEY (`idlog_caisse`),
  KEY `bourse_idbourse` (`bourse_idbourse`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Historique des connexions a une caisse';

/*Table structure for table `participant` */

DROP TABLE IF EXISTS `participant`;

CREATE TABLE `participant` (
  `idparticipant` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bourse_idbourse` int(10) unsigned NOT NULL,
  `nom` varchar(35) DEFAULT NULL,
  `prenom` varchar(25) DEFAULT NULL,
  `login` varchar(15) DEFAULT NULL,
  `pwd` varchar(32) DEFAULT NULL,
  `session` varchar(32) DEFAULT NULL,
  `cookie` varchar(32) DEFAULT NULL,
  `ip` char(15) NOT NULL,
  `may_depot` enum('T','F') NOT NULL DEFAULT 'F',
  `may_caisse` enum('T','F') NOT NULL DEFAULT 'F',
  `may_retrait` enum('T','F') NOT NULL DEFAULT 'F',
  `may_gestion` enum('T','F') NOT NULL DEFAULT 'F',
  `may_admin` enum('T','F') NOT NULL DEFAULT 'F',
  PRIMARY KEY (`idparticipant`),
  KEY `participant_FKIndex1` (`bourse_idbourse`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;

/*Table structure for table `retour` */

DROP TABLE IF EXISTS `retour`;

CREATE TABLE `retour` (
  `idretour` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `participant_idparticipant` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idretour`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `vente` */

DROP TABLE IF EXISTS `vente`;

CREATE TABLE `vente` (
  `idvente` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `participant_idparticipant` int(10) unsigned NOT NULL,
  `bourse_idbourse` int(10) NOT NULL,
  `date_vente` datetime DEFAULT NULL,
  `mnt_esp` float DEFAULT NULL,
  `mnt_chq` float DEFAULT NULL,
  `mnt_autr` float DEFAULT NULL,
  `no_caisse` int(3) unsigned NOT NULL,
  PRIMARY KEY (`idvente`),
  KEY `vente_FKIndex1` (`participant_idparticipant`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/* Function  structure for function  `etat_caisse` */

/*!50003 DROP FUNCTION IF EXISTS `etat_caisse` */;
DELIMITER $$

/*!50003 CREATE FUNCTION `etat_caisse`(p_id_bourse INT, p_no_caisse INT) RETURNS varchar(50) CHARSET latin1
    DETERMINISTIC
    COMMENT 'ctrl caisse ret s=Etat;u=user_id;c=no_caisse;v=vente_id;l=login'
BEGIN
	declare usrid int;
	DECLARE venteid INT;
	declare vlogin varchar(30);
	DECLARE vcloture DATETIME;
	SELECT date_cloture_ventes into vcloture from bourse WHERE idbourse = p_id_bourse;
	IF vcloture IS NOT NULL THEN
		return CONCAT('s=cloture;c=',p_no_caisse);
	end if;
	SELECT idvente, participant.idparticipant, participant.login INTO venteid, usrid, vlogin 
		FROM vente 
		LEFT JOIN participant ON participant_idparticipant = idparticipant
		WHERE vente.bourse_idbourse=p_id_bourse 
		AND date_vente IS NULL 
		AND no_caisse = p_no_caisse
		ORDER BY idvente DESC 
		LIMIT 1;
	IF venteid IS NOT NULL THEN
		RETURN CONCAT('s=vente;c=',p_no_caisse,';v=',venteid, ';u=',usrid,';l=', IFNULL(vlogin, ''));
	END IF;	
	SELECT log_caisse.participant_idparticipant, participant.login  INTO usrid, vlogin 
		FROM log_caisse 
		left join participant on participant_idparticipant = idparticipant
		WHERE log_caisse.bourse_idbourse=p_id_bourse 
		AND logout_date IS NULL 
		AND no_caisse= p_no_caisse;
	if usrid is NOT null then
		return CONCAT('s=ouverte;c=',p_no_caisse,';u=',usrid,';l=', IFNULL(vlogin, ''));
	end if;	
	return concat('s=fermee;c=',p_no_caisse);
    END */$$
DELIMITER ;

/* Function  structure for function  `get_fond` */

/*!50003 DROP FUNCTION IF EXISTS `get_fond` */;
DELIMITER $$

/*!50003 CREATE FUNCTION `get_fond`(p_idbourse int, p_nocaisse int) RETURNS float
    READS SQL DATA
    DETERMINISTIC
BEGIN    
		declare s varchar(255);
		declare item varchar(255);
		declare no_caisse varchar(255);		
		declare fond float default 0;
		declare done int DEFAULT 0;
		DECLARE CONTINUE HANDLER FOR NOT FOUND RETURN -1; 
		
		select fond_de_caisses into s from bourse where idbourse = p_idbourse;
		
		
		while done = 0 do
			set item = SUBSTRING_INDEX(s, ';', 1);
			/* no caisse ? */
			SET no_caisse = trim(SUBSTRING_INDEX(item, '=', 1))+0;
			if(no_caisse = p_nocaisse) then
				set fond = trim(SUBSTRING_INDEX(item, '=', -1))+0;
				return fond;
			end if;
			SET s = SUBSTRING(s, LENGTH(item)+2);
			if s = '' then
				SET done = 1;
			end if;
		end while;
		return 0;	
    END */$$
DELIMITER ;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
