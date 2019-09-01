<?php 
/*

 Unless otherwise noted, code in this file is copyright (C) 2017 Capitol Technology University
 Kenneth Mayer 
 distlearn@captechu.edu 
 ken.i.mayer@gmail.com
*/

include '../common/int_config.php';

if (!($_REQUEST['dist']==PHP_pass))
	{
	// If the password variable isn't there go to error message
	header("Location: http://".$_SERVER['HTTP_HOST']."/connect/errors.php");
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE>Adobe Recordings Settings</TITLE>

<link rel="stylesheet" type="text/css" href="css/style.css">
<!--[if gte IE 9]>
  <style type="text/css">
    .gradient {
       filter: none;
    }
  </style>
<![endif]-->
<style type="text/css" >
.hidden {display:none}
</style>
</HEAD>

<BODY>
<?php 
$GLOBALS['qs'] = $_SERVER['QUERY_STRING'];

include '../common/sess.php';
include '../common/adobe.php';

$query = mysql_query("select * from cc_configdetails");
while($info = mysql_fetch_array($query)) 
{ 
	$root_path = $info['root_path'];
	$number_of_instances = $info['number_of_instances'];
	$download_from_date = $info['download_from_date'];
}
if($download_from_date == '0000-00-00')
{
	$download_from_date = '';
}

?>
<div class="banner">
<img id="bannerLogo" border="0" src="images/home.png" height="50" width="360">
<div id="log" style="">
<a href="index.php?<?php print $GLOBALS['qs']; ?>" style="border-right: 1px solid;padding-right: 16px;">Configuration</a>
<a href="service.php?<?php print $GLOBALS['qs']; ?>" style="border-right: 1px solid;padding-right: 16px;">Repopulate</a>
<a href="reports.php?<?php print $GLOBALS['qs']; ?>">Reports</a>
</div>
</div>
<div class="content">

<h3>Configuration parameters</h3>
<table align="center" width="600">
<!-- <tr>
<td width="250"><a href="welcome.php">Search criteria</a></td><td><a href="webservice.php">Webservice update</a></td>
</tr> -->
<tr>
<td ></td>
</tr>
<tr>
<td ></td>
</tr>

<form action="update.php?<?php print $GLOBALS['qs']; ?>" method="POST">
<tr>
<td>Path to save recordings:</td><td> <input type="text" name="path"  value='<?php print $root_path; ?>'></td>
</tr>

<tr>
<td>Number of recordings:</td><td> <input type="text" name="recordings" value='<?php print $number_of_instances; ?>'> Max:5</td>
</tr>
<tr>
<td>From date:</td><td> <input type="text" name="fromdate"  value="<?php print $download_from_date ?>"> Ex:yyyy-mm-dd<br />
Leave blank or with 0000-00-00 to have the program automatically search one week before the current day. </td>
</tr>
<tr>
<td></td><td><input type="submit" value="submit" /></td>
</tr>
</form>
</table>
</div>
</BODY>
</HTML>
