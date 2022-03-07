#!/usr/bin/env python3

import os
import json
import logging

logging.basicConfig(
    format='%(asctime)s %(levelname)-8s %(message)s',
    level=logging.DEBUG,
    datefmt='%d-%m-%Y %H:%M:%S')

parentDir = os.path.dirname(os.path.realpath(__file__))
confDir = os.path.join(os.path.dirname(parentDir),'core','config','devices')

dictTransform = {'37-0':'37.currentValue',
                 '37-0-type=setvalue&value=255':'37.targetValue.true',
                 '37-0-type=setvalue&value=1':'37.targetValue.true',
                 '37-0-type=setvalue&value=0':'37.targetValue.false',
                 '38-0':'38.currentValue',
                 '38-0-type=setvalue&value=#slider#':'38.targetValue.#slider#',
                 '38-0-type=setvalue&value=0':'38.targetValue.0',
                 '38-0-type=setvalue&value=20':'38.targetValue.20',
                 '38-0-type=setvalue&value=30':'38.targetValue.30',
                 '38-0-type=setvalue&value=40':'38.targetValue.40',
                 '38-0-type=setvalue&value=50':'38.targetValue.50',
                 '38-0-type=setvalue&value=99':'38.targetValue.99',
                 '38-0-type=setvalue&value=255':'38.targetValue.255',
                 '38-2-type=buttonaction&action=release':'38.Open.false',
                 '43-0':'43.Scene ID',
                 '48-0':'48.Any',
                 '49-1':'49.Air temperature',
                 '49-2':'49.General purpose',
                 '49-3':'49.Illuminance',
                 '49-4':'49.Power',
                 '49-5':'49.Humidity',
                 '49-6':'49.Velocity',
                 '50-0':'50.value-65537',
                 '50-8':'50.value-66049',
                 '50-16':'50.value-66561',
                 '50-20':'50.value-66817',
                 '102-0-type=setvalue&value=1':'102.targetState.255',
                 '102-0-type=setvalue&value=0':'102.targetState.0',
                 '102-2':'102.currentState',
                 '112-41':'112.41',
                 '112-45':'112.44',
                 '112-45':'112.45',
                 '128-0':'128.level',
                 '156-0':'156.state-0',
                }
notFound = []
for root, dirs, files in os.walk(confDir):
    for name in files:
        if name.endswith((".json")):
            logging.debug(name)
            with open(os.path.join(root,name)) as config_file:
                CONF = json.load(config_file)
                logging.debug('Loaded conf file ' + str(CONF))
                if 'commands' in CONF:
                    i=0
                    for command in CONF['commands']:
                        if 'configuration' in command:
                            if ('instance' in command['configuration']):
                                logging.debug('Not transformed conf found')
                                configurationCheck = str(command['configuration']['class'])+'-'+str(command['configuration']['index'])
                                if 'value' in command['configuration'] and command['configuration']['value'] != '':
                                    configurationCheck+='-'+str(command['configuration']['value'])
                                logging.debug(configurationCheck)
                                if configurationCheck in dictTransform :
                                    logging.debug('Found transformation')
                                    exploded = dictTransform[configurationCheck].split('.')
                                    command['configuration'].pop('class')
                                    command['configuration'].pop('value')
                                    command['configuration']['class']=int(exploded[0])
                                    command['configuration']['endpoint']=int(command['configuration']['instance'])-1
                                    command['configuration']['property']=exploded[1]
                                    command['configuration'].pop('instance')
                                    command['configuration'].pop('index')
                                    if len(exploded)>2:
                                        command['configuration']['value']=exploded[2]
                                    CONF['commands'][i]['configuration']=command['configuration']
                                else:
                                    if configurationCheck not in notFound:
                                        notFound.append(configurationCheck)
                        i+=1
            with open(os.path.join(root,name), "w", encoding='utf8') as outfile:
                outfile.write(json.dumps(CONF, indent = 4,ensure_ascii=False))
logging.debug(notFound)
