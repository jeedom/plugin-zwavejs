#!/bin/bash

set -x  # make sure each command is printed in the terminal
echo "Post installation de l'installation/mise à jour des dépendances zwavejs"

BASEDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

cd $BASEDIR
cd zwave-js-ui
sudo yarn install
sudo yarn run build
chown -R www-data:www-data *

if [ -e /dev/ttyAMA0 ];  then 
sudo sed -i 's/console=ttyAMA0,115200//; s/kgdboc=ttyAMA0,115200//' /boot/cmdline.txt
sudo sed -i 's|[^:]*:[^:]*:respawn:/sbin/getty[^:]*ttyAMA0[^:]*||' /etc/inittab
fi

if [ -e /dev/ttymxc0 ];  then 
sudo systemctl mask serial-getty@ttymxc0.service
sudo systemctl stop serial-getty@ttymxc0.service
fi
if [ -e /dev/ttyAMA0 ];  then 
sudo systemctl mask serial-getty@ttyAMA0.service
sudo systemctl stop serial-getty@ttyAMA0.service
fi

RPI_BOARD_REVISION=`grep Revision /proc/cpuinfo | cut -d: -f2 | tr -d " "`
if [[ $RPI_BOARD_REVISION ==  "a02082" || $RPI_BOARD_REVISION == "a22082" || $RPI_BOARD_REVISION == "a020d3" ]]
then
  systemctl disable hciuart
  if [[ ! `grep "dtoverlay=pi3-miniuart-bt" /boot/config.txt` ]]
  then
    echo "Raspberry Pi 3 Detected. If you use a Razberry board you must Disable Bluetooth"
    echo "Please add 'dtoverlay=pi3-miniuart-bt' to the end of the file /boot/config.txt"
    echo "And reboot your Raspberry Pi"
  fi
fi
echo "Everything is successfully installed!"

