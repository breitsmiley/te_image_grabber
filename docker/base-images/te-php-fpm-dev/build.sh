#!/bin/bash

# DOCKER HUB Registry
##-------------------------------
VERSION=0.0.4
IMAGE_NAME=te-php-fpm-dev
docker build -t breitsmiley/${IMAGE_NAME}:latest -t breitsmiley/${IMAGE_NAME}:${VERSION} . \
&& docker push breitsmiley/${IMAGE_NAME}:latest \
&& docker push breitsmiley/${IMAGE_NAME}:${VERSION}

