#!/bin/bash

rm shortcode-detect.zip
rm shortcode-cache.zip

zip -r shortcode-detect.zip shortcode-detect --exclude=shortcode-detect/.yact/*
zip -r shortcode-cache.zip shortcode-cache --exclude=shortcode-cache/.yact/*
