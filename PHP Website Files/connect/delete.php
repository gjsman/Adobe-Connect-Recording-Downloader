<?php 
/*

 Unless otherwise noted, code in this file is copyright (C) 2017 Capitol Technology University
 Kenneth Mayer 
 distlearn@captechu.edu 
 ken.i.mayer@gmail.com
*/
include '../common/int_config.php';

session_start();
$GLOBALS['qs'] = $_SERVER['QUERY_STRING'];

// this clears 'recs' and 'recordtally' from the query string--otherwise it will get messy
if(isset($_REQUEST['recs'])) $GLOBALS['qs'] = str_replace("recs=".$_REQUEST["recs"],"",$GLOBALS['qs']);
if(isset($_REQUEST['recordtally'])) $GLOBALS['qs'] = str_replace("recordtally=".$_REQUEST["recordtally"],"",$GLOBALS['qs']);
$debug=false;
if (isset($_REQUEST['debug'])) $debug=true;

include '../common/adobe.php';
include '../common/sess.php';

	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Adobe Recordings Deletion</title>
</head>

<body>
<?php

$acc = new AdobeConnectClient();

$dur_const = true; // debug for changing duration
if ($debug) print("<h2>DEBUGGING $dur_const </h2>");

function getFolderID($sco)
	{
	global $acc;
	$folder_scoData =  $acc->getFolderName($sco);
	return $folder_scoData['@attributes']['folder-id'];
	
	}
	
function fix_duration($sco)
	{
	global $acc;
	$new_duration=0;
	$duration_Data = $acc->makeRequest('sco-info&sco-id='.$sco);
	if ($debug) print("<p>DEBUGGING SCO: $sco </p>");
	if ($debug) print("<p>DEBUGGING duration_Data (array) </p>");
	if ($debug) print(var_dump($duration_Data));
	
	if(isset($duration_Data['sco']['af-recording-duration']))
		{
		$new_duration = $duration_Data['sco']['af-recording-duration'];
		$hr = intval($new_duration);
		$min = substr($new_duration,3,2);
		if ($debug) print("<p>AF-Recording: HR: $hr MIN: $min </p>");
		}

	if(isset($duration_Data['sco']['recording-edited-duration']))
		{
		$new_duration = $duration_Data['sco']['recording-edited-duration'];
		$hr = intval($new_duration);
		$min = substr($new_duration,3,2);
		if ($debug) print("<p>EDITED: HR: $hr MIN: $min </p>");

		}
	
	if ($new_duration !== 0) 
		{
		$new_duration = abs($hr*3600+intval($min)*60);
		}
	return $new_duration;
	
	}
	

$recordTally = 0;
$unDeletes = 0;
$errors=0;

	
//$query = 'UPDATE SET cc_recordings_saved status ='.$query.',datedownloaded =DATE("1941-12-07 07:00:00")  WHERE 
$table1 = 'UPDATE cc_recordings_saved SET status ="DEL", datedownloaded =NULL WHERE scoid = ';
$table2 = 'UPDATE cc_recordings_saved SET status ="P", datedownloaded =NULL WHERE scoid = ';
$new_duration = '';

foreach($_POST as $key => $value) {
	//Print "<p>key: ".$key."</p>";
	$test = "".strpos($key,'DEL');
	//Print "<pre>".$test."</pre>";
	if (strpos($key,"DEL")===0)
		{
		$query1 = $table1.chr(34).$value.chr(34);
		$data = mysql_query($query1);
		//print "<p>query1: ".$query1." data: ".$data."</p>\r\n";
		if(!empty($data)) 
			{$recordTally++;
			}
		else
			{$errors++;
			print "<p>".mysql_error()." : ".$query1."</p>\r\n";
			}
		
		//Print "<p>recordTally: ".$recordTally."</p>";
		}
	if (strpos($key,"UNDEL")===0)
		{
		$new_duration= fix_duration($value);
		if ($new_duration > 0)
			{
			$dur_const = false;
			$query = "UPDATE cc_recordings_in_ac SET duration ='$new_duration' WHERE scoid ='$value'";
			if (! mysql_query($query))
				{
				$errors++;
				print "<p>".mysql_error()." : ".$query."</p>\r\n";
				}
			}
		$query1 = $table2.chr(34).$value.chr(34);
		$data = mysql_query($query1);
		//print "<p>query1: ".$query1." data: ".$data."</p>\r\n";
		if(!empty($data)) 
			{$unDeletes++;
			}
		else
			{$errors++;
			print "<p>".mysql_error()." : ".$query1."</p>\r\n";
			}
		
		//print "<p>recordTally: ".$recordTally."</p>";
		}

	}
	
if ($errors==0 && ! $debug)
	{
	$server_dir = $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/';
	
	/* The header() function sends a HTTP message 
	   The 303 code asks the server to use GET
	   when redirecting to another page */

	header('HTTP/1.1 303 See Other');

   $next_page = 'reports.php';
   $_SESSION['recTally']=$recordTally;
    $_SESSION['unDel']=$unDeletes;
   // This message asks the server to redirect to another page
   header('Location: https://' . $server_dir . $next_page . '?'. $GLOBALS['qs']);
	}

?>
<form action="reports.php?<?php print $GLOBALS['qs']; ?>" method="post" >
<p><input type=submit value="Return to reports.php" /></p></form>
</body>
</html>