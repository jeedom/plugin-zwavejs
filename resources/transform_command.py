#!/usr/bin/env python3

import os
import json
import logging
import collections

logging.basicConfig(
    format='%(asctime)s %(levelname)-8s %(message)s',
    level=logging.DEBUG,
    datefmt='%d-%m-%Y %H:%M:%S')

parentDir = os.path.dirname(os.path.realpath(__file__))
confDir = os.path.join(os.path.dirname(parentDir),'core','config','devices')

filter =''

                
countALL=0
countcommands=0
notFound = {}
for root, dirs, files in os.walk(confDir):
    for name in files:
        if name.endswith((".json")):
            if (filter in name)  or (filter in root):
                with open(os.path.join(root,name)) as config_file:
                    countALL+=1
                    CONF = json.load(config_file)
                    if 'commands' in CONF:
                        countcommands+=1
                        i=0
                        logging.debug(CONF)
                        for command in CONF['commands']:
                            if 'configuration' in command:
                                if str(command['configuration']['class']) == '128' and str(command['configuration']['endpoint']) == '0':
                                    logging.debug('Found Battery')
                                    CONF['commands'].pop(i)
                                    if 'properties' in CONF:
                                        CONF['properties']['Battery']={}
                                    else:
                                        CONF['properties']={}
                                        CONF['properties']['Battery']={}
                                if str(command['configuration']['class']) == '43' and str(command['configuration']['endpoint']) == '0':
                                    logging.debug('Found Scene')
                                    CONF['commands'].pop(i)
                                    if 'properties' in CONF:
                                        CONF['properties']['Scene']={}
                                    else:
                                        CONF['properties']={}
                                        CONF['properties']['Scene']={}
                                if str(command['configuration']['class']) == '37' and str(command['configuration']['endpoint']) == '0' and (str(command['configuration']['property']) == 'currentValue' or str(command['configuration']['property']) == 'targetValue'):
                                    logging.debug('Found Switch')
                                    CONF['commands'].pop(i)
                                    if 'properties' in CONF:
                                        CONF['properties']['Switch']={}
                                    else:
                                        CONF['properties']={}
                                        CONF['properties']['Switch']={}
                                if str(command['configuration']['class']) == '49' and str(command['configuration']['endpoint']) == '0' and (str(command['configuration']['property']) == 'Illuminance'):
                                    logging.debug('Found Switch')
                                    CONF['commands'].pop(i)
                                    if 'properties' in CONF:
                                        CONF['properties']['Luminance']={}
                                    else:
                                        CONF['properties']={}
                                        CONF['properties']['Luminance']={}
                                if str(command['configuration']['class']) == '49' and str(command['configuration']['endpoint']) == '0' and (str(command['configuration']['property']) == 'Air temperature'):
                                    logging.debug('Found Switch')
                                    CONF['commands'].pop(i)
                                    if 'properties' in CONF:
                                        CONF['properties']['Temperature']={}
                                    else:
                                        CONF['properties']={}
                                        CONF['properties']['Temperature']={}
                            i+=1
                        if len(CONF['commands']) == 0:
                            CONF.pop('commands')
                        logging.debug(CONF)
                with open(os.path.join(root,name), "w", encoding='utf8') as outfile:
                    outfile.write(json.dumps(CONF, indent = 4,ensure_ascii=False))
logging.debug('ALL : ' +str(countALL))
logging.debug('COMMANDS : ' +str(countcommands))
