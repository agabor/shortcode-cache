#!/bin/bash

rm shortcode-detect.zip

cd ..

zip -r shortcode-detect.zip shortcode-detect --exclude="shortcode-detect/.git/*" --exclude="shortcode-detect/.idea/*" --exclude="shortcode-detect/.gitignore" --exclude="shortcode-detect/*.sh"
mv shortcode-detect.zip shortcode-detect

cd -