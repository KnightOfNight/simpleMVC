#!/bin/bash

chmod 755 .

for dir in app mvc public tmp; do 
	find $dir -type f -exec chmod a+r {} \; 2> /dev/null
	find $dir -type d -exec chmod a+rx {} \; 2> /dev/null
done

find tmp -mindepth 1 -type d -exec chmod a+rwx {} \;

for dir in doc setup ; do 
	find $dir -type f -exec chmod 600 {} \;
	find $dir -type d -exec chmod 700 {} \;
done

chmod 700 bin
chmod 700 bin/*
chmod 600 bin/.htaccess

chmod 777 public/js/pp
