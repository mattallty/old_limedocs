#!/bin/sh
#
# BE CAREFULL: this script CHANGE your code
# See https://github.com/squizlabs/PHP_CodeSniffer/wiki/Fixing-Errors-Automatically
#
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
$DIR/../vendor/bin/phpcbf --report=diff -n --standard=$DIR/../ruleset.xml $DIR/..