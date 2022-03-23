#!/usr/bin/env python3

import os
import json
import logging
from pathlib import Path

logging.basicConfig(
    format='%(asctime)s %(levelname)-8s %(message)s',
    level=logging.DEBUG,
    datefmt='%d-%m-%Y %H:%M:%S')

parentDir = os.path.dirname(os.path.realpath(__file__))
confDir = os.path.join(os.path.dirname(parentDir),'core','config','devices')
issue=[]
for root, dirs, files in os.walk(confDir):
    for name in files:
        if name.endswith((".json")):
            imageName = name.replace('.json','')
            list = name.split('_',1)
            if (len(list)>1) :
                ids = list[0]
                finalname = list[1]
                logging.debug(finalname)
                finalImage = finalname.replace('.json','')
                listIds = ids.split('.')
                if len(listIds)>2:
                    if Path(os.path.join(root,finalname)).is_file():
                        logging.debug("New file "+finalname+" exists")
                        with open(os.path.join(root,finalname)) as config_file:
                            CONF = json.load(config_file)
                            if listIds[1] in CONF['versions']:
                                if listIds[2] in CONF['versions'][listIds[1]]:
                                    pass
                                else:
                                    CONF['versions'][listIds[1]].append(listIds[2])
                            else:
                                CONF['versions'][listIds[1]]=[listIds[2]]
                        with open(os.path.join(root,finalname), "w", encoding='utf8') as outfile:
                            outfile.write(json.dumps(CONF, indent = 4,ensure_ascii=False))
                        if Path(os.path.join(root,imageName+'.jpg')).is_file():
                            os.remove(os.path.join(root,imageName+'.jpg'))
                        if Path(os.path.join(root,imageName+'.png')).is_file():
                            os.remove(os.path.join(root,imageName+'.png'))
                        if Path(os.path.join(root,imageName+'.json')).is_file():
                            os.remove(os.path.join(root,imageName+'.json'))
                    else:
                        with open(os.path.join(root,name)) as config_file:
                            CONF = json.load(config_file)
                            CONF['versions']= {listIds[1]:[listIds[2]]}
                            if 'commands' in CONF:
                                command = CONF['commands']
                                CONF.pop('commands')
                                CONF['commands'] = command
                        with open(os.path.join(root,finalname), "w", encoding='utf8') as outfile:
                            outfile.write(json.dumps(CONF, indent = 4,ensure_ascii=False))
                        if Path(os.path.join(root,imageName+'.jpg')).is_file():
                            os.rename(os.path.join(root,imageName+'.jpg'),os.path.join(root,finalImage+'.jpg'))
                        if Path(os.path.join(root,imageName+'.png')).is_file():
                            os.rename(os.path.join(root,imageName+'.png'),os.path.join(root,finalImage+'.jpg'))
                        if Path(os.path.join(root,imageName+'.json')).is_file():
                            os.remove(os.path.join(root,imageName+'.json'))
            else:
                issue.append(name)
logging.debug(issue)