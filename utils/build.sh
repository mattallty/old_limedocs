#!/bin/sh
#
# Run box on the code
#
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
mkdir -p $DIR/../build
box build -c $DIR/../box.json
