/*Data for the table `bourse` */

insert  into `bourse`(`idbourse`,`nom_bourse`,`nom_assoc`,`date_deb`,`date_fin`,`marge`,`adresse_deposant`,`nombre_caisse`) 
values (1,'Bourse aux Jouets AVH 2009','Amicale Victor Hugo','2008-11-15','2008-11-18',0.1,'OPTION',4);

INSERT INTO `participant` (`bourse_idbourse`, `nom`, `prenom`, `login`, `pwd`, `may_depot`, `may_caisse`, `may_retrait`, `may_gestion`, `may_admin`) VALUES
 (1, 'VDW', 'Francois', 'francois', md5('francois'), 'T', 'T', 'T', 'T', 'F'),
 (1, 'FABRICE', '', 'fab', md5('fab'), 'T', 'T', 'T', 'T', 'F'),
 (1, 'FARIDA', '', 'farida', md5('farida'), 'F', 'T', 'T', 'F', 'F'),
 (1, 'VIVIANE', '', 'viviane',  md5('viviane'), 'F', 'T', 'T', 'F', 'F'),
 (1, 'GISELE', '', 'gisele', md5('gisele'), 'F', 'T', 'T', 'F', 'F'),
 (1, 'FREDERIQUE', '', 'frederique', md5('frederique'), 'F', 'T', 'T', 'F', 'F'),
 (1, 'SYLVIE D', '', 'sylvied', md5('sylvied'), 'F', 'T', 'T', 'F', 'F'),
 (1, 'CHRISTINE G', '', 'christineg', md5('christineg'), 'T', 'T', 'T', 'F', 'F'),
 (1, 'CHRISTINE P', '', 'christinep', md5('christinep'), 'F', 'T', 'T', 'F', 'F'),
 (1, 'SYLVIE M', '', 'sylviem', md5('sylviem'), 'T', 'T', 'T', 'F', 'F'),
 (1, 'MYRIAM', '', 'myriam',  md5('myriam'), 'T', 'T', 'T', 'F', 'F'),
 (1, 'SOPHIE', '', 'sophie', md5('sophie'), 'T', 'T', 'T', 'F', 'F'),
 (1, 'CECILE', '', 'cecile', md5('cecile'), 'T', 'T', 'T', 'F', 'F'),
 (1, 'VERONIQUE R', '', 'veroniquer',  md5('veroniquer'), 'T', 'T', 'T', 'F', 'F'),
 (1, 'DENISE', '', 'denise', md5('denise'), 'T', 'T', 'T', 'F', 'F'),
 (1, 'VERONIQUE L', '', 'veroniquel',  md5('veroniquel'), 'T', 'T', 'T', 'F', 'F');
