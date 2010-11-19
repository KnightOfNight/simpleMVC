#!/bin/bash


if [ -d ".git" ]; then
	echo "ERROR: this is a git repository.  Remove '.git' if you want to setup a new application."
	exit -1
fi


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

exit

file=".htaccess"
[ -s $file ] && echo "File '$file' already exists"
[ -s $file ] || ( echo "$file does not exist"; cat $source/$file | sed -e "s,MVC_URL,$MVC_URL,g" | sed -e "s,MVC_DOMAIN,$MVC_DOMAIN,g" -e "s,MVC_PATH,$MVC_PATH,g" > $file ; chmod 644 $file)

file="public/.htaccess"
[ -s $file ] && echo "File '$file' already exists"
[ -s $file ] || ( echo "$file does not exist"; cat $source/$file | sed -e "s,MVC_URL,$MVC_URL,g" | sed -e "s,MVC_DOMAIN,$MVC_DOMAIN,g" -e "s,MVC_PATH,$MVC_PATH,g" > $file ; chmod 644 $file)

file="cfg/config.json"
[ -s $file ] && echo "File '$file' already exists"
[ -s $file ] || ( echo "$file does not exist"; cat $source/$file | sed -e "s,MVC_URL,$MVC_URL,g" | sed -e "s,MVC_DOMAIN,$MVC_DOMAIN,g" -e "s,MVC_PATH,$MVC_PATH,g" > $file ; chmod 644 $file)
