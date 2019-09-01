<?php 
//ini_set('display_errors', 'On'); 
//error_reporting(E_ALL); 
/*

 Unless otherwise noted, code in this file is copyright (C) 2017 Capitol Technology University
 Kenneth Mayer 
 distlearn@captechu.edu 
 ken.i.mayer@gmail.com
*/
session_start();
include '../common/int_config.php';
include '../common/adobe.php';
include '../common/sess.php';
date_default_timezone_set(ZONE);

$message='';
if (isset($_SESSION['recs']) && intval($_SESSION['recs'])>0 ) 
	{
    $message.='Success! '.$_SESSION['recs']. ' records added!';
    $_SESSION['recs']=0;
    }
	
if ( isset($_SESSION['recTally']) && $_SESSION['recTally'] >0 ) 
	{
    $message.='Success! '.$_SESSION['recTally'].' records deleted!';
    $_SESSION['recTally']=NULL;
    }
if ( isset($_SESSION['unDel']) && $_SESSION['unDel'] >0 ) 
	{
    $message.=$_SESSION['unDel'].' records undeleted!';
    $_SESSION['unDel']=NULL;
    }
	
function logError($text)
	{
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	$ip = $_SERVER['REMOTE_ADDR']; //-- '.$_SERVER['REMOTE_HOST'];
	$text= htmlentities($text);
	$query = "INSERT INTO errors (useragent,ip,error) VALUES ('$user_agent','$ip','$text')";
	if(! mysql_query($query)) 
		{
		print('<p>LOG FILE failure: '. mysql_error()."</p>\n");
		}
	}

function my_filesize($filename) 
	{
	try
		{
		$fp =fopen($filename,'rb');
		}
	 catch (exception $e) {
		 return 0;
	 	}
    $return = 0;
    if (is_resource($fp)) 
		{
      	if (PHP_INT_SIZE < 8) 
			{
			// 32bit
			if (0 === fseek($fp, 0, SEEK_END)) 
				{
				$return = 0.0;
				$step = 0x7FFFFFFF;
			  	while ($step > 0) 
					{
					if (0 === fseek($fp, - $step, SEEK_CUR)) 
						{
						$return += floatval($step);
						} 
					else 
						{
						$step >>= 1;
						}
					 }
        		}
     		 } 
		elseif (0 === fseek($fp, 0, SEEK_END)) {
       		 // 64bit
        	$return = ftell($fp);
     		 }
    	}
	if ($return===0 && is_resource($fp)) $return=RealFileSize($fp);
	fclose($fp);
    return $return;
  }
 
function RealFileSize($fp)
	{
    $pos = 0;
    $size = 1073741824;
    fseek($fp, 0, SEEK_SET);
    while ($size > 1)
    {
        fseek($fp, $size, SEEK_CUR);

        if (fgetc($fp) === false)
        {
            fseek($fp, -$size, SEEK_CUR);
            $size = (int)($size / 2);
        }
        else
        {
            fseek($fp, -1, SEEK_CUR);
            $pos += $size;
        }
    }

    while (fgetc($fp) !== false)  $pos++;

    return $pos;
	}
function testFile($theUrl,$duration)
	{
	$ret = array('name'=>'', 'size'=>0, 'ratio'=>0, 'button'=>'','type'=>'');
	$testUrl = $theUrl;
	if (file_exists($theUrl)) 
		{ 
		$ret['name']=$theUrl;
		}
	else 
		{
		if (strpos($theUrl,"'")>0 )
			{
			$testUrl = str_replace("'","%27",$theUrl);
			if (file_exists($testUrl)) 
				{
				$ret['name']=$testUrl;
				}
			}
		if ($ret['name']=='')
			if( strpos($testUrl,'.flv')>0)
				{
				$testUrl = str_replace('.flv','.mp4',$testUrl);
				}
			else
				{
				$testUrl = str_replace('.mp4','.flv',$testUrl);
				}
		}
	if (file_exists($testUrl)) 
		{
		$ret['name']= $testUrl;
		if (strpos($ret['name'],'.flv')>0)
			{
			$ret['type'] = 'FLV ';
			}
		if (strpos($ret['name'],'.mp4')>0)
			{
			$ret['type']='MP4 ';
			}
		$ret['size']= my_filesize($testUrl);
		$kps = '';
		if ($duration>0) 
			{$ret['ratio'] = $ret['size']/(1000 * $duration);
			if ($duration>0) $kps = number_format($ret['ratio'],1) .'K/sec';
			}
		$ret['button'] = intval($ret['size']/1000000) . 'MB '.$ret['type']. $kps;
		}

	return $ret;
	}
	
