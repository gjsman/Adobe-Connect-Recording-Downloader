from datetime import datetime
import os
import sys
##########################################################################
#
#   Unless otherwise noted, code in this file is copyright (C) 2017 Capitol Technology University
#	Kenneth Mayer 
#	distlearn@captechu.edu 
#	ken.i.mayer@gmail.com
#
#########################################################################
curDateTime = datetime.now().strftime('%Y-%m-%d_%H_%M_%S')
# Keep the config secrets in a safe directory
exec open("C:\\Python27\\Lib\\rec_config.py").read()
#from rec_config import const
newName = const['logfile']+'_' + curDateTime + '.log'
try:
	os.rename(const['logfile']+'.log', newName)
except:
	newName = 'but failed : ' + newName
import logging
logging.basicConfig(filename=const['logfile']+'.log',level=logging.DEBUG, format='%(asctime)s %(message)s')


logging.debug('-----Sikuli file: AdobeRec.skl -------------')
#logging.debug('const[you]: '+ const['you'] )

logging.debug('Archived log ' + newName)
from com.ziclix.python.sql import zxJDBC
# connect to MYSQL database 
con = zxJDBC.connect(const['mySQL'], const['mySQLaccount'], const['mySQLpass'], "com.mysql.jdbc.Driver")
cur = con.cursor()

def getRootPath():
	cur.execute("SELECT root_path,number_of_instances FROM cc_configdetails")
	result=cur.fetchone()
	return result
	
def openPermit(filename):
	#
	# icacls %1 /t /grant Everyone:F
	#
	#logging.debug('Granting permissions for %s ', filename)
	try:
		result = os.popen("icacls " + '"' + filename + '"' + " /t /grant Everyone:F" )
		#logging.debug('Granting success ')
		return 1
	except:
		logging.debug("OpenPermit generates error %s for %s %s",  sys.exc_info()[0], filename, sys.exc_info()[1] )
		return 0
		
def sendWarn(name,seconds) :
	you = const['you']
	subj = ''
	if (seconds>0):
		hours = int(seconds/360)
		mins = int((seconds-(hours*360))/60)	
		body = "<p>The Recording server tried to record a recording named <b>"+name+ "</b> with a total of "+str(seconds)+" seconds.<br /> Totalling "+str(hours)+" hours and "+ str(mins)+"minutes.</p>"
		subj = "Recording Longer than 4.5 hours --" + name
		html = "<html><head>"
		html = html + "<style>td {vertical-align:top}</style>"
		html = html + "</head><body>" + body + "</body></html>"
	try:
		logging.debug("sending the warning email")
		sys.argv = ['pythonMailer.py',you, subj, html]
		execfile(r'C:/canvas/pythonMailer.py')
	except:
		logging.debug("Execfile sendWarn generates error %s -- %s",  sys.exc_info()[0], sys.exc_info()[1] )
		logging.exception('')
		#logging.debug("exception details: " + e.args)


def mailAlert() :
	you = const['you']
	body = "The Recording server had eight errors in a row.<br /> Probably there is a malfunction at the server level. <br />Please log in and check it out."
	subj = "RESTART the Recording Server"
	html = "<html><head>"
	html = html + "<style>td {vertical-align:top}</style>"
	html = html + "</head><body>" + body + "</body></html>"
	try:
		logging.debug("Shutting down and sending email alert: 12 errors in a row")
		sys.argv = ['pythonMailer.py',you, subj, html]
		execfile(r'C:/canvas/pythonMailer.py')
	except:
		logging.debug("Execfile mailAlert generates error %s -- %s",  sys.exc_info()[0], sys.exc_info()[1] )
		logging.exception('')
		#logging.debug("exception details: " + e.args)
		
def closeOneWindow(recordsCount, name =''):
	try:
		Apps[recordsCount].close()
	except:
		print("Error in rec # %s: No App named: %s", name, Apps[recordsCount])
		logging.debug("Error in rec # %s: No App named: %s", name, Apps[recordsCount])
	setThrowException(False)
	App.close("Flash Player")
	setThrowException(True)
	
