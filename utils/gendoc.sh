#!/bin/sh
#
# Run SAMI on the code
#
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
$DIR/../vendor/bin/sami.php update $DIR/../config/doc.php -v
