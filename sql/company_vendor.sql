SELECT
	Company_ID, Vendor_ID, COUNT(*)
FROM Company_Vendor
GROUP BY
	1, 2
ORDER BY
	3 DESC;

SELECT
	Company_ID, Vendor_ID
FROM Company_Vendor;

SELECT COUNT(*) FROM Company_Vendor;

SELECT * FROM Company;