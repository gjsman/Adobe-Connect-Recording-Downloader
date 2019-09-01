<?php 
//ini_set('display_errors', 'On'); 
//error_reporting(E_ALL); 
/*

 Unless otherwise noted, code in this file is copyright (C) 2017 Capitol Technology University
 Kenneth Mayer 
 distlearn@captechu.edu 
 ken.i.mayer@gmail.com
*/
date_default_timezone_set('America/New_York');
session_start();
include '../common/int_config.php';
include '../common/adobe.php';
include '../common/sess.php';

if (isset($_REQUEST['sco']))
	{
	$sco = $_REQUEST['sco'];
	//$date = date('d.m.Y h:i:s'); 
	
	$query = "UPDATE cc_recordings_saved SET status ='D',
	datedownloaded = IF(datedownloaded < '2014-00-00 00:00:00' OR datedownloaded IS NULL, NOW(), datedownloaded) WHERE scoid = '$sco'" ;
	if ( mysql_query($query)) return 1; else return 0;
	}

?>

