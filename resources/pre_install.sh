#!/bin/bash

set -x  # make sure each command is printed in the terminal
echo "Pre installation de l'installation/mise à jour des dépendances zwavejs"

BASEDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

cd $BASEDIR
source ../core/config/zwavejs.config.ini  &> /dev/null
echo "Wanted Version: $wantedVersion"
rm -R zwave-js-ui
git clone --branch v$wantedVersion --depth 1 https://github.com/zwave-js/zwave-js-ui
echo "Pre install finished"

