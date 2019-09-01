<?php ini_set('display_errors', 'On');
/*

 Unless otherwise noted, code in this file is copyright (C) 2017 Capitol Technology University
 Kenneth Mayer 
 distlearn@captechu.edu 
 ken.i.mayer@gmail.com
*/
ignore_user_abort(true);
error_reporting(E_ALL);
const debug =false;
$debug = false;
include '../common/int_config.php';
if (!($_REQUEST['dist']==PHP_pass))
	{
	// If the password variable isn't there go to error message
	header("Location: https://".$_SERVER['HTTP_HOST']."/connect/errors.php");
	}
if (isset($_REQUEST['debug']) && $_REQUEST['debug']=='yes')
	$debug = true; 

/*
if ($debug)
	{
	print('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">');
	print('<html>');
	print('<head></head><body>');
	}
*/

$GLOBALS['qs'] = $_SERVER['QUERY_STRING'];
session_start();


include '../common/sess.php';
include '../common/adobe.php';

$acc = new AdobeConnectClient();
//$s = $_REQUEST['fromdate'];
$query = mysql_query('SELECT download_from_date FROM cc_configdetails');

$from_date = date('Y-m-d', strtotime(date('Y-m-d') .' -7 day'));
while($info = mysql_fetch_array($query)) 
	{ 
	if ($debug) //print var_export($info);
	if (! is_null($info['download_from_date']) || $info['download_from_date']>'2012-01-01') $from_date = $info['download_from_date'];
	}

if ($debug) logit('makeRequest(report-bulk-objects&filter-icon=archive&filter-gt-date-created='.$from_date);

$api_rqst =  $acc->makeRequest('report-bulk-objects&filter-icon=archive&filter-gt-date-created='.$from_date);

if ($debug) 
	{logit("VAR DUMP: report-bulk-objects created after $from_date");
	logit(var_dump($api_rqst,false));
	 }


