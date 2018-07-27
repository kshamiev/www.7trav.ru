DELIMITER $$

DROP PROCEDURE IF EXISTS `null_redir`.`GeoIP_Init`$$

CREATE PROCEDURE `null_redir`.`GeoIP_Init`()
    /*LANGUAGE SQL
    | [NOT] DETERMINISTIC
    | { CONTAINS SQL | NO SQL | READS SQL DATA | MODIFIES SQL DATA }
    | SQL SECURITY { DEFINER | INVOKER }
    | COMMENT 'string'*/
    BEGIN

-- load country
TRUNCATE TABLE _GeoIP_CSV;
ALTER TABLE _GeoIP_CSV DROP INDEX Kod;
LOAD DATA INFILE '/tmp/GeoIPCountryWhois.csv'
    INTO TABLE _GeoIP_CSV
    FIELDS
      TERMINATED BY ','
      ENCLOSED BY '"'
      ESCAPED BY '\\'
    LINES TERMINATED BY '\n';
ALTER TABLE _GeoIP_CSV ADD INDEX (Kod, Name);

-- init country
INSERT IGNORE INTO GeoIPCountry (Kod, Name) SELECT DISTINCT Kod, Name FROM _GeoIP_CSV;

-- init country validation ipnum
TRUNCATE TABLE _GeoIP_IP;
INSERT INTO _GeoIP_IP
SELECT
  csv.start, csv.end, cc.ID
FROM _GeoIP_CSV as csv
  INNER JOIN GeoIPCountry as cc ON csv.Kod = cc.Kod AND csv.Name = cc.Name;

    END$$

DELIMITER ;