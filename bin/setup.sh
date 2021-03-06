#!/bin/bash


echo
echo "Setting up simpleMVC..."


export reqdirs="app bin doc mvc public setup tmp"
export conf_vars="MVC_URL MVC_PROTOCOL MVC_DOMAIN MVC_PATH DB_HOST DB_PORT DB_NAME DB_USER DB_PASS"



#
# check for required directories
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
for var in $conf_vars; do
	eval $var=""
done

[ -s setup/simpleMVC_config ] && . setup/simpleMVC_config

while true; do

	while [ -z "$MVC_PROTOCOL" ]; do
		echo
		echo -n "Enter web protocol <http or https>: "; read MVC_PROTOCOL

		if [ "$MVC_PROTOCOL" != "http" -a "$MVC_PROTOCOL" != "https" ]; then
			echo "Invalid protocol.  Must be 'http' or 'https'."
			MVC_PROTOCOL=""
		fi
	done

	while [ -z "$MVC_DOMAIN" ]; do
		echo
		echo -n "Enter domain name <www.somedomain.com>: "; read MVC_DOMAIN
	done

	while [ -z "$MVC_PATH" ]; do
		echo
		echo -n "Enter path </some/web/path>: "; read MVC_PATH
	done

	while [ -z "$DB_HOST" ]; do
		echo
		echo -n "Enter database host name: "; read DB_HOST
	done

	while [ -z "$DB_PORT" ]; do
		echo
		echo -n "Enter database port number: "; read DB_PORT
	done

	while [ -z "$DB_NAME" ]; do
		echo
		echo -n "Enter database name: "; read DB_NAME
	done

	while [ -z "$DB_USER" ]; do
		echo
		echo -n "Enter database user name: "; read DB_USER
	done

	while [ -z "$DB_PASS" ]; do
		echo
		echo -n "Enter database password: "; read DB_PASS
	done

	MVC_URL="$MVC_PROTOCOL://$MVC_DOMAIN$MVC_PATH"
	
	echo
	echo "URL: $MVC_URL"
	echo "Database: $DB_HOST:$DB_PORT to $DB_NAME as $DB_USER/$DB_PASS"
	
	echo
	echo -n "Is this correct? (y)/n "; read yesno
	if [ "$yesno" = "" -o "$yesno" = "y" ]; then
		break
	else
		for var in $conf_vars; do
			eval $var=""
		done
	fi
done


#
# save config values
cp /dev/null setup/simpleMVC_config

for var in $conf_vars; do
	echo "$var=\"${!var}\"" >> setup/simpleMVC_config

	export $var
done


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

	tmp=$(mktemp /tmp/XXXXXXXXXX)

	cp "$src" $tmp
	
	for var in $conf_vars; do
		value="${!var}"
		sed -i -e "s,$var,$value,g" $tmp
	done

	mv $tmp "$dest"

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
bin/perms.sh
echo "All permissions verified."


#
# setup database
echo

cmd="mysql --user=$DB_USER --password=$DB_PASS --host=$DB_HOST --port=$DB_PORT $DB_NAME"
err=$(mktemp /tmp/XXXXXXXXXX)
if echo "show tables" | $cmd > /dev/null 2> $err; then
	echo "Successfully tested database permissions."
	echo

	if cat setup/sql/mvc_logs.sql | $cmd 2> $err; then
		echo "Successfully setup logs table."
	else
		echo "ERROR: unable to setup logs table."
		echo
		echo "MySQL error follows..."
		cat $err
		rm $err

		echo
		exit -1
	fi
else
	echo "ERROR: unable to connect to database."
	echo
	echo "MySQL error follows..."
	cat $err
	rm $err

	echo
	exit -1
fi






echo

### EOF