def addRecordings(recordsResult,recordsCount,rootPath):
	global Apps
	startTimeArray = []
	duration =""
	scoid = ""
	text = ""
	if(recordsCount<len(recordsResult)):
		if(recordsResult[recordsCount][0] == "" or recordsResult[recordsCount][0] == " "):
			folderpath = ""
		else:	
			folderpath =recordsResult[recordsCount][0]+"\\"
		scoid = recordsResult[recordsCount][4]
		duration =recordsResult[recordsCount][5]
		if duration<0:
			duration = abs(duration)
		fileNames[scoid] = rootPath + folderpath + recordsResult[recordsCount][3] + "_0.mp4"
		print("adding "+ fileNames[scoid])
		cmd1 = 'start /B \"\" \"'
		cmd2 ='\"'
		os.popen(cmd1+ recordsResult[recordsCount][1] +cmd2)
		logging.debug('Recording # %s: %s', recordsCount, recordsResult[recordsCount][3])
		focus_name = recordsResult[recordsCount][3]
		focus_name = focus_name[:14]

		wait (10)
		if exists("ReLogin.png"):
			logging.debug('Lost log in to Adobe Connect. Trying to reset.')
			print('Trying to relog into Adobe Connect')
			loginAdobe()
			os.popen(cmd1+ recordsResult[recordsCount][1] +cmd2)
			logging.debug('Recording # %s: %s', recordsCount, recordsResult[recordsCount][3])
		if exists("LoadingStall.png"):
			if exists("reload_chrome.png"):
				click("reload_chrome.png")
				wait(20)
			elif exists("LoadingStall_close.png"):
				click("LoadingStall_close.png")
				wait(5)
				if exists("reload_chrome.png"):
					click("reload_chrome.png")
					wait(20)
		if exists("InstallAdd-in.png"):
				click("InstallAdd-in.png")
				wait(10)
				if exists("AdobeConnect.png"):
					click("YES-4.png")
					#wait(70)
		else:
			if exists("InstallAddin-1.png"):
				click("InstallAddin-1.png")
				wait(10)
				if exists("AdobeConnect.png"):
					click("Yes-1.png")

		Adobe_Wait = 0
		while Adobe_Wait <21 :
			wait(10)
			if exists("Adobe_Connecting.png"):
				Adobe_Wait = Adobe_Wait +1					
				# wait another 10 to see if that disappears
				logging.debug('Adobe Connecting wait %s', Adobe_Wait *10)	
			else :
				Adobe_Wait =  25
		if Adobe_Wait < 20 :
			# The wait timed out at 200 seconds and it still hasn't connected
			logging.debug('Wait has timed out for %s', recordsResult[recordsCount][3])
			print('Wait has timed out after 200 seconds. Trying other record.')
			App.close("Flash Player")
			App.close("Adobe Connect")
			App.close(focus_name)			
			return 0
		# Otherwise, Adobe has connected and is displaying the recording screen
		wait(5)
		# This should focus on the Adobe window which just opened
		try:
			Apps[recordsCount] = App.focus(focus_name)
		except:
			logging.debug('Adobe window missing for %s', recordsResult[recordsCount][3])
			print('Adobe window missing')
			App.close("Flash Player")
			App.close("Adobe Connect")		
			return 0
		
		# This command (Window + Shift + UP ) should bring Adobe Recording window to top of screen
		type(Key.UP, KeyModifier.WIN + KeyModifier.SHIFT)
		
		# The following commands should move the Adobe Recording window to the left
		type(Key.SPACE, KeyModifier.ALT)
		type("M")
		type(Key.LEFT + Key.LEFT + Key.LEFT + Key.LEFT + Key.LEFT + Key.LEFT + Key.LEFT + Key.LEFT + Key.LEFT  )
		type(Key.ENTER)
	
		# This command (Window +  UP ) should maximize Adobe Recording window to fullscreen
		#type(Key.UP, KeyModifier.WIN)
		setThrowException(False)
		wait("Next.png",65)
		setThrowException(True)
		if exists("Next.png"):
			setThrowException(False)
			click("Next.png")
			# This command (Window + Shift + UP ) should bring Adobe Recording window to top of screen
			#type(Key.UP, KeyModifier.WIN + KeyModifier.SHIFT)
			wait ("ProceedwithO-5.png", 60)
			setThrowException(True)
			if exists("ProceedwithO-5.png"):
				click("ProceedwithO-5.png")
				wait(4)
			else:
				logging.debug('No Proceed With Online Recording button found for %s', recordsResult[recordsCount][3])
				#try to close the dead Adobe window
				#This keyboard shortcut closes the window
				#type(Key.F4, KeyModifier.ALT)
				closeOneWindow(recordsCount, recordsResult[recordsCount][3])
				return 0
		else:
			logging.debug('No NEXT button found for %s', recordsResult[recordsCount][3])
			if exists("NotFound.png"):
				return -1
			#try to close the dead Adobe window
			#This keyboard shortcut closes the window
			#type(Key.F4, KeyModifier.ALT)
			closeOneWindow(recordsCount, recordsResult[recordsCount][3])
			return 0
		if exists("CloseTheProgram.png"):
			click("CloseTheProgram.png")
			logging.debug('Adobe Add-in has stopped working')
			#type(Key.TAB,KeyModifier.ALT)
			closeOneWindow(recordsCount, recordsResult[recordsCount][3])
			return 0			
		if exists("NotAuthorized.png") or exists("NotAuthorized_Adobe_WS2012.png") or exists("NotAuthorize.png"):
			curDateTime = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
			cur.execute("""UPDATE cc_recordings_saved SET status =?,datedownloaded =? WHERE scoid =?""",('PROB',curDateTime,scoid))
			logging.debug('Not authorized to open and record %s', fileNames[scoid])
			#type(Key.F4, KeyModifier.ALT)
			closeOneWindow(recordsCount, recordsResult[recordsCount][3])
			return 0			
			wait(5)
		else:
			if not os.path.exists(rootPath+folderpath):
				os.makedirs(rootPath+folderpath)
				wait(10)
			type(Key.HOME)
			type(rootPath+folderpath) 
			if exists('save.png'):
				click('save.png')
			elif exists('Save_WS2012.png'):
				click('Save_WS2012.png')
			elif exists('Save-6.png'):
				click('Save-6.png')
			elif exists('Save-3.png'):
				click('Save-3.png')
			elif exists('Save-1.png'):
				click('Save-1.png')
			setThrowException(False)
			wait('ConfirmSaveWS2012_2.png',15)
			setThrowException(True)
			if exists('ConfirmSaveWS2012_2.png'):
				click('Yes_WS2012.png') 
				wait(5)
			starttime_data[scoid] = duration
			print(starttime_data)
			if exists('CloseTheProgram.png'):
				click('CloseTheProgram.png')
				logging.debug('Adobe Add-in has stopped working')
				closeOneWindow(recordsCount, recordsResult[recordsCount][3])		
				return 0
			# This command (Window + Shift + UP ) should bring Adobe Recording window to top of screen
			#type(Key.UP, KeyModifier.WIN + KeyModifier.SHIFT)
		return 1
	