$total_records = $api_rqst['report-bulk-objects']['row'];
$count = 0;
if(isset($total_records))
	{
	if ($debug) 
		{logit('<pre>VAR DUMP: total_records:</pre>');
		logit(var_dump($total_records,false));
		 }
	if ($debug) logit(var_export($total_records));
	if(is_array($total_records[0]))
		{
		for($i=0;$i<sizeof($total_records);$i++)
			{
			if (! isset($total_records[$i]['@attributes']['sco-id'])) continue;
			$sco_id_url = $total_records[$i]['@attributes']['sco-id'];
			$sco_id = $sco_id_url;
			$dept = 1;
			$canvas_id='0';
			$folder = getFolderInfo($sco_id);
			$folder_sco = $folder['sco'];
			$meeting_name =$folder['name'];
			$meeting_url =$folder['url'];
			// SCreen out recordings that are not from classes or the CAE Tech talk
			// const INST_DOWNLOAD = array('_201','cae_tech_talk');
			$skip = true;
			$downloads = explode('|',INST_DOWNLOAD);
			foreach ($downloads as $down)
				if (strpos($meeting_url,$down)>-1) $skip=false;
			if ($skip)
				{
				logit($meeting_url .' does not fit criteria. Skipped.');
				continue;
				}
			$profID = $folder['prof'];
			if (isset($folder['canvas-id'])) $canvas_id = $folder['canvas-id'];
			if (isset($folder['department'])) $dept = $folder['department'];
			$folder_name = substr($meeting_url, 1, -1 ); // removes slashes
			if (isset($total_records[$i]['date-end'])) $date_created = $total_records[$i]['date-end'];
			if (isset($total_records[$i]['date-created'])) $date_created = $total_records[$i]['date-created'];

			$url = ADOBE.$total_records[$i]['url'].'?pbMode=offline';
			$url = str_replace('com//','com/',$url); // get rid of double slashes in wrong place

			// load up attendance reports for new recordings
			// first create filter
			$attend = 0;
			$filter= '&filter-like-date-created='. substr($date_created,0,10);
			if ($debug) logit($folder_name.' date: '.$date_created. ' meeting sco: '. $folder_sco . ' filter: '. $filter);
			if (isset($folder_sco) && intval($folder_sco)>1000)	$attend = sumAttendance($sco_id,$folder_sco,$filter,$profID);
			if ($debug) logit($attend);
			//
			// let's get the true duration from a bunch of other API calls
			//
			$duration = 0;
			$duration = getDuration($sco_id,$folder_sco);
			$duration = abs($duration);
			if ($debug)
				{
				//print('<pre>Folder Sco-id: '.$folder_sco.'</pre>');
				logit('Folder_sco: '.$folder_sco. ' sco: '.$sco_id. ' duration: '. $duration);
				}

			$hr = intval($duration/3600);
			$min = intval(($duration % 3600)/60);
			if ($min<10) $min = '0'.$min;

			$rc_name = 	clearChar($total_records[$i]['name']);	
			
			if (debug)
				{//print('<pre>Duration (adjusted): '.$duration.' = '.$hr.':'.$min.'</pre>');
				$ans = 'false';
				if ($duration>15000) $ans='true';
				logit("$rc_name $profID Duration: $duration = $hr:$min duration>15000: $ans");
				}
			$folder_name = substr($meeting_url, 1, -1 );
			if ($debug) logit('course: '.$folder_name.' prof: '.$profID);

			$query = "INSERT INTO cc_recordings_in_ac (scoid,foldername,instructor,url,datecreated,meetingurl,meetingname,recordingname, attendance, duration) VALUES ('$sco_id_url','$folder_name','$profID','$url','$date_created', '$meeting_url', '$meeting_name', '$rc_name',  '$attend','$duration')";

			if(mysql_query($query))
				{ $count++; 

				// only report NEW durations that are too long
				 if ($duration>15000) 
				 	{$editLink = str_replace('offline','edit',$url).'&fcsContent=true';
					$mailMess = '<p>The following recording was longer than four and a half hours and will be shortened to that length. We recommend checking and editing the recording to its actual length, presumably a lot shorter.</p>';
					$mailMess .= "<p folder='".$folder_name."' ><b>$meeting_name</b><br /> <b>Recording:</b> $rc_name <br /><b>Seconds:</b> $duration <b>Duration:</b> $hr:$min<br /><a href='".$editLink."' >Click here to view or edit the recording</a></p>";
					$mailMess .= "<br /><p>Some professors forget to end recordings, which <b style='
    text-decoration: underline'>can result in their private conversations being inadvertently captured and shared.</b> It also results in 8-12 hour recordings, which is a hassle for students and our recording server. <br />We recommend formally ending the recording and then leaving Adobe. You can also quit your browser and shut the Adobe windows. That should ensure that the recording ends. Professors who connect with multiple devices for teaching online classes should be sure to shut down all devices!</p>";
					$mailTo= $profID.'@captechu.edu';
					sendMail($mailTo,'Course Recording too Long!',$mailMess);
					}
				}
			else
				{	
				$query = "UPDATE cc_recordings_in_ac SET duration ='$duration',meetingname='$meeting_name',recordingname ='$rc_name',instructor='$profID' WHERE scoid ='$sco_id_url'";
				if(! mysql_query($query)) logit("$query ERROR: ".mysql_error() );
				}
			if ($debug) 
				{logit('<pre>SEARCH: '.$query.'</pre>'); 
				logit('<pre>I: '.$i.' Count: '.$count.'</pre>');
				logit('mysql_info(): '.mysql_info()); 
				logit('Affected rows: '.mysql_affected_rows());
				}
			}
		}
	else
		{
		$sco_id_url = $total_records['@attributes']['sco-id'];
		$sco_id = $sco_id_url;
		$rc_name = 	clearChar($total_records['name']);
		if (isset($total_records['date-end'])) $date_created = $total_records['date-end'];													
		if (isset($total_records['date-created'])) $date_created = $total_records['date-created'];
				
		$folder_sco ='0';
		$duration = 0;
		$folder = getFolderInfo($sco_id);
		$folder_sco = $folder['sco'];
		$meeting_name =$folder['name'];
		$meeting_url =$folder['url'];
		$profID = $folder['prof'];
		$folder_name = substr($meeting_url, 1, -1 ); // removes slashes

		if ($debug)
			{
			//print('<pre>Folder Sco-id: '.$folder_sco.'</pre>');
			logit('Folder Sco-id: '.$folder_sco);
			}
		$attend=0;
		// load up attendance reports for new recordings
		// first create filter
		$filter= '&filter-like-date-created='. substr($date_created,0,10);
		if ($debug) logit($folder_name.' date: '.$date_created. ' sco: '. $sco_id . ' filter: '. $filter);
		$attend = sumAttendance($sco_id,$folder_sco,$filter,$profID);
		if ($debug) logit($attend);
		$duration = getDuration($sco_id,$folder_sco);
		$duration = abs($duration);
		$hr = intval($duration/3600);
		$min = intval(($duration % 3600)/60);
		if ($min<10) $min = '0'.$min;

		if ($debug)
			{//print('<pre>Duration (adjusted): '.$duration.' = '.$hr.':'.$min.'</pre>');
			logit('Duration (adjusted): '.$duration.' = '.$hr.':'.$min);
			}

		$url = ADOBE.$total_records['url'].'?pbMode=offline';
		$icon_type = $total_records['@attributes']['icon'];


		$folder_name = substr($meeting_url, 1, -1 );
		if ($debug) logit('course: '.$folder_name.' prof: '.$profID);
		if (strpos($meeting_url,'_20')>5 || strpos($meeting_url,'cae')>-1) 
			{ // do not make recordings of NON classes.
			$query = "INSERT INTO cc_recordings_in_ac (scoid,foldername,instructor, url,datecreated,meetingurl,meetingname,recordingname, attendance, duration) VALUES ('$sco_id_url','$folder_name','$profID','$url','$date_created', '$meeting_url', '$meeting_name', '$rc_name','$attend', '$duration')";
			if ($debug) logit('SEARCH: '.$query. ' -- Only one record');
			if(mysql_query($query))
				{$count++;

				if ($duration>15000) 
					{
					$editLink = str_replace('offline','edit',$url).'&fcsContent=true';
					$mailMess = '<p>The following recording was longer than four and a half hours and will be shortened to that length. We recommend checking and editing the recording to its actual length, presumably a lot shorter.</p>';
				$mailMess .= "<p folder='".$folder_name."' ><b>$meeting_name</b><br /> <b>Recording:</b> $rc_name <br /><b>Seconds:</b> $duration <b>Duration:</b> $hr:$min<br /><a href='".$editLink."' >Click here to view or edit the recording</a></p>";
					$mailTo= $profID.'@captechu.edu';
					sendMail($mailTo,'Course Recording too Long!',$mailMess);
					}
				} 
			else { 
				$query = "UPDATE cc_recordings_in_ac SET duration ='$duration',meetingname='$meeting_name', instructor='$profID', recordingname ='$rc_name' WHERE scoid ='$sco_id_url'";
				if(! mysql_query($query)) logit($query .' ERROR: '.mysql_error()); 
				}
			}
		}
	}
	else
	{
		$_SESSION['recs'] = 0;
	}
	$new_search = "INSERT INTO cc_recordings_saved (scoid) SELECT scoid FROM cc_recordings_in_ac WHERE scoid not in (SELECT scoid FROM cc_recordings_saved)";
	$recs = 0;
	$mess='';
	if (mysql_query($new_search))
		{
		$recs +=  mysql_affected_rows();
		}
	else
		{
		$date = date('d.m.Y h:i:s'); 
		$mess = "$date | Query $query ERROR: ".mysql_error();		
		logit($mess);
		}
		
	$_SESSION['recs'] =  $recs;
	
	if ($debug) 
		{logit('SEARCH: '.$new_search); 
		logit('mysql_info(): '.mysql_info()); 
		logit('Affected rows: '.mysql_affected_rows());
		}
		

	// if it's Sunday, clear out old records
	if (date('w')==0) clearOld();
	//clearOld(); // for testing on a day besides a Sunday
	session_write_close();
	$GLOBALS['qs'] = $GLOBALS['qs'];
	if ($debug)
		{ logit(var_dump($_REQUEST));
		 logit(var_dump($GLOBALS));
		}
	else
		{
		header("Location: https://".$_SERVER['HTTP_HOST']."/connect/reports.php?".$GLOBALS['qs']);
		}

