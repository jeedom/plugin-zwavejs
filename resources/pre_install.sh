#!/bin/bash

set -x  # make sure each command is printed in the terminal
echo "Pre installation de l'installation/mise à jour des dépendances zwavejs"

BASEDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

cd $BASEDIR
rm -R zwavejs2mqtt
git clone https://github.com/zwave-js/zwavejs2mqtt
echo "Pre install finished"

