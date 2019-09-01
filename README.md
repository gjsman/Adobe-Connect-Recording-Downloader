# Adobe Connect Recordings Downloader
GitHub user @AnnapolisKen wrote these scripts which can be set up to automatically download MP4 files of Adobe Connect recordings. Sadly, these scripts haven't been updated to work on modern platforms and use programming languages which are 6-7 years old. This is my attempt to modernize these scripts and bring them to life again.

Target software (still in development):
•	Windows Server 2016
•	Amazon Corretto JRE 11
•	Python 3.7
•	HTML5
•	SikuliX 1.1
•	PHP 7.3 (and mysql -> mysqli extension migration)
•	MariaDB

Please give thanks to [Homeschool Connections](https://homeschoolconnectionsonline.com) for funding these improvements.

# Old Readme
A collection of scripts that can be set up to automatically download MP4 files of Adobe Connect recordings.

This program finds new recordings in an Adobe Connect instance and automatically downloads the MP4 recordings. This program runs Adobe Connect and uses the Sikuli screenreader to interpret the recording and click buttons and enter info into input fields. Therefore it needs to be set up on a server with graphic user interface. I set it up on a Windows Server 2012 R2, with the following software
•	Python 2.7
•	Sikuli IDE 1.0.0 Win32/Win64
•	PHP 5.3 with PEAR extension
•	MYSQL
The PHP programs populate a MYSQL database. A Windows Batch file calls the Sikuli scripts that launch up to five recordings simultaneously in Adobe Connect, and then stores the recordings in folders named after the Adobe Connect meeting rooms. After it finishes downloading all the pending recordings in the hopper, it launches the PHP program to check for new recordings. It continues to check for new recordings every hour or two.

For PHP programs the institution-dependent constants are in the file int_config.php

For Python (Sikuli) programs the institution-dependent constants are in the file rec_config.py

After you edit the rec_config.py file, you need to take it and everything in the Sikuli Distribution folder (but not the folder itself!) and zip it to a zip file, then rename that file AdobeRec.skl, changing the file extension. Now you have your Sikuli file.