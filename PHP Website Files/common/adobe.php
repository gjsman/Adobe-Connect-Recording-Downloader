<?php
/*
 Unless otherwise noted, code in this file is copyright (C) Capitol Technology University
 Kenneth Mayer 
 distlearn@captechu.edu 
 ken.i.mayer@gmail.com

 Addentum Copyright (C) 2019 Gabriel Sieben
*/
class AdobeConnectClient {
	private $cookie;
	private $curl;

	public function __construct () {
		$this->cookie = sys_get_temp_dir().DIRECTORY_SEPARATOR.'cookie_'.time().'.txt';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_REFERER, ADOBE_API);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		$this->curl = $ch;
		$api_rqst = $this->makeRequest('common-info');
		$_SESSION['api'] = $api_rqst["common"]["cookie"];
		$this->makeAuth();
		}
	/**
	 * make auth-request with admin username and password
	  */
	public function makeAuth() {
		$user = ADOBE_ADMIN;
		$pass = ADOBE_PWD;

		$this->makeRequest('login',
			array(
				'login'    => $user,
				'password' => $pass,
			)
		);
		return $this;
	}



	public function __destruct() {
		$api_rqst = $this->makeRequest('logout');
		@curl_close($this->curl);
	}

	public function makeRequest($action, array $params = array()) {
		$url = ADOBE_API;
		$url .= 'xml?action='.$action;
		if ($action != "common-info")  $url .= '&session='.$_SESSION['api'];
		if (isset($params) && is_array($params) && count($params)>0 ) $url .= '&'.http_build_query($params);
		curl_setopt($this->curl, CURLOPT_URL, $url);
		$result = curl_exec($this->curl);
		$xml = simplexml_load_string($result);
	 	
		$json = json_encode($xml);
		$data = json_decode($json, true); 
		if (!isset($data['status']['@attributes']['code']) || $data['status']['@attributes']['code'] !== 'ok') {
			//throw new Exception('Coulnd\'t perform the action: '.$action);
		}
		return $data;
	}
}

function getSCO($url)
	{
	global $acc, $debug;
	if ($debug) print '<p>getSCO('.$url.')</p>';
	$sco='';
	$api_rqst = $acc->makeRequest('sco-expanded-contents', array(
		'sco-id' =>ADOBE_MFOLD,
		'filter-type' =>'meeting',
		'filter-url-path' => '/'.$url.'/') 
	);
	if(isset($api_rqst['expanded-scos']['sco']))
		{
		$courses = $api_rqst['expanded-scos']['sco'];
		$sco = $courses['@attributes']['sco-id'];
		$_SESSION['course'] = $sco;
		$_SESSION['folder'] = $courses['@attributes']['folder-id'];
		$_SESSION['desc'] = $courses['description'];
		$_SESSION['c_name'] = $courses['name'];
		}
	return $sco;
	}


function getMeetingID($sco)
	{
	global $acc, $debug;
	$scos = $acc->makeRequest("sco-info&sco-id=".$sco); //getFolderName
	if (! isset($scos['sco']))
		{
		logit('getMeetingID error sco: '.$sco.' --> '.var_export($scos,true));
		return '';
		}
	
	$folder_scoData=$scos["sco"];
	return $folder_scoData['@attributes']['folder-id'];
	}
	
	
	
