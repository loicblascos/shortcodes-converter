#!/bin/bash

# Exit if any command fails.
set -e

# Change to the expected directory.
cd "$(dirname "$0")"
cd ..

echo "🔢  Checking plugin version..."
# Get plugin version from readme.txt and main plugin file.
PLUGINVERSION=`grep "Version:" shortcodes-converter.php | awk -F' ' '{print $NF}' | tr -d '\r'`
READMEVERSION=`grep "Stable tag:" readme.txt | awk -F' ' '{print $NF}' | tr -d '\r'`

# Check plugin version against readme.txt.
if [ "$PLUGINVERSION" != "$READMEVERSION" ]; then
	echo "⚠️ Version in readme.txt doesn't match plugin version. Exiting...."
	exit 1;
fi

echo "👷‍♂️  Generating assets..."
# Run the build.
npm run build

echo "🌎  Generating .pot file..."
# Update pot.
wp i18n make-pot . languages/sc-converter.pot

cd ..
echo "🎁  Creating archive..."

# Generate the plugin zip file.
zip -r shortcodes-converter.zip \
	shortcodes-converter/shortcodes-converter.php \
	shortcodes-converter/readme.txt \
	shortcodes-converter/license.txt \
	shortcodes-converter/assets/* \
	shortcodes-converter/includes/* \
	shortcodes-converter/languages/sc-converter.pot \
	shortcodes-converter/languages/sc-converter.php \
	-x *src*

mv shortcodes-converter.zip shortcodes-converter

echo $'\n🎉  Done. You\'ve built Shortcodes Converter plugin!'
