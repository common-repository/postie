#!/bin/sh
cd ..
echo Create readme.txt
cat docs/Postie.txt docs/Installation.txt docs/Usage.txt docs/FAQ.txt docs/Changes.txt > readme.txt
cd deploy
