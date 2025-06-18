#!/bin/bash

set -e

if [ -f "wp-includes/version.php" ]; then
  echo "WordPress already installed. Skipping download."
  exit 0
fi

echo "Downloading latest WordPress..."
curl -o latest.zip https://wordpress.org/latest.zip

echo "Extracting WordPress..."
unzip -o latest.zip

echo "Moving files to project root..."
shopt -s dotglob
mv -f wordpress/* ./

echo "Cleaning up..."
rm -rf wordpress latest.zip

echo "WordPress installed in project root."