function sendMail($to,$subject,$text)
	{
	require_once 'Mail.php';
	// $from = the local Server account
	$new_to = $to.','.INST_TO;
	// if the professor email is empty cut it out!
	if (strpos($to,'@')==0) $new_to = INST_TO;
	logit('sending mail to '. $to .' subj: '.$subject);
	$from = INST_mail;
	// $to = the receiver's email

	// $host = the name of the server where your account is located
	$host = 'localhost';
	$port = mail_port;
	// $username and $password are the uid & pwd of your account

	$headers = array ('From' => $from,
		'To' => $to,
		'Subject' => $subject,
		'Reply-To' => INST_DEPT .' <'.INST_TO.'>',
		'MIME-Version' => '1.0',
		'Content-Type' => 'text/html; charset=ISO-8859-1',
		'Cc' => INST_TO,
		'Date'      => date('r', time())
		);
	$smtp = Mail::factory('smtp',
			array ('host' => $host,
			'port' => $port
			//'auth' => true,
			//'username' => mail_user,
			//'password' => mail_pass
			
			));
	try {
		$mail = $smtp->send($new_to, $headers, $text);
		}
	catch (Exception $e) {
		$err = $e->getMessage();
		logit('Mail error: '.$err);
		}
	
	if (PEAR::isError($mail)) 
		{
		$err = $mail->getMessage();
		echo("<p>$err</p>");
		logit('Mail error: '.$err);
		} 
	else {
		//echo("<p>Message successfully sent!</p>");
		logit('Mail successfully sent.');
		}
	}

