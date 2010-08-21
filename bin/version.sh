#!/bin/bash

# Set the version string in all source files.  This will be a git hook someday.

version=$(cat "VERSION" 2> /dev/null)

if [ -z "$version" ]; then
	echo "Run this script from the top-level source directory."
	exit -1
fi

echo "Setting version: $version ..."
sed --in-place -e "s/@version .*/@version $version/" lib/*.php
echo "... source files updated."
