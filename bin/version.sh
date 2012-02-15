#!/bin/bash


# Set the @package and @version in all source files.


package=$(cat "PACKAGE" 2> /dev/null)
version=$(cat "VERSION" 2> /dev/null)


if [ -z "$version" ]; then
	echo "Run this script from the top-level source directory."
	exit -1
fi

if [ -z "$package" ]; then
	echo "Run this script from the top-level source directory."
	exit -1
fi


echo
echo "Current version: $version"
echo
echo -n "Enter new version: "; read newversion
if [ -z "$newversion" ]; then
	echo
	echo "You must enter a new version."
	echo
	exit -1
elif [ "$version" == "$newversion" ]; then
	echo
	echo "Version is the same."
	echo
	exit -1
fi
echo $newversion > VERSION
version="$newversion"

echo

echo "Setting version: $version ..."

sed --in-place -e "s/@version .*/@version $version/" mvc/lib/*.php

sed --in-place -e "s/simpleMVC [^\"][^\"]*/simpleMVC $version/" setup/files/config.json

echo "... source files updated."

echo

echo "Setting package: $package ..."

sed --in-place -e "s/@package .*/@package $package/" mvc/lib/*.php

echo "... source files updated."

echo
