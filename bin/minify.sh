#!/bin/bash

PLUGIN_PATH="wp-content/plugins/user-directory-plugin"
SRC_DIR="$PLUGIN_PATH/src/Assets"
DIST_DIR="$PLUGIN_PATH/dist"

mkdir -p "$DIST_DIR"

echo "Minifying JS..."
npx terser "$SRC_DIR/script.js" --compress --mangle -o "$DIST_DIR/script.min.js"

echo "Minifying CSS..."
npx cleancss "$SRC_DIR/style.css" -o "$DIST_DIR/style.min.css"

echo "Minification complete."
