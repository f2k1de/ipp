#!/bin/bash
while true; do
curl -s https://stream.wikimedia.org/v2/stream/recentchange | php -f rcbot.php > rcbot.log
sleep 300
done