function fileServ($theUrl)
	{
	if (file_exists($theUrl))
		{
		
		// New code from http://www.media-division.com/php-download-script-with-resume-option/
		// * Copyright 2012 Armand Niculescu - media-division.com
		
		$file_size  = my_filesize($theUrl);
		try {
			$file = fopen($theUrl,'rb');
			} catch (Exception $e) {
			// get rid of output buffer
			 ob_clean();
			die();
			exit;

			}
		if ($file)
			{
			// set the headers, prevent caching
			header("Pragma: public");
			header("Expires: -1");
			header("Cache-Control: public, must-revalidate, post-check=0, pre-check=0");
			$suffix =  substr(strrpos($theUrl,'.'));
			$baseFile = basename($theUrl);
			if (strpos($baseFile,'.'.$suffix)<0) $baseFile .= '.'.$suffix;
			//header("Content-Type: application/octet-stream");
			if ($suffix=='mp4')
				{
				header("Content-Type: video/mp4");
				}
			else 
				if ($suffix=='flv')
					{
					header("Content-Type: application/flv");
					}
				else
					if ($suffix=='log') header("Content-Type: text/plain");

				
			header("Content-Disposition: attachment; filename=\"$baseFile\"");
			header("Accept-Ranges: 0-$file_size");
			header("Content-Length: $file_size");
			header("Content-Transfer-Encoding: binary\n");
			ob_clean();
			//check if http_range is sent by browser (or download manager)
			if(isset($_SERVER['HTTP_RANGE']))
				{
				list($size_unit, $range_orig) = explode('=', $_SERVER['HTTP_RANGE'], 2);
				if ($size_unit == 'bytes')
					{
					//multiple ranges could be specified at the same time, but for simplicity only serve the first range
					//http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt
					list($range, $extra_ranges) = explode(',', $range_orig, 2);
					}
				else
					{
					$range = '';
					header('HTTP/1.1 416 Requested Range Not Satisfiable');
					exit;
					}
				}
			else
				{
				$range = '';
				}
		 
			//figure out download piece from range (if set)
			list($seek_start, $seek_end) = explode('-', $range, 2);
	 
			//set start and end based on range (if set), else set defaults
			//also check for invalid ranges.
			$seek_end   = (empty($seek_end)) ? ($file_size - 1) : min(abs(intval($seek_end)),($file_size - 1));
			$seek_start = (empty($seek_start) || $seek_end < abs(intval($seek_start))) ? 0 : max(abs(intval($seek_start)),0);
	 
			//Only send partial content header if downloading a piece of the file (IE workaround)
			if ($seek_start > 0 || $seek_end < ($file_size - 1))
				{
				header('HTTP/1.1 206 Partial Content');
				header('Content-Range: bytes '.$seek_start.'-'.$seek_end.'/'.$file_size);
				header('Content-Length: '.($seek_end - $seek_start + 1));
				}
			else
				header("Content-Length: $file_size");
		 
			header('Accept-Ranges: bytes');
		 
			set_time_limit(0);
			fseek($file, $seek_start);			
			while(!feof($file)) 
				{
				print(fread($file, 1024*8));
				ob_flush();
				flush();
				if (connection_status()!=0) 
					{
					fclose($file);
					exit;
					}			
				}
	 
			// file save was a success
			fclose($file);
			return true;
			}
		else 
			{
			// file couldn't be opened
			logError('theUrl: '.$theUrl.' could not be opened.');
			header("HTTP/1.0 500 Internal Server Error");
			return false;
			}
		}
	return file_exists($theUrl);
	}
	
