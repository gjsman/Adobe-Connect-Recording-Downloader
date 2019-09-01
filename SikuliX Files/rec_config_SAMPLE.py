

#!/usr/bin/python
##########################################################################
#
#   Unless otherwise noted, code in this file is copyright (C) 2017 Capitol Technology University
#	Kenneth Mayer 
#	distlearn@captechu.edu 
#	ken.i.mayer@gmail.com
#
#########################################################################

"""This script collects all the institution-dependent variables used by AdobeRec.py and puts them in a dictionary object
  called "const".
  Values that need replacing are called #REPLACE#
  
""" 

const = {
	# where this import script will log errors
	'logfile':'C:\errors\SikuliErrors',
		
	#the API URL for your Adobe Connect instance
	'BASE_URL': "https://REPLACEME.adobeconnect.com/api/xml?action=",
	
	# Adobe account and login
	'login' : "login&login=#REPLACE#&password=#REPLACE#",
		
	# the email address used for sending error alert emails from this server
	'me' : '#REPLACE#@#REPLACE#',
	
	# the email address that receives alert emails
	'you' : '#REPLACE#@#REPLACE#',
	
	# the SMTP server 
	'SMTP' : 'localhost:25',
	
	#mail_login
	'mail_log' : '#REPLACE#',
	
	#mail_pass
	'mail_pass' : '#REPLACE#',
	
	# Highest duration of class (4 and 1/2 hours, longer recordings are assumed to be errors
	# caused by professors not logging out of the class URL for the PHP program to create courses in Adobe Connect
	'maxDuration' : 15300,
	
	# MySQL account
	 'mySQLaccount' : '#REPLACE#',
	 
	# MySQL database
	'mySQL' : "jdbc:mysql://localhost/#REPLACE#",
	
	# MySQL password
	'mySQLpass' : "#REPLACE#",
	
	# service PHP URL
	'PHP_URL' : "https://#REPLACE#/service.php?dist=#REPLACE#",
	
	# Server for calls
	'server' : "#REPLACE#"
	}
