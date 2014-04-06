import sys
import os
import re
import urllib
import json
import smtplib
import linecache
import couchdb
from datetime import datetime
from random import randint
from email.MIMEText import MIMEText

###########
# Methods #
###########

# This method takes in a list of words, and a word to remove from that list
def purge_word_list(words, word):
	if word in words:
		words.remove(word)

# This method takes 0, 1, or 2 arguments by position.
#   0 - write all lines to file
#   1 - write all passed in lines to file
#   2 - write all passed in lines, except for the other lines
def write_fresh(*args):
	if len(args) == 0:
		j = open('', 'w') #Enter full path to txt file with fresh words
		j.writelines(lines)
		j.close()
	elif len(args) == 1:
		linesToWrite = args[0]
		j = open('', 'w') #Enter full path to txt file with fresh words
		j.writelines(linesToWrite)
		j.close()
	elif len(args) == 2:
		linesToWrite = args[0]
		linesToIgnore = args[1]

		#Remove linesToIgnore from linesToWrite
		for badline in linesToIgnore:
			if badline in linesToWrite:
				purge_word_list(linesToWrite, badline)

		#Write remaining lines to file
		j = open('', 'w') #Enter full path to txt file with fresh words
		j.writelines(linesToWrite)
		j.close()
	else:
		print "There were %s too many arguments passed into write_fresh()" % (len(args)-2)

# This method takes in a list of used words to output
def write_used(used):
	u = open('', 'a') #Enter full path to txt file with used words
	u.write(used + "\n")
	u.close()

# This method takes in a list of failed words to append to the bad_words.txt file
def write_bad(bads):
	b = open('', 'a') #Enter full path to txt file with bad words
	for bad in bads:
		b.write(bad + "\n")
	b.close()

# This method adds the word and definition to the database
def updateDatabase(word, meanings, time):
	couch = couchdb.Server()
	db = couch[''] #Enter the name of the CouchDB database
	doc = {'word': word, 'definitions': meanings, 'time': time}
	db.save(doc)

#Send message to destination(s)
def sendMessage():
	#! /usr/local/bin/python
	SMTPserver = '' #Enter your SMTPServer name (e.g., mail.domain.com)
	sender =     '' #Enter the email address for the senter (e.g., sender@domain.com)
	destination = [''] #Enter the destination email address(es)
	USERNAME = "" #Enter the username used to connect to the SMTP server (may be same as sender)
	PASSWORD = "" #Enter password for username
	text_subtype = 'plain'
	subject = ""
	content = '%s' % (definitionString)
	content = content.rstrip()

	try:
    		msg = MIMEText(content, text_subtype)
    		msg['Subject'] = subject
	    	msg['From'] = sender

		conn = smtplib.SMTP(SMTPserver, 587)
		conn.set_debuglevel(False)
		conn.login(USERNAME, PASSWORD)
		
		try:
        		conn.sendmail(sender, destination, msg.as_string())
    		finally:
        		conn.close()

	except Exception, exc:
    		sys.exit( "Failed to deliver message; %s" % str(exc) ) # give an error message

###############
# FIND A WORD #
###############

#Open fresh_words.txt to read all lines into a list
f = open('', 'r') #Enter the full path to txt file with fresh words
lines = f.readlines()
f.close()

word_count = len(lines)
badLines = []
result = ""

while len(result) < 150:
	#used to alter word_count so that a rando is not generated larger than the latest list of words
	counter = 0
	#Generate a random number based on word_count
	rando = randint(0, (word_count - counter))
	#Get a random word from the fresh_words list
	word = linecache.getline('', rando).rstrip() #Enter full path to txt file with fresh words

	#Get JSON from Google define; convert jsonp to json structure as result
	data = urllib.urlopen('http://www.google.com/dictionary/json?callback=a&sl=en&tl=en&q=' + word)
	result = data.read()
	json_start = result.find('{')
	json_end = result.rfind('}')
	result = result[json_start:(json_end - len(result) + 1)]

	#If the word is not recognized by Google define command, delete the word from the list
	if len(result) < 150:
		purge_word_list(lines, word)
		badLines.append(word)
		counter += 1
	else:
		break

#####################
# Update Text Files #
#####################

#Remove used word from lines to prevent repeats
purge_word_list(lines, word+"\n")

#Write out the bad lines
write_bad(badLines)

#Write used line
write_used(word)

#Write remaining fresh lines
write_fresh(lines, badLines)

####################
# Finalize Message #
####################

#remove invalid json escape characters '\' or '\x27'
re1='(\\\\)'
re2='(x)'
re3='(\\d)'
re4='(\\d)'
rg = re.compile(re1+re2+re3+re4,re.IGNORECASE|re.DOTALL)

result = re.sub(rg, "", result)
result = result.replace('\\', "")

#Load json from result
j = json.loads(result)

#Extract the definition(s) from the JSON data
definitionString = ""
meanings = []
entries = j['primaries'][0]['entries']
for key in entries:
	if key['type'] == 'meaning':
		meaning = key['terms'][0]['text']
		definitionString += "%s: %s \n" % (word, meaning)
		meanings.append(meaning.rstrip())

sendMessage()
time = datetime.now()
updateDatabase(word, meanings, str(time))
