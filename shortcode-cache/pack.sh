#!/bin/bash

rm shortcode-cache.zip

cd ..

zip -r shortcode-cache.zip shortcode-cache --exclude="shortcode-cache/.git/*" --exclude="shortcode-cache/.idea/*" --exclude="shortcode-cache/.gitignore" --exclude="shortcode-cache/*.sh"
mv shortcode-cache.zip shortcode-cache

cd -