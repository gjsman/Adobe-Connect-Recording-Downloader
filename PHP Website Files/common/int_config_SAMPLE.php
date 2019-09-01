<?php
/*

 Unless otherwise noted, code in this file is copyright (C) 2017 Capitol Technology University
 Kenneth Mayer 
 distlearn@captechu.edu 
 ken.i.mayer@gmail.com

 Addentum Copyright (C) 2019 Gabriel Sieben
*/
/*
Constants to be configured for each client
 Values that need to be replaced are marked #REPLACE#
 */ 

// email 'From' address that this program will use to send messages and the authorization username and password
const INST_MAIL = '#REPLACE#@#REPLACE#';
// email 'To' address for monitoring errors and reports
const INST_TO = '#REPLACE#';

const mail_user = '#REPLACE#';
const mail_pass = '#REPLACE#';
const mail_server = '#REPLACE#';
const mail_port = '25';

// Dept name is displayed if there are problems, also used in 
const INST_DEPT = 'Distance Learning Services#REPLACE#';
const INST_DOMAIN = '#REPLACE#'; // for mail addresses
// directory for Error logs
const INST_ERROR = "C:\\errors\\";

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
// The Meetings Folder  SCO  <-- inside which we will do an expanded SCO search for meetings/courses
const ADOBE_MFOLD = '#REPLACE#';

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