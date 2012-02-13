#!/bin/bash


reqdirs="app bin doc mvc public setup tmp"


#
# check for required directories
if [ $(basename "$PWD") == "bin" ]; then
	cd ..
fi

for dir in $reqdirs; do
	if [ ! -d $dir ]; then
		echo "Required directory '$dir' not found."
		exit -1
	fi
done

echo "Directory check: passed"



#
# setup the config and htaccess files
MVC_URL=""
MVC_PROTOCOL=""
MVC_DOMAIN=""
MVC_PATH=""

[ -s .simpleMVC_config ] && . .simpleMVC_config

while true; do
	while [ -z "$MVC_PROTOCOL" ]; do
		echo
		echo -n "Enter protocol: "; read MVC_PROTOCOL

		if [ "$MVC_PROTOCOL" != "http" -a "$MVC_PROTOCOL" != "https" ]; then
			echo "Invalid protocol.  Must be 'http' or 'https'."
			MVC_PROTOCOL=""
		fi
	done

	while [ -z "$MVC_DOMAIN" ]; do
		echo
		echo -n "Enter domain name: "; read MVC_DOMAIN
	done

	while [ -z "$MVC_PATH" ]; do
		echo
		echo -n "Enter path: "; read MVC_PATH
	done

	MVC_URL="$MVC_PROTOCOL://$MVC_DOMAIN$MVC_PATH"
	
	echo
	echo "URL: $MVC_URL"
	
	echo
	echo -n "Is this correct? (y)/n "; read yesno
	if [ "$yesno" = "" -o "$yesno" = "y" ]; then
		break
	else
		MVC_PROTOCOL=""
		MVC_DOMAIN=""
		MVC_PATH=""
	fi
done
echo


cp /dev/null .simpleMVC_config
echo "MVC_PROTOCOL=\"$MVC_PROTOCOL\"" >> .simpleMVC_config
echo "MVC_DOMAIN=\"$MVC_DOMAIN\"" >> .simpleMVC_config
echo "MVC_PATH=\"$MVC_PATH\"" >> .simpleMVC_config

export MVC_PROTOCOL
export MVC_DOMAIN
export MVC_PATH


function setup_file () {
	file="$1"
	if [ -z "$file" ]; then
		echo "usage: setup_file(<file>)"
		exit -1
	fi
	tmpfile=$(mktemp /tmp/XXXXX)
	cat $file | sed -e "s,MVC_URL,$MVC_URL,g" | sed -e "s,MVC_DOMAIN,$MVC_DOMAIN,g" -e "s,MVC_PATH,$MVC_PATH,g" >  $tmpfile
	mv $tmpfile $file
	chmod 644 "$file"
}


setup_file .htaccess
setup_file public/.htaccess
setup_file app/cfg/config.json


for dir in app mvc public tmp; do 
	find $dir -type f -exec chmod a+r {} \;
	find $dir -type d -exec chmod a+rx {} \;
done


chmod 755 tmp
find tmp -mindepth 1 -type d -exec chmod a+rwx {} \;


cp -a mvc/lib/index.php public/


chmod 755 .
