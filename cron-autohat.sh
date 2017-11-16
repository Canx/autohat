#!/bin/bash
# utiliza este script en vez de autohat.php directamente si lo ejecutas desde cron.
#
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
export PATH=/usr/bin/:/bin/:$DIR
autohat.php $@ >> $DIR/autohat.log
