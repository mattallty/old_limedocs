#!/bin/sh
#
# BE CAREFULL: this script CHANGE your code
# See https://github.com/squizlabs/PHP_CodeSniffer/wiki/Fixing-Errors-Automatically
#
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
$DIR/../vendor/bin/phpmd $DIR/../src text cleancode,codesize,controversial,design,naming,unusedcode