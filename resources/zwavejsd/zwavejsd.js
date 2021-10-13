/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/
var Jeedom = require('./jeedom/jeedom.js');
const fs = require('fs');
import { Driver } from "zwave-js";


const args = Jeedom.getArgs()
if(typeof args.loglevel == 'undefined'){
  args.loglevel = 'debug';
}
Jeedom.log.setLevel(args.loglevel)

Jeedom.log.info('Start zwavejsd')
Jeedom.log.info('Log level on  : '+args.loglevel)
Jeedom.log.info('Socket port : '+args.socketport)
Jeedom.log.info('Device port : '+args.port)
Jeedom.log.info('PID file : '+args.pid)
Jeedom.log.info('Apikey : '+args.apikey)
Jeedom.log.info('Callback : '+args.callback)
Jeedom.log.info('Cycle : '+args.cycle)

Jeedom.write_pid(args.pid)
Jeedom.com.config(args.apikey,args.callback,args.cycle)

/************************START ZWAVEJS SERVER*****************************/

try {
    const driver = new Driver(args.port);
    driver.on("error", (e) => {
        Jeedom.log.error(e);
    });
    driver.once("driver ready", () => {
        Jeedom.log.info('Driver ready')
        /*
        Now the controller interview is complete. This means we know which nodes
        are included in the network, but they might not be ready yet.
        The node interview will continue in the background.
        */
    
        driver.controller.nodes.forEach((node) => {
            // e.g. add event handlers to all known nodes
        });
    
       
    });
    // Start the driver. To await this method, put this line into an async method
    Jeedom.log.info('Start driver')
    await driver.start();
} catch (e) {
  console.log(e);
  Jeedom.log.error('Main error on  zwavejsd : '+JSON.stringify(e));
}

/**********************START HTTP SERVER********************************/
Jeedom.http.config(args.socketport,args.apikey)

Jeedom.http.app.get('/device/all', function(req, res) {
    Jeedom.log.debug('/device/all');
    try {

    } catch (e) {
        Jeedom.log.error('Error on operation : '+JSON.stringify(e));
        res.setHeader('Content-Type', 'application/json');
        res.send({state:"nok",result : JSON.stringify(e)});
      }
});