if (isset($_POST['rec_serv']) && strlen($_POST['rec_serv'])>5)
	{
	// If the recserv link is clicked, serve up a file and exit
	if ($_POST['rec_serv']=='ERRORFILE')
		{
		$success = fileServ(INST_ERROR .'SikuliErrors.log');
		if (! $success) $message="Failed to find Errors file!";
		}
	else
		{
		$file_link = $_SESSION["SCO".$_POST['rec_serv']];
		$_POST['rec_serv'] = NULL; // clear for clicking another button
		$fname = basename($file_link);
		$testUrl = $file_link;
		$success=false;
		if (strpos($fname,"'")>0 )
			{
			$fnameFix = str_replace("'","%27",$fname);
			$testUrl = str_replace($fname,$fnameFix,$file_link);
			$success = fileServ($testUrl);
	
			}
		else
			{$success = fileServ($file_link);
			$testUrl = $file_link;
			}
		if (! $success)
			{
			if (strpos($testUrl,'.flv')>0)
				{
				$testUrl = str_replace('.flv','.mp4',$testUrl);
				}
			else
				{
				$testUrl = str_replace('.mp4','.flv',$testUrl);
				}
			$success = fileServ($testUrl);	
			}
			
		if ($success) exit; else $message="Failed to find recording file!";
		}
	}
	
session_destroy();
session_start();
$_SESSION['standard']=0;
if (isset($_REQUEST['standard'])) $_SESSION['standard'] = $_REQUEST['standard'];

if (isset($_POST['reset']))
	{
	$query = "UPDATE cc_recordings_saved SET status ='P' WHERE status = 'PROB'" ;
	if( mysql_query($query)) $message="Problematic recordings reset!";
	}
print '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>';
print '<!--- PHP Version: '.phpversion(). '-->';
print '
<meta charset="utf-8"/>
<title>Adobe Recordings Checker</title>
<link href="css/reset.css" rel="stylesheet" type="text/css"/>
<link href="css/960.css" rel="stylesheet" type="text/css"/>
<link href="css/coolMenu.css" rel="stylesheet" type="text/css" media="screen"/>
<link rel="stylesheet" type="text/css" href="css/style.css">
<link href="css/demo_table.css" rel="stylesheet" type="text/css" />
<link href="css/demo_page.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" ></script>

<script type="text/javascript" language="javascript" src="js/jquery.dataTables.js"></script>
<script type="text/javascript" src="js/jquery-datepicker.js"></script>
<script language="JavaScript" type="text/javascript" src="js/jquery.corner.js" ></script>