function getProfID($course_sco)
	{
	global $debug;
		// find the instructor id, such as  clcayot, carankin, hyu, jmpittman
	$profCode = '';
	/*
		live_courses Structure
		course_code 	tinytext	No 	  	 			(iae-677-l02_2016_40_gs	)
		name 			tinytext	No 	  				(IAE-677-L02 Malicious Software 2016_40_GS)
		prof 			tinytext	No 	  	 			(Peter Christensen	)
		prof_id 		tinytext	Yes 	NULL  	 	(phchristensen)
		connect_id 		int(10)	No 	0  	 				(1626733144)
	*/
	$query = 'SELECT prof_id FROM live_courses WHERE connect_id="'.$course_sco.'"';
	$results = mysql_query($query);
	if ($debug) logit('Query: '.$query. ' affected rows: '. mysql_affected_rows());
	$err= mysql_error();
	if (mysql_affected_rows()<1 && $err ) logit("Query $query ERROR: $err");
	
	while($info = mysql_fetch_array($results)) 
		{ 
		if (! is_null($info['prof_id']) && strlen($info['prof_id'])>0 ) $profCode = $info['prof_id'];
		}
	if ($profCode=='') $profCode = connectProf($course_sco);
	return $profCode;
	}

function connectProf($sco)
	{
	global $acc, $debug;
	// given the sco of a course in connect, find the folder info
	$prof_id = '';
	$scos = $acc->makeRequest('sco-info&sco-id='.$sco); 
	if (! isset($scos['sco'])) return $prof_id;
	$folder=$scos['sco'];
	$first_sco= $sco;
	$first_name = '';
	if (isset($folder['name'])) $first_name = $folder['name'];
	while (isset($folder['@attributes']['folder-id']) && $folder['@attributes']['folder-id'] != '1141159112') 
		{
		$sco = $folder['@attributes']['folder-id'];
		$scos = $acc->makeRequest('sco-info&sco-id='.$sco);
		if (! isset($scos['sco'])) break; 
		$folder=$scos['sco'];
		}
	if (isset($folder['name'])) $prof_id = $folder['name'];
	if ($debug) logit($first_sco.' - '.$first_name. ' prof_id in connect: '.$prof_id);
	return $prof_id; 
	}
	
