DELIMITER $$

DROP PROCEDURE IF EXISTS `7trav_test`.`Get_Ware`$$

CREATE DEFINER=`mysql3`@`%` PROCEDURE `Get_Ware`()
BEGIN

-- SELECT ID, Name	FROM Ware WHERE	FlagNew = 'да';
SELECT ID, Name	FROM Ware WHERE	FlagJob = 'да';

SELECT COUNT(*) FROM Ware;

 END$$

DELIMITER ;