<style type="text/css" title="currentStyle" >
	.delsubmit {font-weight:bold}
	.success {background-color:green; color:white;}
	.deleted  {background-color:#DDA4B9 !important; border-bottom:gray medium solid !important;}
	.problem  {background-color:#FF3 !important; color:#C00 !important;}
	.looksGood {background-color:green;}
	.trouble {background-color:#FF3 !important; color:#C00 !important; }
	#statusMess {padding:.5em;font-size:80%;}
	.flvsub:hover {background-color:green; color:white;}
	.mark_done {background-color:green; color:white;}
	.mark_done:hover {background-color:lime; color:black;}

</style>
<script type="text/javascript">
		(function($) {
		/*
		 * Function: fnGetColumnData
		 * Purpose:  Return an array of table values from a particular column.
		 * Returns:  array string: 1d data array 
		 * Inputs:   object:oSettings - dataTable settings object. This is always the last argument past to the function
		 *           int:iColumn - the id of the column to extract the data from
		 *           bool:bUnique - optional - if set to false duplicated values are not filtered out
		 *           bool:bFiltered - optional - if set to false all the table data is used (not only the filtered)
		 *           bool:bIgnoreEmpty - optional - if set to false empty values are not filtered from the result array
		 * Author:   Benedikt Forchhammer <b.forchhammer /AT\ mind2.de>
		 */
		$.fn.dataTableExt.oApi.fnGetColumnData = function ( oSettings, iColumn, bUnique, bFiltered, bIgnoreEmpty ) {
		    // check that we have a column id
		    if ( typeof iColumn == "undefined" ) return new Array();
		     
		    // by default we only wany unique data
		    if ( typeof bUnique == "undefined" ) bUnique = true;
		     
		    // by default we do want to only look at filtered data
		    if ( typeof bFiltered == "undefined" ) bFiltered = true;
		     
		    // by default we do not wany to include empty values
		    if ( typeof bIgnoreEmpty == "undefined" ) bIgnoreEmpty = true;
		     
		    // list of rows which we are going to loop through
		    var aiRows;
		     
		    // use only filtered rows
		    if (bFiltered == true) aiRows = oSettings.aiDisplay; 
		    // use all rows
		    else aiRows = oSettings.aiDisplayMaster; // all row numbers
		 
		    // set up data array    
		    var asResultData = new Array();
		     
		    for (var i=0,c=aiRows.length; i<c; i++) {
		        iRow = aiRows[i];
		        var aData = this.fnGetData(iRow);
		        var sValue = aData[iColumn];
		         
		        // ignore empty values?
		        if (bIgnoreEmpty == true && sValue.length == 0) continue;
		 
		        // ignore unique values?
		        else if (bUnique == true && jQuery.inArray(sValue, asResultData) > -1) continue;
		         
		        // else push the value onto the result data array
		        else asResultData.push(sValue);
		    }
		     
		    return asResultData;
		}}(jQuery));
		 
		 
		function fnCreateSelect( aData )
		{
		    var r=';
			print "'<select><option value=";
			Print '"" ></option>';
			print "', i, iLen=aData.length;
		    for ( i=0 ; i<iLen ; i++ )
		    {
		        r += '<option value=".'"'."'+aData[i]+'".'"'.">'+aData[i]+'</option>';
		    }
		    return r+'</select>';
		}
		 
		 function fileServ(daSco)
		{
			//alert('SCO: '+daSco);
			$('#rec_serv').val(daSco);
			$('#flvform').submit();
		}	
		
		function markDone(daSco)
			{
			var r = confirm('Mark this recording as done? This will take it off the pending list. If there is a problem, you can delete it and then undelete it to put it back in the pending list.');
			if (r == true) 
				{
				$.get('markDone.php?sco='+daSco, function() {
					//clear the button and mark row as clear
					var row = $(this).parent('tr');
					row.attr('title','Success! Status is now D');
					row.find('td.problem').removeClass('problem');
					row.remove('input.mark_done');
					location.reload(true);
					});
				
				}
			}
			
		$(document).ready(function() {
			
		    /* Initialise the DataTable */
		    var oTable = $('#example').dataTable( {";
			Print '
				"aaSorting": [[ 2, "desc" ]],
		        "oLanguage": {
		            "sSearch": "Search all columns:"
		        },
				 "iDisplayLength": 50,
				 "aLengthMenu": [25, 50, 100, 250]
			
		    } );
		     
		    /* Add a select menu for each TH element in the table footer */
		    $("tfoot th").each( function ( i ) {
		        this.innerHTML = fnCreateSelect( oTable.fnGetColumnData(i) );
		        $("select", this).change( function () {
		            oTable.fnFilter( $(this).val(), i );
		        } );
		    } );
		} );
</script>
<!--[if gte IE 9]>
  <style type="text/css">
    .gradient {
       filter: none;
    }
  </style>
<![endif]-->


</head>
<body>

<div class="banner">
<img id="bannerLogo" border="0" src="images/home.png" height="50" width="360">
<div id="log" >
<a href="index.php" style="border-right: 1px solid;padding-right: 16px;">Configuration</a>
<a href="service.php" style="border-right: 1px solid;padding-right: 16px;">Repopulate</a>
<a href="reports.php">Reports</a>
</div>
</div>
<div class="content">';






$result =  mysql_query("select * from cc_configdetails");
$status_class = '"looksGood"';
$dateNow = new DateTime('NOW');
while($info1 = mysql_fetch_array($result)) 
{ 
	$root_path = $info1['root_path'];
	$status_check = $info1['status_check'];
	$dateStatus = new DateTime($status_check);
	$dateChecker = $dateStatus;
	date_add($dateChecker, date_interval_create_from_date_string('3 hours'));
	if ($dateNow > $dateChecker) $status_class ='"trouble"';
}


$prev_day = date("Y-m-d H:i:s", strtotime(date("Y-m-d") .' -1 day'));//date('Y-m-d', strtotime(date("Y-m-d") .' -1 day'));
print '<div id="filter"><form method="get" style="margin-top: .5em;padding-left: .4em;float:left;" >Show: <select name = "standard" id="stnd" onChange="form.submit();"><option value=0>Undeleted</option>
		<option value=1>Pending</option>
		<option value=2>Done</option>
		<option value=3>Problematic</option>
		<option value=4>Deleted</option>
		<option value=5>All</option>
	</select>
	<input type="hidden" name="dist" value='.$_REQUEST['dist'].' />
		</form>
		</div>
		<h3 style="margin-top:1em;padding-left:4em;">Reports </h3>
		
<script type="text/javascript" >

function submitValidate()
	{
		var n = $( "input.delcheck:checked" ).length + $( "input.undelcheck:checked" ).length;
		if (n<1) 
			{
			alert("No recordings are selected! Form not submitted!");
			return false;
			}
		else
			{   
		
				n = $( "input.delcheck:checked" ).length;
				if (n>0)
					{
					return confirm("Are you sure you want to delete the records of " + n + " recordings from the database?");
					}

				n = $( "input.undelcheck:checked" ).length;
				if (n>0) 
					{
					return confirm("Are you sure you want to UNDELETE " + n + " recordings in the database?");
					}
			}
		
	}
	
jQuery(document).ready(function(){
	$("#stnd").val('.$_SESSION['standard'].');
	$(".probbutt").click( function() {
		if (confirm("Reset the problematic recordings to pending and try to record them again?"))
				$("#resetForm").submit();
		});
	$("#statusMess").corner();
	';
if(isset($_REQUEST['userdate']))
	{
		print '$("#userdate").val("'.$_REQUEST['userdate'].'");\r\n';
	}
print '$("input:checked").prop("checked",false);

	$("#all").change(function() {
		$(this).prop("checked",false);
		if (confirm("Are you sure you want to remove ALL recordings visible on the screen from this database (the recording files will not be erased, they will just be removed from the queue and display)?"))
			{
			$(this).prop("checked",true);
			$(".delcheck").prop("checked",true);
			}
		});
	

});
</script>
';

$query = 'SELECT c.scoid, c.datedownloaded,  c.status, u.foldername, u.url, u.recordingname, u.datecreated, u.meetingname, u.duration FROM cc_recordings_saved c INNER JOIN cc_recordings_in_ac u ON c.scoid = u.scoid' ;

switch ($_SESSION['standard'])
	{
	case 0:
		$query .= ' where c.status <> "DEL"';
		break;
	case 1:
		$query .= ' where c.status = "P"';
		break;
	case 2:
		$query .= ' where c.status = "D"';
		break;
	case 3:
		$query .= ' where c.status = "PROB"';
		break;
	case 4:
		$query .= ' where c.status = "DEL"';
		break;
	//case 5 is the default = basic query
	}



$data = mysql_query($query);


print "<form method='post' action='delete.php' onsubmit='return submitValidate();' >\r\n";
print "<table align='center' cellpadding='0' cellspacing='0' border='0' class='display' id='example' style='font-size: 14px;margin-top: 1em;margin-bottom: 1em;' >\r\n"; 
print "<thead>\r\n";
print "<tr style='background-color:#8e0202 !important;color:#fff;'>\r\n";
print "<th colspan=2 ><span id='statusMess' class=".$status_class." onclick='fileServ(".'"ERRORFILE"'.")' title='Download the Sikuli Error File' > ".$status_check."</span></th>";
print "<th colspan=1 >\r\n";
//if the service.php has created records, display the notice
print "<strong><span class=success>".$message."</span></strong>\r\n";
print "</th>\r\n";
print "<th colspan=3 ><input name=delsubmit class=delsubmit type=submit value='Submit deletions' />\r\n
   		<input name=problematic class=probbutt type=button value='Reset problem recs' />\r\n
  		<a href='downloader.php' ><input name=download type=button value='View data' /></a></th>\r\n";
print "</tr>\r\n";
print "<tr style='background-color:#8e0202 !important;color:#fff;'>\r\n";
print "<th>Delete all <br/> <input id=all type='checkbox' name='all' /></th>\r\n";
print "<th>Recording name</th>";
print "<th>Created</th>";
print "<th>Downloaded</th>";
print "<th>Course</th>";
print "<th>Duration</th>";
print "</tr>\r\n";
print "</thead>\r\n";
print "<tbody>\r\n";
if(!empty($data))
{
	$i = 1;
	while($info = mysql_fetch_array( $data )) 
 		{ 
 		print "<tr class='gradient' title='Status: ".$info['status']."' >"; 
        $duration = (int) $info['duration'];
        $hr = intval($duration/3600);
        $min = intval(($duration % 3600)/60);
        if ($min<10) $min = "0".$min;
 		if ($info['status']=="DEL") 
			{
			print "<td title='ID#=".$info['scoid']."' class=deleted ><input class=undelcheck type=checkbox name=UNDEL".$info['scoid']." value=".$info['scoid']." /></td> "; 
			}
		else
			if ($info['status']=="PROB") 
				{
				print "<td title='ID#=".$info['scoid']."' class=problem ><input class=delcheck type=checkbox name=DEL".$info['scoid']." value=".$info['scoid']." /></td> "; 	
				}
			else
				{
				print "<td title='ID#=".$info['scoid']."' ><input class=delcheck type=checkbox name=DEL".$info['scoid']." value=".$info['scoid']." /></td> "; 	
				}
		$theUrl = str_replace('offline','edit',$info['url']).'&fcsContent=true';
		
 		print "<td sort='" .$info['recordingname']. "' title='edit recording' >
		<a target='_blank' href='".$theUrl."' >" .$info['recordingname']."</a> </td>";
 		print "<td>".substr($info['datecreated'],0,10)." </td>";
		if ($info['status']=="D") 
			{
			print '<!--- '; //comment out the fopen Error messages
			$fileData = testFile($root_path.$info['foldername']."\\".$info['recordingname']."_0.flv",$duration);
			print ' -->';
			$_SESSION["SCO".$info['scoid']] = $fileData['name'];
       		print "<td title='Download ".$fileData['button']." file' >\r\n";
			// invisible text for sorting purposes
			print "<span style='display:none' >".$info['datedownloaded']."</span>\r\n";
			print "<input class='flvsub' type=button onclick='fileServ(".'"'.$info['scoid'].'"'.");' value='".substr($info['datedownloaded'],0,16)."' />\r\n";
			print "</td>\r\n";
			}
		else
			{// check existence of file
			print '<!--- '; //comment out the fopen Error messages
			$fileData = testFile($root_path.$info['foldername']."\\".$info['recordingname']."_0.flv",$duration);
			print ' -->';
			if ($fileData['size']==0)
				{
        		print "<td >".substr($info['datedownloaded'],0,16) . " </td>";
				}
			else
				{
       			print "<td title='Download ".$fileData['button']." file' >\r\n";
				// invisible text for sorting purposes
				print "<span style='display:none' >".$info['datedownloaded']." $size $kbs</span>\r\n";
				$_SESSION["SCO".$info['scoid']] = $fileData['name'];
				print "<input class='flvsub' type=button onclick='fileServ(".'"'.$info['scoid'].'"'.");' value='".$fileData['button']."' />\r\n";
				print "<input title='Mark recordings as done' class='mark_done' type=button onclick='markDone(".'"'.$info['scoid'].'"'.");' value='OK' />\r\n";
				print "</td>\r\n";
				}
			}
 		print "<td>".$info['meetingname'] . " </td>";
        print "<td title='".$duration."' >".$hr.":".$min." </td>"; 
 		print "</tr>\r\n"; 
 		} 
}

print "</table>\r\n</form>\r\n<br/><br/>\r\n
</div>\r\n
<form action='".$_SERVER['REQUEST_URI']."' id='flvform' method=post >
<input type=hidden name='rec_serv' id='rec_serv' value='' />
</form>
<form action='".$_SERVER['REQUEST_URI']."' id='resetForm' method=post >
<input type=hidden name='reset' id='reset' value='reset' />
</form>
</body>\r\n
</html>\r\n";
?>

