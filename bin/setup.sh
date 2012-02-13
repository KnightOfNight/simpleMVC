#!/bin/bash






#
# check for required directories
reqdirs="app bin doc mvc public setup tmp"

if [ $(basename "$PWD") == "bin" ]; then
	cd ..
fi

for dir in $reqdirs; do
	if [ ! -d $dir ]; then
		echo
		echo "ERROR: required directory '$dir' not found."
		echo
		exit -1
	fi
done



#
# get the config values
MVC_URL=""
MVC_PROTOCOL=""
MVC_DOMAIN=""
MVC_PATH=""

[ -s .simpleMVC_config ] && . .simpleMVC_config

while true; do
	echo

	while [ -z "$MVC_PROTOCOL" ]; do
		echo -n "Enter protocol <http or https>: "; read MVC_PROTOCOL

		if [ "$MVC_PROTOCOL" != "http" -a "$MVC_PROTOCOL" != "https" ]; then
			echo "Invalid protocol.  Must be 'http' or 'https'."
			MVC_PROTOCOL=""
		fi
	done

	while [ -z "$MVC_DOMAIN" ]; do
		echo -n "Enter domain name <www.somedomain.com>: "; read MVC_DOMAIN
	done

	while [ -z "$MVC_PATH" ]; do
		echo -n "Enter path </some/web/path>: "; read MVC_PATH
	done

	MVC_URL="$MVC_PROTOCOL://$MVC_DOMAIN$MVC_PATH"
	
	echo
	echo "URL: $MVC_URL"
	
	echo
	echo -n "Is this correct? (y)/n "; read yesno
	if [ "$yesno" = "" -o "$yesno" = "y" ]; then
		break
	else
		MVC_URL=""
		MVC_PROTOCOL=""
		MVC_DOMAIN=""
		MVC_PATH=""
	fi
done


#
# save config values
cp /dev/null .simpleMVC_config
echo "MVC_URL=\"$MVC_URL\"" >> .simpleMVC_config
echo "MVC_PROTOCOL=\"$MVC_PROTOCOL\"" >> .simpleMVC_config
echo "MVC_DOMAIN=\"$MVC_DOMAIN\"" >> .simpleMVC_config
echo "MVC_PATH=\"$MVC_PATH\"" >> .simpleMVC_config

export MVC_URL
export MVC_PROTOCOL
export MVC_DOMAIN
export MVC_PATH


#
# function to write a setup file - declared here to make sure it
# gets the exports of the variables
function setup_file () {
	src="$1"
	dest="$2"

	if [ -z "$src" -o -z "$dest" ]; then
		echo "usage: setup_file(<src>,<dest>)"
		exit -1
	fi

	cat $src | sed -e "s,MVC_URL,$MVC_URL,g" -e "s,MVC_DOMAIN,$MVC_DOMAIN,g" -e "s,MVC_PATH,$MVC_PATH,g" > $dest

	chmod 644 "$dest"
}


#
# setup all of the config files
echo

setup_file	 setup/files/htaccess-01-top		.htaccess
setup_file	 setup/files/htaccess-02-public		public/.htaccess
setup_file	 setup/files/config.json			app/cfg/config.json

cp -a mvc/lib/index.php public/

echo "Configuration files written."


#
# setup perms
echo

chmod 755 .

for dir in app mvc public tmp; do 
	find $dir -type f -exec chmod a+r {} \;
	find $dir -type d -exec chmod a+rx {} \;
done

find tmp -mindepth 1 -type d -exec chmod a+rwx {} \;

echo "All permissions verified."


#
# finish up
echo
echo "You must now edit 'app/cfg/config.json' with your database permissions."



echo

### EOF