def browserWinsClose(browTot):
	closer = 0
	setThrowException(False)
	while closer<browTot :
		App.close('Internet Explorer')
		App.close('Firefox')
		App.close('Chrome')
		#App.close('Adobe')  #don't close this in this sub!
		App.close('Flash Player')

		closer = closer + 1
		#logging.debug('closing browser ... %s', closer)
	App.close('Internet Explorer')
	App.close('Firefox')
	App.close('Chrome')
	#App.close('Adobe') #don't close this in this sub!
	App.close('Flash Player')
	setThrowException(True)

def closeTabs(recordsResult,rootPath,Installations):
	global Apps
	errCount = 0
	curDateTime = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
	cur.execute("""UPDATE cc_configdetails SET status_check =?, root_path =?""",(curDateTime,rootPath))

	try:
		durationList=[]
		ScoidList = []
		durationList=starttime_data.values()
		if len(durationList)<1 :
			logging.debug('No active recordings found. Trying again')
			browserWinsClose(15)
			setThrowException(False)
			App.close("Adobe Connect")
			setThrowException(True)
			return
		highestDuration = max(durationList)
		print("Duration List")
		print(durationList)
		ScoidList = starttime_data.keys()
		print("ScoIdList")
		print(ScoidList)
		print("highestDuration")
		print(highestDuration)
		if(highestDuration>const['maxDuration']):
			print("A recording is longer than 4 hrs and 15 minutes. It will be cut off.")
			logging.debug("A recording is longer than 4 hrs and 15 minutes.")
			# If the recording is longer than 4hrs 15 min, then stop it at 4hrs 15 min.
			# This way we don't tie up the recording server for 9 or 10 hrs for empty space.
			highestDuration = const['maxDuration']
		minutesLeft = int((highestDuration)/60)
		logging.debug('Need to wait %s minutes.', minutesLeft)
		print('Have to wait %s minutes', minutesLeft)
		browserWinsClose(15)
	except:
		logging.debug('Error encountered in CloseTabs() PART I %s', sys.exc_info()[0])
		logging.exception('')
		print('Unexpected error: CloseTabs PART I %s',  sys.exc_info()[0] )
		errCount = 1
	timer = 0
	while timer<(highestDuration-700):
		# post status every 10 minutes
		wait(600) 
		curDateTime = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
		cur.execute("""UPDATE cc_configdetails SET status_check =?, root_path =?""",(curDateTime,rootPath))
		timer = timer +600
		minutes = int(timer/60)
		minutesLeft = int((highestDuration-timer)/60)
		logging.debug('waited %s minutes and %s minutes to go.',minutes, minutesLeft)
		print('waited ',minutes,' minutes and have to wait another ', minutesLeft, 'minutes')
		if exists('CloseTheProgram.png'):
			click('CloseTheProgram.png')
			logging.debug('Adobe Add-in has stopped working')
	remainder = highestDuration-timer
	logging.debug('waiting the last %s seconds', remainder)
	wait(remainder)
	logging.debug('closing all Adobe Windows')
	#logging.debug('Installations: %s', Installations)
	closer = 0

	while closer<Installations :
		try:
			#logging.debug('closing Adobe %s', closer)
			region = Apps[closer].window()
		except:
			#logging.debug('no Apps[closer].window()')
			closer = closer + 1
			continue
		try:
			Apps[closer].focus()
			#logging.debug('Setting the ROI')
			#setROI(Apps[closer].window())
			#logging.debug('done setting the ROI')
			setThrowException(False)
			wait('OK.png',15)
			setThrowException(True)
			if exists('OK.png'):
				click('OK.png')
			else:
				# This command (Window + Shift + UP ) should bring Adobe Recording window to top of screen
				type(Key.UP, KeyModifier.WIN + KeyModifier.SHIFT)

				if exists('StopAndSaveWS2012.png'):
					# if that isn't greyed out, we are stopping the recording early because it is
					# over the time limit
					logging.debug('Clicking the StopAndSaveButton')
					#setThrowException(False) # no exception raised, not found returns None
					click('StopAndSaveWS2012.png')
					setThrowException(False)
					wait('OK.png',25)
					setThrowException(True)
			#This keyboard shortcut closes the window
			#type(Key.F4, KeyModifier.ALT)
		except:
			logging.debug("Error encountered in CloseTabs() PART II %s",  sys.exc_info()[0] )
			logging.exception('')
			print("Unexpected error: CloseTabs PART II %s",  sys.exc_info()[0] )
			errCount = 1
			return errCount
		try:
			name = recordsResult[closer][3]
		except:
			name = ""
		closeOneWindow(closer, name)

		closer = closer + 1
	curDateTime = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
	logging.debug(curDateTime  + ' Successfully closed all Adobe windows')
	closer = 0
	for i in range(0,len(ScoidList)) :
		tempName = fileNames[ScoidList[i]]
		#logging.debug('Checking file %s', tempName)
		try:
			if not os.path.isfile(tempName) :
				# first try switching MP4 or FLV
				if tempName.find(".mp4")>0 :
					tempName = tempName.replace(".mp4",".flv")
				elif tempName.find(".flv")>0 :
					tempName= tempName.replace(".flv",".mp4")
				logging.debug('Checking file %s', tempName)
				if not os.path.isfile(tempName) :
					# next try changing quote mark
					if tempName.find("'")>0 :
						tempName = tempName.replace("'","%27")
						logging.debug('File name %s has a single quote in it', tempName)
					else:
						# other quote mark
						if tempName.find("%27")>0 :
							tempName = tempName.replace("%27","'")
							logging.debug('File name %s has a single quote in it', tempName)
					logging.debug('Checking file %s', tempName)
					if not os.path.isfile(tempName) :
						# again try switching MP4 or FLV 
						if tempName.find('.mp4')>0 :
							tempName = tempName.replace('.mp4','.flv')
						elif tempName.find('.flv')>0 :
							tempName= tempName.replace('.flv','.mp4')
						if not os.path.isfile(tempName) :
							# NO LUCK -- fail!
							logging.debug('No recording file found %s --labeled PROBLEMATIC', tempName )
							cur.execute("""UPDATE cc_recordings_saved SET status =? WHERE scoid =?""",('PROB',ScoidList[i]))
							tempName='FAIL'
			# successful!
			if not tempName =='FAIL' :
				closer = closer + 1
				cur.execute("""UPDATE cc_recordings_saved SET status =?,datedownloaded =? WHERE scoid =?""",('D',curDateTime,ScoidList[i]))
				if openPermit(tempName)==1 :
					# make sure that this works for recordings longer than 4.5 hours
					if starttime_data[ScoidList[i]]>const['maxDuration']:
						sendWarn(tempName, starttime_data[ScoidList[i]])
						starttime_data[ScoidList[i]] = const['maxDuration']
					size = os.path.getsize(tempName)
					logging.debug('File size for %s : %d bytes for %d length', tempName, size, starttime_data[ScoidList[i]] )
					if size<(4000*starttime_data[ScoidList[i]]) and starttime_data[ScoidList[i]]>600 :
						# when the recording length is less than 10 minutes, the math is little different
						logging.debug('File size for %s is far too small at %d bytes for %d seconds length', tempName, size, starttime_data[ScoidList[i]] )
						cur.execute("""UPDATE cc_recordings_saved SET status =? WHERE scoid =?""",('PROB',ScoidList[i]))
						closer = closer -1
					else: 					
						logging.debug('Successful recording of %s with %s bytes', tempName, size)
				else:
					logging.debug('OpenPermit generates error %s for %s',  sys.exc_info()[0], tempName)
					logging.debug('Presumed successful recording of %s with %s bytes', tempName, 'unknown')
		except:
			logging.debug('Error encountered in CloseTabs() PART III %s ', sys.exc_info()[0])
			logging.exception('')
			print('Unexpected error: CloseTabs PART III %s',  sys.exc_info()[0] )
			errCount = 1
	logging.debug('All recordings accounted for, %s recordings marked successful', closer)
	# grant all permissions to all recordings
	try:
		result = os.popen("icacls C:/recordings/ /t /grant Everyone:(OI)(CI)F /inheritance:e" )
		#logging.debug('All Recording permissions updated')
	except:
		logging.debug('icacls Permissions error: %s ',  sys.exc_info()[0] )
		errCount = 1
	# close all Adobe windows
	try:
		App.close("Adobe Connect")
		App.close("Adobe Connect")
		App.close("Adobe Connect")
		App.close("Adobe Connect")
		App.close("Adobe Connect")
	except:
		print("Yeah, the App.close crashed again.")
	#logging.debug('getting more records')
	return errCount

