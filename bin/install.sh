#!/bin/bash

MVC_DOMAIN=""
MVC_PATH=""
MVC_URL=""

[ -s .config ] && . .config

while true; do
	while [ -z "$MVC_DOMAIN" ]; do
		echo
		echo -n "Enter domain name: "; read MVC_DOMAIN
	done
	while [ -z "$MVC_PATH" ]; do
		echo
		echo -n "Enter path: "; read MVC_PATH
	done

	MVC_URL="https://$MVC_DOMAIN$MVC_PATH"
	
	echo
	echo "Domain name: $MVC_DOMAIN"
	echo "Path: $MVC_PATH"
	echo "URL: $MVC_URL"
	
	echo
	echo -n "Are these values correct? (y)/n "; read yesno
	if [ "$yesno" = "" -o "$yesno" = "y" ]; then
		break
	else
		MVC_DOMAIN=""
		MVC_PATH=""
	fi
done

echo

source="../mvc"

for dir in app cfg html public tmp; do
mkdir $dir
chmod 755 $dir
done

for dir in controllers lib models views; do
	mkdir app/$dir && chmod 755 app/$dir
done

for dir in js img css html views; do
	mkdir public/$dir && chmod 755 public/$dir
done

for dir in cache logs sessions; do
	mkdir tmp/$dir && chmod 777 tmp/$dir
done

[ -e lib ] && echo "Symlink 'lib' to '$source/lib' exists."
[ -e lib ] || ln -sv $source/lib lib

file=".htaccess"
[ -s $file ] && echo "File '$file' already exists"
[ -s $file ] || ( echo "$file does not exist"; cat $source/$file | sed -e "s,MVC_URL,$MVC_URL,g" | sed -e "s,MVC_DOMAIN,$MVC_DOMAIN,g" -e "s,MVC_PATH,$MVC_PATH,g" > $file ; chmod 644 $file)

file="public/.htaccess"
[ -s $file ] && echo "File '$file' already exists"
[ -s $file ] || ( echo "$file does not exist"; cat $source/$file | sed -e "s,MVC_URL,$MVC_URL,g" | sed -e "s,MVC_DOMAIN,$MVC_DOMAIN,g" -e "s,MVC_PATH,$MVC_PATH,g" > $file ; chmod 644 $file)

file="cfg/config.json"
[ -s $file ] && echo "File '$file' already exists"
[ -s $file ] || ( echo "$file does not exist"; cat $source/$file | sed -e "s,MVC_URL,$MVC_URL,g" | sed -e "s,MVC_DOMAIN,$MVC_DOMAIN,g" -e "s,MVC_PATH,$MVC_PATH,g" > $file ; chmod 644 $file)

cp -av $source/html/* html/

cp -av $source/app/.htaccess app/
cp -av $source/cfg/.htaccess cfg/
cp -av $source/cfg/inflection.php cfg/
cp -av $source/tmp/.htaccess tmp/

cp -av $source/app/lib/* app/lib/
cp -av $source/app/controllers/WelcomeController.class.php app/controllers/
cp -av $source/app/views/header.php app/views/
cp -av $source/app/views/footer.php app/views/
mkdir app/views/welcome && chmod 755 app/views/welcome
cp -av $source/app/views/welcome/frontpage.php app/views/welcome/
cp -av $source/app/views/welcome/loginform.php app/views/welcome/

cp -av $source/public/index.php public/
cp -av $source/public/error.php public/
