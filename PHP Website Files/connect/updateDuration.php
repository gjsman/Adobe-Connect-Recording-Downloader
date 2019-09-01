<?php ini_set('display_errors', 'On');
/*

 Unless otherwise noted, code in this file is copyright (C) 2017 Capitol Technology University
 Kenneth Mayer 
 distlearn@captechu.edu 
 ken.i.mayer@gmail.com
*/
error_reporting(E_ALL);

$debug = false;
$table_disp = false;
include '../common/int_config.php';
include '../common/adobe.php';
include '../common/sess.php';

if (isset($_REQUEST['debug']) && $_REQUEST['debug']=='yes')
	$debug = true; 

if ($_REQUEST['debug']=="table")
	$table_disp = true;
if ($debug || $table_disp)
	{
	Print('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">');
	Print("<html>");
	Print("<head></head><body>");
	}

$GLOBALS['qs'] = $_SERVER['QUERY_STRING'];
session_start();
	
$acc = new AdobeConnectClient();
//$s = $_REQUEST['fromdate'];
$query = mysql_query("select download_from_date from cc_configdetails");
while($info = mysql_fetch_array($query)) 
	{ 
		$from_date = $info['download_from_date'];
	}
  if(trim($from_date) !="0000-00-00")
	  $from_date = $from_date;
  else 
	  $from_date = date('Y-m-d', strtotime(date("Y-m-d") .' -1 day'));
  if ($debug) print "<pre>FROM DATE: ".$from_date."</pre>";
  $api_rqst =  $acc->getAllMeetings('report-bulk-objects&filter-icon=archive&filter-gt-date-created='.$from_date);
  if ($debug) 
	  {
	  print('<pre>QUERY: report-bulk-objects&filter-icon=archive&filter-gt-date-created='.$from_date."</pre>");
	  Print("<pre>VAR DUMP: report-bulk-objects:</pre>");
	  print var_dump($api_rqst);
	   }

  $total_records = $api_rqst["report-bulk-objects"]["row"];
  $count = 0;
  if(isset($total_records))
	{
		if ($debug) 
		{Print("<pre>VAR DUMP: total_records:</pre>");
		print var_dump($total_records);
		 }

		if(is_array($total_records[0]))
		{
			for($i=0;$i<sizeof($total_records);$i++)
			{
				$name_of_folder = $total_records[$i]['name'];
				$date_created = $total_records[$i]['date-created'];
				$date_end = $total_records[$i]['date-end'];
				$date_created_sub =  substr($date_created, 0, strpos($date_created, "."));
				$date_end_sub =  substr($date_end, 0, strpos($date_end, "."));
				
				$date1 = new DateTime($date_created_sub);
				$date2 = new DateTime($date_end_sub);
				
				$date_end_sub = $date2->format('Y-m-d H:i:s');
				$date_created_sub = $date1->format('Y-m-d H:i:s');
				$duration = strtotime($date_end_sub) - strtotime($date_created_sub);
				$duration = $duration+ 300;
				$url = ADOBE.$total_records[$i]["url"]."?pbMode=offline";
				$sco_id_url = $total_records[$i]["@attributes"]["sco-id"];
				$sco_id = $total_records[$i]["@attributes"]["sco-id"];
				//
				// let's get the true duration from a bunch of other API calls
				//
				if ($debug) 
					{Print("<pre>Sco-id: ".$sco_id."</pre>");
		 			}

				$folder_sco ="0";
				$new_duration = 0;
				$folder_scoData =  $acc->getFolderName($sco_id);
				$folder_sco = $folder_scoData["@attributes"]["folder-id"];
				if ($debug) 
					{Print("<pre>VAR DUMP Folder ScoData: ".$folder_scoData."</pre>");
					print var_dump($folder_scoData);
						Print("<pre>Folder Sco-id: ".$folder_sco."</pre>");
		 			}
				$duration_Data = $acc->getAllMeetings('sco-contents&sco-id='.$folder_sco.'&filter-sco-id='.$sco_id);
						if ($debug) 
							{Print("<pre>VAR DUMP duration_Data[scos]: </pre>");
							print var_dump($duration_Data["scos"]);
		 					}

					if(isset($duration_Data["scos"]["sco"]))
						{
						if ($debug) 
							{Print("<pre>Duration (raw): ".$duration_Data["scos"]["sco"]["@attributes"]["duration"]."</pre>");
		 					}
						$new_duration = intval($duration_Data["scos"]["sco"]["@attributes"]["duration"]);
						$hr = intval($new_duration/3600);
        				$min = intval(($new_duration % 3600)/60);
        				if ($min<10) $min = "0".$min;

						
					}
				if ($debug) 
						{Print("<pre>New Duration (raw): ".$new_duration." = ".$hr.":".$min."</pre>");
		 				}
				if ($new_duration > 0) $duration = $new_duration;
				$duration = abs($duration);
				$hr = intval($duration/3600);
        		$min = intval(($duration % 3600)/60);
        		if ($min<10) $min = "0".$min;

				if ($debug) 
					{Print("<pre>Duration (adjusted): ".$duration." = ".$hr.":".$min."</pre>");
		 			}
				
				$rc_name = 	$total_records[$i]["name"];	
				
				if ($table_disp)
					{ Print("<pre>".$rc_name.",".$hr.":".$min."</pre>");
					}
					
				// Adobe automatically replaces these characters with an underscore when saving
				// file names. We need to replace them in the stored recording name or we cannot
				// locate the files later
				$reserved_chars = '\/:*?"<>|';	
				for ($j=0;$j<strlen($reserved_chars)-1; $j++)
					{
						$char = substr($reserved_chars,$j,1);
						$rc_name = str_replace($char,"_",$rc_name);
					}
				$icon_type = $total_records[$i]["@attributes"]["icon"];
				$attributes = '';
				$meeting_name = '';
				$meeting_url = '';
				for($s=0;$s<5;$s++)
				{
					if($icon_type != "folder")
					{
						$attributes = $acc->getFolderName($sco_id);
						$sco_id = $attributes["@attributes"]["folder-id"];
						$icon_type = $attributes["@attributes"]["icon"];
						if($icon_type == "meeting")
						{
							$meeting_name = $attributes['name'];
							$meeting_url = $attributes['url-path'];
							break;
						}
						
					}
					else 
					{
						
						break;
					} 
				}
				$folder_name = substr($meeting_url, 1, -1 );
				$query = "INSERT INTO cc_recordings_in_ac (scoid,foldername,url,datecreated,meetingurl,meetingname,recordingname, duration) VALUES ('$sco_id_url','$folder_name','$url','$date_created', '$meeting_url', '$meeting_name', '$rc_name', '$duration')";

				if(mysql_query($query)) 
					{ $count++; }
				else
					{
					$query = "UPDATE cc_recordings_in_ac SET duration ='$duration' WHERE scoid ='$sco_id_url'";
					if(mysql_query($query)) 
						{ $count++; }
					}
				if ($debug) 
					{Print("<pre>SEARCH: ".$query."</pre>"); 
					Print("<pre>I: ".$i." Count: ".$count."</pre>");
					Print "<pre>mysql_info(): ".mysql_info()."</br>"; 
					Print "<pre>Affected rows: ".mysql_affected_rows()."</pre>";
				}
			}
		}
		else
		{
			$name_of_folder = $total_records["name"];
			$date_created = $total_records["date-created"];
			$date_end = $total_records["date-end"];
			$date_created_sub =  substr($date_created, 0, strpos($date_created, "."));
			$date_end_sub =  substr($date_end, 0, strpos($date_end, "."));
			
			$date1 = new DateTime($date_created_sub);
			$date2 = new DateTime($date_end_sub);
			
			$date_end_sub = $date2->format('Y-m-d H:i:s');
			$date_created_sub = $date1->format('Y-m-d H:i:s');
			
			
			$duration = strtotime($date_end_sub) - strtotime($date_created_sub);
			$duration = $duration+ 300;
			
				$url = ADOBE.$total_records["url"]."?pbMode=offline";
				$sco_id_url = $total_records["@attributes"]["sco-id"];
				$sco_id = $total_records["@attributes"]["sco-id"];
				$rc_name = 	$total_records["name"];															
				$icon_type = $total_records["@attributes"]["icon"];
				$attributes = '';
				$meeting_name = '';
				$meeting_url = '';
			for($s=0;$s<5;$s++)
			{
				if($icon_type != "folder")
				{
					$attributes = $acc->getFolderName($sco_id);
					$sco_id = $attributes["@attributes"]["folder-id"];
					$icon_type = $attributes["@attributes"]["icon"];
					if($icon_type == "meeting")
					{
						$meeting_name = $attributes['name'];
						$meeting_url = $attributes['url-path'];
						break;
					}
					
				}
				else 
				{
					
					break;
				} 
			}
			$folder_name = substr($meeting_url, 1, -1 );
			$query = "INSERT INTO cc_recordings_in_ac (scoid,foldername,url,datecreated,meetingurl,meetingname,recordingname, duration) VALUES ('$sco_id_url','$folder_name','$url','$date_created', '$meeting_url', '$meeting_name', '$rc_name', '$duration')";
				if ($debug) 
					{Print("<pre>SEARCH: ".$query."</pre>"); 
					Print("<pre>Only one record</pre>");}
				if(mysql_query($query))
					$count++;
		}
	}
	else
	{
		$_SESSION['recs'] = "No records found...";
	}
	
	$new_search = chr(34)."%201___0%".chr(34);
	// $new_search = "INSERT INTO cc_recordings_saved (scoid) SELECT scoid FROM cc_recordings_in_ac WHERE scoid not in (SELECT scoid FROM cc_recordings_saved) ";
	// old search:
	$new_search = "INSERT INTO cc_recordings_saved (scoid) SELECT scoid FROM cc_recordings_in_ac where scoid not in (select scoid from cc_recordings_saved) and meetingname like ".$new_search;
	
	if (mysql_query($new_search))
		{
			$_SESSION['recs'] =  mysql_affected_rows()." records inserted.";
		}
	else
		{
			$_SESSION['recs'] = "Failure! ".mysql_error();
		}
	if ($debug) 
		{Print "<pre>SEARCH: ".$new_search."</br>"; 
		Print "<pre>mysql_info(): ".mysql_info()."</br>"; 
		Print "<pre>Affected rows: ".mysql_affected_rows()."</pre>";
		
		}
		
	// try to update the NULL status to "P" status
	$query = "UPDATE cc_recordings_saved SET status = 'P', datedownloaded = NULL WHERE status = NULL";
		if ($debug) 
		{Print "<pre>SEARCH: ".$query."</pre>"; }

	$data =mysql_query($query);
	if ($debug)
		{
		$i = 0;
		if(!empty($data))
			{
			while($info = mysql_fetch_array( $data )) 
				{ $i = $i+1;
				}
			}
		Print "<pre>Status Changes: ".$i."</pre>";
		}

	
	session_write_close();
	$GLOBALS['qs'] = $GLOBALS['qs']."&recs=".$count;
	if ($debug || $table_disp)
		{ Print "<pre>".var_dump($_REQUEST)."</pre>";
		 Print "<pre>".var_dump($GLOBALS)."</pre>";
		}
		else
		{
		header("Location: http://".$_SERVER['HTTP_HOST']."/connect/reports.php?".$GLOBALS['qs']);
		}
	
?>
