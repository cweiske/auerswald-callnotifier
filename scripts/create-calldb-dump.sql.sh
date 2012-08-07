#!/bin/sh
cd "`dirname "$0"`"

mysqldump -ucallnotifier -pcallnotifier callnotifier\
 -d --skip-add-drop-table --skip-comments\
 | grep -v '/*!'\
 | grep -v '^$'\
 > ../docs/create-call-log.sql