#!/bin/sh
#
# Run phpcs on the code
#
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
$DIR/../vendor/bin/phpcs --report=full --standard=$DIR/../ruleset.xml $DIR/..