def loginAdobe():
	print('Logging into Adobe Connect')
	cmd1 = r'start /B "" "' +const['BASE_URL'] +r'login^&login=integrator^&password=none"'
	os.popen(cmd1)
	wait(15)
	
def getRecords():
	global starttime_data 
	global fileNames 
	global Apps
	#global const
	problems = 0
	errTot = 0
	try:
		root_Installations=getRootPath()
		rootPath = root_Installations[0]
		Installations = root_Installations[1]
		#logging.debug('Installations: %d ', Installations)
		loop = 1
		curDateTime = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
		cur.execute("""UPDATE cc_configdetails SET status_check =?, root_path =?""",(curDateTime,rootPath))

		while loop ==1:
			logging.debug('Getting %s more records. Problems in a row: %s',Installations, problems)
			print('Getting pending recordings')
			fileNames = {}
			Apps = {}
			starttime_data = {}
			recordsCount = 0
			cur.execute("SELECT foldername,url,meetingname,recordingname,cs.scoid,duration FROM cc_recordings_in_ac cr INNER JOIN cc_recordings_saved cs ON cs.scoid = cr.scoid WHERE cs.status = ? order by duration ASC LIMIT ?",('P',Installations))
			recordsResult = cur.fetchall()
			if(len(recordsResult)>0):
				recordsCount= len(recordsResult)
				# if the longest recording is longer than 2 and 1/2 hours, check for more records
				# this ensures that records will be checked
				if recordsResult[recordsCount-1][5]> 9000 :
					cmd1 = r'start /B "" "'+const['PHP_URL']+'"'
					print(cmd1)
					os.popen(cmd1)
					wait(90)
					setThrowException(False)
					App.close("Internet Explorer")
					App.close("Firefox")
					App.close("Chrome")
					setThrowException(True)
				loginAdobe()
				for i in range(0,len(recordsResult)):
					recordsCount=recordsCount-1
					success=addRecordings(recordsResult,recordsCount,rootPath)
					if success==1 :
						problems = 0
					if success==0 :
						#failure to start recording
						del recordsResult[recordsCount]
						problems = problems + 1
					if success==-1 :
						#failure to find recording
						scoid = recordsResult[recordsCount][4]
						cur.execute("""UPDATE cc_recordings_saved SET status =?,datedownloaded =? WHERE scoid =?""",('PROB',curDateTime,scoid))
						logging.debug('Recording marked problematic')
						del recordsResult[recordsCount]
				if problems == 8 :
					# we have a serious failure and need to alert the admin
					# probably a Microsoft Windows update or other server crisis
					logging.debug('Sending alert--over 7 problem recordings in a row')
					mailAlert()
					browserWinsClose(15)
				if problems > 12 :
					# close all windows, close all programs
					logging.debug('CLOSING--over 12 problem recordings in a row')
					browserWinsClose(15)
					wait(5000)
					problems = 0
					App.open("C:\errors\Killer.bat")
					try:
						cur.close()
						con.close()
					except:
						logging.debug('cur.close fails on line 585 Problems>12')
					exit()
				wait(5)
				errTot = closeTabs(recordsResult,rootPath,Installations)
			else:
				print('No pending recordings found')
				logging.debug('No pending recordings found')
				loop = 0		
	except SystemExit:
		try:
			cur.close()
			con.close()
		except:
			logging.debug('cur.close fails on line 598 SystemExit')
		exit()
	except:
		logging.exception('Unexpected error in getRecords()')
		print('Unexpected error in getRecords(): %s %s', sys.exc_info()[0] , sys.exc_info()[1])

	if errTot == 0 :
		print("Waiting two hours")
		cur.execute("""UPDATE cc_recordings_saved SET status ='P' WHERE status = 'PROB'""")
		logging.debug('Resetting %d problematic records', cur.rowcount)
		#getting more records
		cmd1 = r'start /B "" "https://capitolserver.net/connect/service.php?dist=km"'
		logging.debug('Opening %s', cmd1) 
		print(cmd1)
		os.popen(cmd1)
		# indexes the Ask@Capitol knowledgebase in Canvas
		cmd2 = r'start /B "" "https://capitolserver.net/search/admin/spider_open.php?soption=full"'
		logging.debug('Opening %s', cmd2) 
		print(cmd2)
		os.popen(cmd2)
		# Checks to see if Course Evaluations are Ready and sends reminders
		cmd3 = r'start /B "" "https://capitolserver.net/evals/notifier_publisher.php?auth=1"'
		logging.debug('Opening %s', cmd3) 
		print(cmd3)
		os.popen(cmd3)
		wait(150)
	try:
		setThrowException(False)
		for i in range(0,Installations) :
			App.close("Internet Explorer")
			App.close("Firefox")
			App.close("Chrome")
			App.close("Adobe Connect")
			App.close("Flash Player")
		# For good measure, let's close apps again!
		App.close("Internet Explorer")
		App.close("Firefox")
		App.close("Chrome")
		App.close("Adobe Connect")
		App.close("Flash Player")
		setThrowException(True)
		
		cur.execute("SELECT foldername,url,meetingname,recordingname,cs.scoid,duration FROM cc_recordings_in_ac cr INNER JOIN cc_recordings_saved cs ON cs.scoid = cr.scoid WHERE cs.status = ? order by duration ASC LIMIT ?",('P',Installations))
		recordsResult = cur.fetchall()
		if(len(recordsResult)<1):
			curDateTime = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
			cur.execute("""UPDATE cc_configdetails SET status_check =?, root_path =?""",(curDateTime,rootPath))
			logging.debug('No pending records--waiting 6500 seconds')
			wait(6500)
	except SystemExit:
		try:
			cur.close()
			con.close()
		except:
			logging.debug('cur.close fails on line 651 SystemExit')
		exit()
	except:
		logging.exception('Unexpected error in closing browsers')
		logging.debug('Unexpected error closing browsers: %s %s', sys.exc_info()[0], sys.exc_info()[1])
	#logging.debug('Restoring the ROI')
	#setROI(Screen(0))
	#logging.debug('Restored the ROI')
	
	# check error file to make sure it's not huge
	size = os.path.getsize(const['logfile']+".log")
	if size > 2000000 :
		logging.debug('Logfile larger than 1 MB--probably some looping error. Closing')
		try:
			cur.close()
			con.close()
		except:
			logging.debug('cur.close fails on line 669 size>200000')
		exit()
	getRecords()
getRecords()
	