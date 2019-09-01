#!/usr/bin/python
# 
##########################################################################
#
#   Unless otherwise noted, code in this file is copyright (C) 2017 Capitol Technology University
#	Kenneth Mayer 
#	distlearn@captechu.edu 
#	ken.i.mayer@gmail.com
#
#########################################################################

import sys, smtplib, re
import os, mimetypes, base64
import logging
logging.basicConfig(filename=r'C:/errors/pythonMailer.log',level=logging.DEBUG, format='%(asctime)s %(message)s')

from email import encoders
from email.mime.audio import MIMEAudio
from email.mime.base import MIMEBase
from email.mime.image import MIMEImage
from email.mime.application import MIMEApplication
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from email.mime.message import MIMEMessage
from datetime import datetime
curDateTime = datetime.now().strftime('%Y-%m-%d_%H_%M_%S')

#print('----------------pythonMailer running! ---'+curDateTime)
exec open("C:\\Python27\\Lib\\rec_config.py").read()

def mailSMTP( you, subject, body, file='' ):
	#print('mailSMTP body: ' + body)
	#logging.debug('mailSMTP body: ' + body)
	if os.path.isfile(body) : 
		# body is a file, read the file and put that into the body
		daName = os.path.basename(body)
		# if there is no attachment, make the body file also the attachment
		if file == '':
			file = body
		fil = open(body, "r")
		body = fil.read()
	
	# attach a file if it exists
	if file != '' and os.path.isfile(file):
		daName = os.path.basename(file)
		daType, encoding =  mimetypes.guess_type(daName)
		#print('daType: '+str(daType)+'  encoding: '+str(encoding))
		fil = open(file, "rb")
		daFile = MIMEApplication( fil.read(),Name=daName)
		if daType is None or encoding is not None:
			# No guess could be made so use a binary type.
			daType = 'application/octet-stream'
		maintype, subtype = daType.split('/', 1)
		if maintype == 'text':
			fp = open(file)
			attach = MIMEText(fp.read(), _subtype=subtype)
			fp.close()
		elif maintype == 'image':
			fp = open(file, 'rb')
			attach = MIMEImage(fp.read(), _subtype=subtype)
			fp.close()
		elif maintype == 'audio':
			fp = open(file, 'rb')
			attach = MIMEAudio(fp.read(), _subtype=subtype)
			fp.close()
		else:
			fp = open(file, 'rb')
			attach = MIMEBase(maintype, subtype)
			attach.set_payload(fp.read())
			fp.close()
			# Encode the payload using Base64
			encoders.encode_base64(attach)		
		daFile['Content-Disposition'] = 'attachment; filename="%s"' % daName
		daFile['Content-Type'] = daType
		main = MIMEMultipart()
		#print('adding a file')
		main.attach(daFile)
	else:
		main = MIMEMultipart('alternative')
		
		plain = re.sub(r'<[^>]*?>', '', body)
		
		#plain = body.replace("</td><td>", "\t").replace("<tr>","").replace("</tr>","")
		#plain = plain.replace("<strong>",'').replace("</strong>","")
		#plain = plain.replace("<br />", "\n\r\t")
		#plain = plain.replace("<table>",'').replace("</table>","").replace("<h2>","")
		#plain = plain.replace("</h2>",'').replace ("<td>","").replace ("</td>","")
		p_msg = MIMEMessage(MIMEText(  plain, "plain"  ))
		p_msg.add_header('Content-Disposition', 'inline', filename='Message_in_Plain.txt')
		#print('Adding plain text')
		main.attach(p_msg  )

	me = const['me']
	you = you
	main[ 'Subject' ] = subject
	main[ 'From' ] = me
	main[ 'To' ] = you
	main.add_header('reply-to', const['me'])
	
	html = "<html><head>"
	html = html + "<style>td {vertical-align:top}</style>"
	html = html + "</head><body>" + body + "</body></html>"
	main.attach(MIMEText( html, "html" ))
	
	# Send the message via our own SMTP server
	mess = main.as_string()
	mail_server = smtplib.SMTP()
	mail_server.connect('localhost', 25)
	#print('connected' )

	mail_server.sendmail( me, you, mess )
	#print('mail sent?' )

	mail_server.quit( )

try:
	if len(sys.argv)>3 :
		you = sys.argv[1]
		subject = sys.argv[2]
		body = sys.argv[3]
		file = ''
		if len(sys.argv)>4:
			file = sys.argv[4]
		mailSMTP(you, subject, body,file)
	
except Exception, e:
		logging.debug("mailSMTP generates error %s -- %s",  sys.exc_info()[0], sys.exc_info()[1] )
		logging.debug("exception details: " + str(e.args) )



	