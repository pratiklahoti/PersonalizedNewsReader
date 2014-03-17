<?php
	
	ob_start();
	require_once('config.php');
	require_once('index.php');
	//require_once('index.php');
	//$facebook->setSession(NULL);
	$facebook->destroySession();
	//session_destroy();
	header('Location: http://localhost/pn/');

?>

