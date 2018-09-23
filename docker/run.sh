#!/usr/bin/env bash

OPTIONS='';

if [ "${DEBUG_LEVEL:-0}" -gt 0 ]; then
    OPTIONS="-d xdebug.remote_host=$(/sbin/ip route|awk '/default/ { print $3 }')";
fi

php $OPTIONS $1;
