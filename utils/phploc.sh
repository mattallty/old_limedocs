#!/bin/sh
#
# Run phpmloc on the code
#
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
$DIR/../vendor/bin/phploc $DIR/../src $DIR/../tests
