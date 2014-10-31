/*
 * $Revision: 185 $
 */
DROP FUNCTION IF EXISTS etat_caisse;
DELIMITER $$

CREATE FUNCTION `etat_caisse`(p_id_bourse INT, p_no_caisse INT)
    RETURNS VARCHAR(50) DETERMINISTIC
    COMMENT 'ctrl caisse ret s=Etat;u=user_id;c=no_caisse;v=vente_id;l=login'
    BEGIN
	DECLARE usrid INT;
	DECLARE venteid INT;
	DECLARE vlogin VARCHAR(30);
	DECLARE vcloture DATETIME;
	SELECT date_cloture_ventes INTO vcloture FROM bourse WHERE idbourse = p_id_bourse;
	IF vcloture IS NOT NULL THEN
		RETURN CONCAT('s=cloture;c=',p_no_caisse);
	END IF;
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
		LEFT JOIN participant ON participant_idparticipant = idparticipant
		WHERE log_caisse.bourse_idbourse=p_id_bourse 
		AND logout_date IS NULL 
		AND no_caisse= p_no_caisse;
	IF usrid IS NOT NULL THEN
		RETURN CONCAT('s=ouverte;c=',p_no_caisse,';u=',usrid,';l=', IFNULL(vlogin, ''));
	END IF;	
	RETURN CONCAT('s=fermee;c=',p_no_caisse);
    END$$
DELIMITER ;
SHOW WARNINGS;