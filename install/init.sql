insert  into `bourse`(`idbourse`,`nom_bourse`,`nom_assoc`,`date_deb`,`date_fin`,`date_cloture_ventes`,`marge`,`adresse_deposant`,`nombre_caisse`,`fond_de_caisses`,`msg_fin_depot`) 
 values (1,'Bourse aux Jouets ','Nom assoc',NULL,NULL,NULL,0.1,'OPTION',4,'','Pour information...');

INSERT INTO `participant` (`bourse_idbourse`, `nom`, `prenom`, `login`, `pwd`, `may_depot`, `may_caisse`, `may_retrait`, `may_gestion`, `may_admin`) VALUES
 (1, 'ADMIN', '-', 'admin', md5('baj'), 'T', 'T', 'T', 'T', 'T');
