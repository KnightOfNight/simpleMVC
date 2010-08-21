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


echo "Setting version: $version ..."
sed --in-place -e "s/@version .*/@version $version/" lib/*.php
echo "... source files updated."


echo "Setting package: $package ..."
sed --in-place -e "s/@package .*/@package $package/" lib/*.php
echo "... source files updated."