function clearOld()
	{
	// this clears out records from more than 4 months ago from the database to an archive
	// first move records to the archive
	// the moved records should not have been modified within the last month (that will catch recengtly rerecorded recs or modified recs
	if ( intval(date('m'))>1)
		{
		$mod_date =  date('Y') . '-0'.strval( intval(date('m'))-1 ).'-01';
		$mod_date = str_replace('00','0',$mod_date);
		}
	else
		{
		$mod_date =  strval(intval(date('Y'))-1) . '-12-01';
		}
	if ( intval(date('m'))>5)
		{
		$create_d = date('Y') . '-0'.strval(intval(date('m'))-4) .'-01';
		$create_d = str_replace('00','0',$create_d);
		}
	else
		{
		$create_d =  strval(intval(date('Y'))-1) .  '-0'.strval(7 + intval(date('m'))) .'-01';
		$create_d = str_replace('00','0',$create_d);
		}
	
 // try without mod date
   $query = 'INSERT INTO cc_recordings_archive (scoid, foldername, url,meetingname,meetingurl, recordingname,datecreated,duration) SELECT c.scoid, u.foldername, u.url,u.meetingname,u.meetingurl, u.recordingname,u.datecreated,u.duration FROM cc_recordings_saved c INNER JOIN cc_recordings_in_ac u ON c.scoid = u.scoid where c.status = "D" AND u.datecreated < "'.$create_d.'"'; 
  
  	if(mysql_query($query))
		{
		logit('create_date: '.$create_d.' mod_date: '.$mod_date);
		logit('Clearing '.mysql_affected_rows(). ' old records out of the database.');

  		// now delete the archived records from the main database
		// SELECT u.scoid, u.foldername, u.url,u.meetingname,u.meetingurl, u.recordingname,u.datecreated FROM cc_recordings_in_ac u INNER JOIN cc_recordings_in_archive a ON u.scoid = a.scoid
		$query = 'DELETE c FROM `cc_recordings_saved` c INNER JOIN `cc_recordings_archive` a ON c.scoid = a.scoid';
		if (! mysql_query($query)) logit("Query $query ERROR: ".mysql_error());
		$query = 'DELETE u FROM `cc_recordings_in_ac` u INNER JOIN `cc_recordings_archive` a ON u.scoid = a.scoid';
		if (! mysql_query($query)) logit("Query $query ERROR: ".mysql_error());
		}
	else
		{logit("Query $query ERROR: ".mysql_error());
		}
	}
function getFolderID($sco)
	{
	global $acc;
	$scos = $acc->makeRequest('sco-info&sco-id='.$sco);
	$folder_scoData=$scos['sco'];
	return $folder_scoData['@attributes']['folder-id'];
	}
	
function getFolderInfo($sco_id)
	{
	global $acc, $debug;
	$icon_type = '';
	if ($debug) logit('Start getFolderInfo('.$sco_id.')');
	//if ($debug) print('<h3>Start getFolderInfo('.$sco_id.')</h3>');
	$ret = array();
	$ret['name'] = '';
	$ret['sco'] = $sco_id;
	$ret['url'] = '';
	$ret['prof'] = '';
	
	while($icon_type != 'meeting')
		{
		$ret['sco'] = $sco_id; // this has to be before it is processed, since we are using folder-id
		$results = $acc->makeRequest('sco-info&sco-id='.$sco_id); 
		$sco=$results['sco'];
		if ($debug) logit('action=sco-info&sco-id='.$sco_id.' '.var_export($results,false));
		if (isset($sco['@attributes']['folder-id']) ) $sco_id = $sco['@attributes']['folder-id'];
		if (isset($sco['@attributes']['icon']) ) $icon_type = $sco['@attributes']['icon'];
		if (isset($sco['name']) ) $ret['name'] = clearChar($sco['name']);
		if (isset($sco['url-path']) ) $ret['url'] = $sco['url-path'];
		if ($debug) logit('getFolderInfo temp sco: '.$sco_id. ' meeting name: '.$ret['name']. ' url: '. $ret['url']. ' meeting sco: '. $ret['sco']);
		if ($debug) logit('<p> temp sco: '.$sco_id. ' meeting name: '.$ret['name']. ' url: '. $ret['url']. ' meeting sco: '. $ret['sco'].'</p>');
		
		}
	if($icon_type == 'meeting' && $ret['sco'] !='') 
		{
		if (isset($sco['name'])) $first_name = $sco['name'];
		while (isset($sco['@attributes']['folder-id']) && $sco['@attributes']['folder-id'] != '1141159112') 
			{
			$fold = $sco['@attributes']['folder-id'];
			$scos = $acc->makeRequest('sco-info&sco-id='.$fold);
			if (! isset($scos['sco'])) break; 
			$sco=$scos['sco'];
			}
		if (isset($sco['name'])) $ret['prof'] = clearChar($sco['name']);
		//$ret['prof']= getProfID($meeting_sco); // this is replaced by this process above, maybe?
		}
	$course=$ret;
	$course['course_id']=$ret['url'];
	//canvasInfo($ret['url']);  Removing Canvas calls from this distribution
	$ret = $course;
	//if ($debug && is_array($course)) foreach ($course as $key=>$val)	
		//{logit($ret['url']. ' -- '. $key.': '.$val);
		//}
	if ($debug) logit('getFolderInfo FINAL sco: '.$sco_id. ' meeting name: '.$ret['name']. ' url: '. $ret['url']. ' meeting sco: '. $ret['sco']. ' prof: '.$ret['prof']);
	if ($debug) logit('<p><strong> FINAL sco: '.$sco_id. ' meeting name: '.$ret['name']. ' url: '. $ret['url']. ' meeting sco: '. $ret['sco']. ' prof: '.$ret['prof'].'</strong></p>');
	return $ret;
	}
	
