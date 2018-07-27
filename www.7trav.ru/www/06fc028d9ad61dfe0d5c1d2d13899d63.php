<?php 
     define('_SAPE_USER', '06fc028d9ad61dfe0d5c1d2d13899d63');
     require_once($_SERVER['DOCUMENT_ROOT'].'/'._SAPE_USER.'/sape.php'); 
     $sape_articles = new SAPE_articles();
     echo $sape_articles->process_request();
?>
