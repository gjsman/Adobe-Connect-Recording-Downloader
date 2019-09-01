<?php
/*

 Unless otherwise noted, code in this file is copyright (C) 2017 Capitol Technology University
 Kenneth Mayer 
 distlearn@captechu.edu 
 ken.i.mayer@gmail.com
*/
/*
Constants to be configured for each client
 The tool is designed on the assumption that the main components are located:
 https://'.$_SERVER['SERVER_NAME'].'/canvas/
 And authentication and configurations are located:
 https://'.$_SERVER['SERVER_NAME'].'/canvas/
 
 Values that need to be replaced are marked #REPLACE#
 */ 
// INSTITUTION Constants

// Help contant information for Canvas/Adobe Connect issues
const INST_NAME = '#REPLACE#';
// email 'From' address that this program will use to send messages and the authorization username and password
const INST_MAIL = '#REPLACE#@#REPLACE#';
// username for mail authorization
const INST_USER = '#REPLACE#';
// password for mail authorization
const INST_PWD = '#REPLACE#';
// email 'To' address for monitoring errors and reports
const INST_TO = '#REPLACE#';
// Mail SMTP host
const INST_HOST = '#REPLACE#';
// MAIL SMTP port
const INST_PORT = '465';

const mail_user = '#REPLACE#';
const mail_pass = '#REPLACE#';
const mail_server = '#REPLACE#';
const mail_port = '25';

// Phone and email are displayed if there are problems
const INST_PH = '#REPLACE#';
// Dept name is displayed if there are problems, also used in 
const INST_DEPT = 'Distance Learning Services#REPLACE#';
const INST_DOMAIN = '#REPLACE#'; // for mail addresses
// directory for Error logs
const INST_ERROR = "C:\\errors\\";
// Canvas logins of the admins with special privileges (separated by a space)
const INST_TECHS = 'distlearn kmtech #REPLACE#';

// course prefixes for courses which forbid downloading of recordings (separated by a space)
const NO_DOWNLOAD = 'icp- #REPLACE#';
// generic terms that match all courses that will be downloaded
const INST_DOWNLOAD = '_201|cae_tech_talk|#REPLACE#';

const lf = " \n\r\n"; // line feed


//ADOBE CONNECT CONSTANTS

// adobe connect admin username
const ADOBE_ADMIN = '#REPLACE#';
// adobe connect password
const ADOBE_PWD = '#REPLACE#';
// link for your institution's Adobe site
const ADOBE = 'https://#REPLACE#.adobeconnect.com/';
// your ADOBE api URL
const ADOBE_API = 'https://#REPLACE#.adobeconnect.com/api/';
// your root-folder id
const ADOBE_FOLD = 0; //root folder id
// your institution Brand name for Adobe Connect
// const ADOBE_BRAND = 'Adobe Connect'; // default
const ADOBE_BRAND = '#REPLACE#';
// The Meetings Folder  SCO  <-- inside which we will do an expanded SCO search for meetings/courses
const ADOBE_MFOLD = '#REPLACE#';
// this is the SCO of the folder storing deleted courses
const ADOBE_DFOLD = '#REPLACE#'; 
// this is the SCO of the folder storing unassigned courses (w/o an instructor or offline w/o named host)
const ADOBE_UFOLD = '#REPLACE#'; // username is testprof

// The local location of the downloaded MP4 recordings to be served as downloads
const ADOBE_RECS = "C:\\recordings\\";

//breakout host is the sco-id a host account used for leading breakouts
const ADOBE_BRK = '#REPLACE#'; // this username MUST BE 'breakout'

// Automatic adds --SCOs of deans, integrator, and admins who are automatically enrolled in courses in ADOBE (separated by space)
	// adding the sco for the Integrator 'inst_1143954498'
	// adding the sco for the Deans 'inst_1143253815'
	// adding the sco for the Administrators 'inst_1141159121'
	// adding the sco for the  breakout leader host account  'inst_1168433358'
const ADOBE_ADM = '#REPLACE# #REPLACE# #REPLACE#';

// This text displays at the bottom of the integration page--can be links for help:
const INT_TEXT = " <p> &nbsp </p><p><a target='_blank' href='http://#REPLACE#' >Click here for general help with Adobe Connect</a></p>	
        <p><a target='_blank' href='#REPLACE#' >Click here for help with Adobe Connect Mobile.</a></p>	
        <p><a target='_blank' href='#REPLACE#' >Click here to run an Adobe Connect Connection test, that determines if your system is set up for Connect.</a></p>	";

// PHP CONSTANTS

// PHP Dist password
// In a request variable for the PHP pages ?dist=#PHP_pass
const PHP_pass = '#REPLACE#';

// MYSQL CONSTANTS

// MYSQL Username
const MYSQL_USER = '#REPLACE#';
// MYSQL Password
const MYSQL_PWD = '#REPLACE#';
// MYSQL Database
const MYSQL_DB ='ac_db';
// MYSQL Time zone
const MYSQL_ZONE = 'EST5EDT';



// PHP Constants

//time zone
const ZONE= 'America/New_York'; // See the MYSQL_ZONE constant above as well for Daylight savings time
date_default_timezone_set(ZONE);