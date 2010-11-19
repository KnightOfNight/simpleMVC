#!/bin/bash


#if [ -d ".git" ]; then
#	echo "ERROR: this is a git repository.  Remove '.git' if you want to setup a new application."
#	exit -1
#fi


MVC_URL=""
MVC_PROTOCOL=""
MVC_DOMAIN=""
MVC_PATH=""


[ -s .config ] && . .config


while true; do
	while [ -z "$MVC_PROTOCOL" ]; do
		echo
		echo -n "Enter protocol: "; read MVC_PROTOCOL
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

cp /dev/null .config
echo "MVC_PROTOCOL=\"$MVC_PROTOCOL\"" >> .config
echo "MVC_DOMAIN=\"$MVC_DOMAIN\"" >> .config
echo "MVC_PATH=\"$MVC_PATH\"" >> .config

tmpfile=$(mktemp /tmp/XXXXX)
file=".htaccess"
cat $file | sed -e "s,MVC_URL,$MVC_URL,g" | sed -e "s,MVC_DOMAIN,$MVC_DOMAIN,g" -e "s,MVC_PATH,$MVC_PATH,g" >  $tmpfile
mv $tmpfile $file
chmod 644 "$file"

tmpfile=$(mktemp /tmp/XXXXX)
file="public/.htaccess"
cat $file | sed -e "s,MVC_URL,$MVC_URL,g" | sed -e "s,MVC_DOMAIN,$MVC_DOMAIN,g" -e "s,MVC_PATH,$MVC_PATH,g" >  $tmpfile
mv $tmpfile $file
chmod 644 "$file"

tmpfile=$(mktemp /tmp/XXXXX)
file="cfg/config.json"
cat $file | sed -e "s,MVC_URL,$MVC_URL,g" | sed -e "s,MVC_DOMAIN,$MVC_DOMAIN,g" -e "s,MVC_PATH,$MVC_PATH,g" >  $tmpfile
mv $tmpfile $file
chmod 644 "$file"

for dir in app cfg lib public tmp; do 
	find $dir -type f -exec chmod a+r {} \;
	find $dir -type d -exec chmod a+rx {} \;
done

chmod 755 tmp
find tmp -type d -mindepth 1 -exec chmod a+rwx {} \;
