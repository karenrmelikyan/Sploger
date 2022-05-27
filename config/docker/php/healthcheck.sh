#!/bin/sh
set -e

if [ "$CONTAINER_ROLE" = "app" ]; then
    SCRIPT_NAME=/ping \
	SCRIPT_FILENAME=/ping \
	REQUEST_METHOD=GET \
	cgi-fcgi -bind -connect 127.0.0.1:9000 | tail +6 | grep pong >/dev/null 2>& 1
else
    exit 0
fi
