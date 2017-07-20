#!/bin/bash

if [ ! $path_app ]; then
	path=`dirname $(readlink -f $0)`
	path=`dirname ${path}`
	path=`dirname ${path}`
	path=`dirname ${path}`
	source ${path}/lib/scripts/paths.sh
fi

source ${path}/lib/scripts/run_check.sh

me=`basename $0`
time_start=`date +%s`
time_start_all=`date +%s`
EMAILBODY="/tmp/emailbody-${me}-$$.txt"
rm -rf $EMAILBODY

ECHOMSG="[$$][`date`]: Running: ${me} with pid of $$ - starting on: `date`"
fappend $EMAILBODY "${ECHOMSG}\n"
echo ${ECHOMSG}

# Sync the /var/www
time_start=`date +%s`
ECHOMSG="[$$][`date`]: Syncing mediatracker web files. Starting on: `date`"
fappend $EMAILBODY "${ECHOMSG}\n"
echo ${ECHOMSG}

/usr/bin/rsync -raz -e 'ssh -p 9001' --delete /var/www/mediatracker/app/webroot/files user@example.com:/var/www/mediatracker/app/webroot/files
RETVAL=$?

time_end=`date +%s`
time_diff=$(expr ${time_end} - ${time_start})
ERR_WEB=""
if [ $RETVAL -ne 0 -a $RETVAL -ne 23 -a $RETVAL -ne 95 ]; then
ECHOMSG="[$$][`date`]: An Error occurred while syncing mediatracker web files. Return code was: ${RETVAL} - in ${time_diff} seconds"
ERR_WEB=" - ERROR WEB "
else
ECHOMSG="[$$][`date`]: Completed Syncing mediatracker web files in ${time_diff} seconds"
fi
fappend $EMAILBODY "${ECHOMSG}\n"
echo ${ECHOMSG}

time_end_all=`date +%s`
time_diff_all=$(expr ${time_end_all} - ${time_start_all})
ECHOMSG="[$$][`date`]: Completed: ${me} with pid of $$ - seconds to complete: ${time_diff_all}"
fappend $EMAILBODY "${ECHOMSG}\n"
echo ${ECHOMSG}

SUBJECT="Cron Rsync - mediatracker - ${ERR_WEB}- ${ECHOMSG}"
TOEMAIL="example@example.com, example@example.com, example@example.com"
FREMAIL="example@example.com"
TMP="/tmp/email-${me}-$$.txt"

EMAILBODYFILE=$EMAILBODY
EMAILBODY=$(cat $EMAILBODY)
rm -f $EMAILBODYFILE

rm -rf $TMP
fappend $TMP "From: $FREMAIL"
fappend $TMP "To: $TOEMAIL"
fappend $TMP "Reply-To: $FREMAIL"
fappend $TMP "Subject: $SUBJECT"
fappend $TMP ""
fappend $TMP "Results:"
fappend $TMP ""
fappend $TMP "$EMAILBODY"
fappend $TMP ""
#cat $TMP|sendmail -t
/usr/sbin/ssmtp "${TOEMAIL}" < $TMP
RETVAL=$?
if [ $RETVAL -ne 0 ]; then
echo "[$$][`date`]: An Error occurred while sending the email. Return code was: ${RETVAL}"
else
echo "[$$][`date`]: The email successfully sent"
fi
rm -f $TMP