function sumAttendance($rec_sco,$sco,$filter, $inst = '')
	{
	global $acc, $debug;
	$results = $acc->makeRequest("report-meeting-attendance&sco-id=".$sco.'&sort-login=asc'.$filter);
	if ($debug) logit("report-meeting-attendance&sco-id=".$sco.'&sort-login=asc'.$filter);
	if (! isset($results['report-meeting-attendance']['row']))
		{ // empty or an error
		if( isset($results['status']['@attributes']['code']) && ($results['status']['@attributes']['code']=='ok' || $results['status']['@attributes']['code']=='no-data')) 
			{
			return 0; // no info for this recording
			}
		else {logit('meeting-attendance ERROR: '.$sco. ' filter: '. $filter.' --> '.var_export($results,true));}
		return;
		}
	$rows = $results['report-meeting-attendance']['row'];
	//print(var_export($rows,true));
	$unique_viewers = array(); 

	foreach ($rows as $row)
		{
		//print (var_export($row,true));
		$login = '';
		$sess = '';
		$trans = '';
		$start = '';
		$stop = '';
		$status = 'student';
		if (isset($row['@attributes']['asset-id'])) $sess=$row['@attributes']['asset-id'];
		if (isset($row['@attributes']['transcript-id'])) $trans=$row['@attributes']['transcript-id'];
		if (isset($row['participant-name'])) $login=$row['participant-name'];
		if (isset($row['session-name'])) $login=$row['session-name'];
		if (isset($row['login']) && strlen($row['login'])>3 ) $login= $row['login'];
		if (isset($row['date-created'])) $start= $row['date-created'];
		if (isset($row['date-end'])) $stop= $row['date-end'];
		if ($login=='') continue; // don't bother if there is NO name attached
		$to_time = strtotime($stop);
		$from_time = strtotime($start);
		$secs = round(abs($to_time - $from_time),2);
		$stop= date("Y-m-d H:i:s",$to_time);
		$start =date("Y-m-d H:i:s",$from_time);
		// do not add TECHs to attendance counts
		if (strpos($login,'tech')>1 && strpos($login,'tech')==strlen($login)-4)
			{
			if ($debug) logit('sco:'.$sco.' tech detected: '.$login.' min: '.floor($secs/60));
			$status='tech';
			}
		else
			{// do not add professor in attendance count
			if ($login==$inst)
				{
				if ($debug) logit('sco:'.$sco.' prof detected: '.$login.' min: '.floor($secs/60));
				$status='instructor';
				}
			else
				{if (! in_array($login,$unique_viewers)) $unique_viewers[]=$login;
				}
			}
		}

	unset($unique_viewers[$inst]); // leave out the professor
	$attend = count($unique_viewers);
	if ($debug) logit($sco.' attendance: '.$attend);
	return $attend; 

	}
	
// info is a record from the database cc_recordings_in_ac

function rec_attend($info)
	{
	global $acc, $debug;
	$start='';
	$duration='';
	$inst='';
	$rec_sco = $info['scoid'];
	if (isset($info['datecreated'])) $start = $info['datecreated'];
	if (isset($info['duration']))$duration = $info['duration'];
	if (isset($info['instructor']))$inst = $info['instructor'];
	$filter ='';

	$date = new DateTime($start);
	$rec_start = $date;
	$rec_end = $date; // temporarily have it start at the same place
	$rec_start->sub(new DateInterval('PT1H')); // start session 1 hour before recordings
	$start = date('c', $rec_start->getTimestamp());
	$start = substr($start,0,13);
	$rec_end->add(new DateInterval('PT'.$duration.'S')); 
	$rec_end->add(new DateInterval('PT1H')); // end 1 hour after end of recording
	$end = date('c', $rec_end->getTimestamp());
	$end = substr($end,0,16);
	if (strlen($start)>6 && strlen($end)>6)	$filter = '&filter-gte-date-created='.$start.'&filter-lte-date-end='.$end;

	$meet_sco = getMeetingID($rec_sco);
	if ($debug) logit('sco: '.$rec_sco . ' filter: '.$filter);
	if ($meet_sco=='') return 0; // there is no meeting ID for this recording

	$attend = sumAttendance($rec_sco,$meet_sco,$filter, $inst);
	return $attend; 
	}

/**
 * DB connection.
 * 
 */
$dbcon = mysql_connect('localhost',MYSQL_USER,MYSQL_PWD);

if (!$dbcon)
	{
	die('Could not connect: ' . mysql_error());
	}
mysql_select_db(MYSQL_DB, $dbcon);
mysql_query("SET time_zone='".MYSQL_ZONE."'");
