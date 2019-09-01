<?php 
/*

 Unless otherwise noted, code in this file is copyright (C) 2017 Capitol Technology University
 Kenneth Mayer 
 distlearn@captechu.edu 
 ken.i.mayer@gmail.com
*/

include '../common/int_config.php';
include '../common/adobe.php';
include '../common/sess.php';

$GLOBALS['qs'] = $_SERVER['QUERY_STRING'];
 
if ($_REQUEST['debug']=='yes') $debug = true; 

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE>Adobe connect</TITLE>
<link rel='stylesheet' type='text/css' href='css/style.css'>
<!--[if gte IE 9]>
  <style type='text/css'>
    .gradient {
       filter: none;
    }
  </style>
<![endif]-->
</HEAD>

<BODY>
<div class='banner'>
<img id='bannerLogo' border='0' src='images/home.png' height='50' width='360'>
<div id='log'>
<a href="index.php?<?php print $GLOBALS['qs']; ?>" style="border-right: 1px solid;padding-right: 16px;">Configuration</a>
<a href="service.php?<?php print $GLOBALS['qs']; ?>" style="border-right: 1px solid;padding-right: 16px;">Repopulate</a>
<a href="reports.php?<?php print $GLOBALS['qs']; ?>">Reports</a>
</div>
</div>
<div class='content'>
<?php
	$syspath1 = $_REQUEST['path'];
	$syspath = str_replace("\\", "\\\\", $syspath1);
	$number_of_instances = $_REQUEST['recordings'];
	$from_date = 'NULL';
	if (is_null($_REQUEST['fromdate']) || substr($_REQUEST['fromdate'],0,4)=='0000') {$from_date = 'NULL';} else {$from_date = $_REQUEST['fromdate'];}
	if (isset($_REQUEST['fromdate']) && trim($_REQUEST['fromdate'])=='')  $from_date = 'NULL';
		
	$result = mysql_query("select * from cc_configdetails");
	if ($debug) 
		{Print '<pre>RESULTS: '.$result.'</pre>'; }
	if(mysql_num_rows($result) == 0)
		{
		$query = "INSERT INTO cc_configdetails (root_path, number_of_instances, download_from_date) VALUES ('$syspath' , '$number_of_instances', '$from_date')";
		if ( $from_date=='NULL') $query = "INSERT INTO cc_configdetails (root_path, number_of_instances, download_from_date) VALUES ('$syspath' , '$number_of_instances', NULL)";
		}
	elseif( trim($number_of_instances) != '' && trim($syspath) != '')
		{
		$query = "UPDATE cc_configdetails SET root_path = '$syspath', number_of_instances = '$number_of_instances', download_from_date = '$from_date'";
		if ( $from_date=='NULL') $query = "UPDATE cc_configdetails SET root_path = '$syspath', number_of_instances = '$number_of_instances', download_from_date = NULL";
		}


		print '<h2>QUERY: '.$query.'</h2>'; 
		if(mysql_query($query))
			{
			print "<div class='thankyou_message'>Thank you for updating!</div>";
			//print '<p>mysql_info(): '.mysql_info().'</p>'; 
			//print '<p>mysql_error(): '.mysql_error().'</p>'; 
			//print'<p>Affected rows: '.mysql_affected_rows().'</p>';
			}
		else
			{
			print "<div class='thankyou_message'>ERROR: No update!</div>";
			print '<p>mysql_error(): '.mysql_error().'</p>'; 
			}
?>
</div>
</BODY>	
</HTML>	