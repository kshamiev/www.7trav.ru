UPDATE StatHostHit SET Name = 'www.7trav.ru';

SELECT COUNT(*) FROM StatHostHit;

SELECT * FROM StatHostHit WHERE OS LIKE '%Mozilla%' AND OS NOT LIKE '%MSIE%' LIMIT 0, 50;
SELECT COUNT(*) FROM StatHostHit WHERE OS LIKE '%Opera%';
SELECT COUNT(*) FROM StatHostHit WHERE OS LIKE '%MSIE%';
SELECT COUNT(*) FROM StatHostHit WHERE OS LIKE '%MSIE 6%';
SELECT COUNT(*) FROM StatHostHit WHERE OS LIKE '%MSIE 7%';
SELECT COUNT(*) FROM StatHostHit WHERE OS LIKE '%MSIE 8%';
SELECT * FROM StatHostHit WHERE OS LIKE '%MSIE 6%' LIMIT 0, 50;
SELECT * FROM StatHostHit WHERE OS NOT LIKE '%Opera%' AND OS NOT LIKE '%Mozilla%';