function getDuration($sco, $fold)
	{
	global $acc, $debug;
	$new_duration = 0;
	$duration = '';
	$hr = 0;
	$min = '';
	$duration_Data = $acc->makeRequest('list-recordings&folder-id='.$fold.'&filter-sco-id='.$sco);
	if ($debug)
		{
		logit('list-recordings&folder-id='.$fold.'&filter-sco-id='.$sco);
		logit('<pre>VAR DUMP duration_Data[scos]: </pre>');
		logit(var_export($duration_Data, false));
		}

	if(isset($duration_Data['recordings']['sco']['duration']))
		{
		if ($debug)
			{//print('<pre>Duration (raw): '.$duration_Data['recordings']['sco']['duration'].'</pre>');
			logit('Duration (raw): '.$duration_Data['recordings']['sco']['duration']);
			}
		$new_duration = $duration_Data['recordings']['sco']['duration'];
		$hr = intval($new_duration);
		$min = substr($new_duration,3,2);
		if ($debug)
			{//print('<pre>New Duration: '.$new_duration.' = '.$hr.':'.$min.'</pre>');
			logit('New Duration: '.$new_duration.' = '.$hr.':'.$min);
			
			}
		}
	else
		{
		$duration_Data = $acc->makeRequest('list-recordings&folder-id='.$fold);
		if ($debug) 
			{
			//print ('list-recordings&folder-id='.$fold);
			logit('list-recordings&folder-id='.$fold);
			logit('VAR DUMP duration_Data[scos]');
			logit(var_export($duration_Data, false));
			}
		}
	if(isset($duration_Data['recordings']['sco']['af-recording-duration']))
		{
		if ($debug)
			{//print('<pre>Current Duration (raw): '.$duration_Data['recordings']['sco']['af-recording-duration'].'</pre>');
			logit('Current Duration (raw):'.$duration_Data['recordings']['sco']['af-recording-duration']);						
			}
		$new_duration = $duration_Data['recordings']['sco']['af-recording-duration'];
		$hr = intval($new_duration);
		$min = substr($new_duration,3,2);
		if ($debug)
			{//print('<pre>New Duration (current): '.$new_duration.' = '.$hr.':'.$min.'</pre>');
			logit('New Duration (current): '.$new_duration.' = '.$hr.':'.$min);		
			}
		}

	if(isset($duration_Data['recordings']['sco']['recording-edited-duration']))
		{
		if ($debug)
			{//print('<pre>Current Duration (raw): '.$duration_Data['recordings']['sco']['recording-edited-duration'].'</pre>');
			logit('Current Duration (raw):'.$duration_Data['recordings']['sco']['recording-edited-duration']);						
			}
		$new_duration = $duration_Data['recordings']['sco']['recording-edited-duration'];
		$hr = intval($new_duration);
		$min = substr($new_duration,3,2);
		if ($debug)
			{//print('<pre>New Duration (edited): '.$new_duration.' = '.$hr.':'.$min.'</pre>');
			logit('New Duration (edited): '.$new_duration.' = '.$hr.':'.$min);		
			}
		}
	
	if ($new_duration !== 0) $duration = abs($hr*3600+intval($min)*60);
	$duration = $duration+ 300;
	if ($debug)
		{//print('<pre>Duration (adjusted): '.$duration.' = '.$hr.':'.$min.'</pre>');
		logit('Duration (adjusted): '.$duration.' = '.$hr.':'.$min);
		}	

	return $duration;
	}
?>
*/