const fs = require('fs');
const path = require('path');
const { exec } = require("child_process");
const mysql = require('mysql');
const express = require('express');
const app = express();
var rootDir = __dirname.replace('/\"/g, /');
app.use(express.static(rootDir));
const server = require('http').Server(app);
const io = require('socket.io')(server);
const SerialPort = require("serialport");
const Readline = require("@serialport/parser-readline");
const cron = require('node-cron');

var pidCons = -1;
var username = "";
var token = "";
var serverOn = false;
var device = "";
var deviceId = "";
var deviceName = "";
var deviceOff = false;
var userNum = 0;
var timeout = true;

var deviceMessage = '';
var deviceMessageAux = '';
const installationCompleted = "--- System Configuration Dialog ---";
var alreadyCompleted = false; 
var canSend = false;
const unrecognizedCommand = "% Unrecognized command";
const validCommand = "Invalid input detected. Please, introduce it correctly."
var commandSend = 'NO_COMMAND_SEND_YET';
var oneWarn = false;
var connectionRejected = false;

var commandsWithNoResponse = 0;
var loadTime = null;
var logger = null;
var loggerAttempIntrusion = null;

var port = new SerialPort(process.argv[2], {
    baudRate: parseInt(process.argv[4]),
    databits: parseInt(process.argv[5]),
    stopBits: parseInt(process.argv[6]),
    flowControl: process.argv[7],
    lock: process.argv[8]
});

//Initialization parser
const parser = port.pipe(new Readline({ delimiter: ' ' }));

function printLongString (line) { //Divide long strings into short ones
  while (line.length > 108) {
    io.sockets.emit('messages', line.substring(0,108));
    line = line.substring(108);
  }
  return line;
};

parser.on("data", (line) => { //Detects when network device sends a message
 deviceOff = false;

  if (deviceMessage.includes("\""))
    deviceMessage = deviceMessage.substring(0,deviceMessage.length - 1)
  if ((line.includes('\r\n')) && canSend) { //Printing line on HTML

    deviceMessage = deviceMessage.concat(line.substring(0, line.indexOf('\r\n')) + ' ');

    if (deviceMessage.includes(installationCompleted)){ // Verify Device installation completed
      alreadyCompleted = true;
    }

    if (deviceMessage.indexOf('\t') == 0) {
      io.sockets.emit('messages', "<p style='margin-left: 4em'>" + deviceMessage + '</p>');
      console.log(deviceMessage);
    }
    else if (deviceMessage != commandSend + ' '){
        if (!deviceMessage.includes('^') && deviceMessage != unrecognizedCommand + ' '){
          if (commandSend == 'show')
              commandSend = 'NO_COMMAND_SEND_YET';
            if (!deviceMessage.includes(commandSend.split(' ')[0] + ' ') || commandSend == ''){

              if (deviceMessage != ' ') {
                deviceMessage = deviceMessage.split("\b").join("");
                io.sockets.emit('messages', deviceMessage);
                console.log(deviceMessage);
                deviceMessage = '';
              }
            }
          } else if (!oneWarn){
              io.sockets.emit('messages', validCommand);
              oneWarn = true;
          }
        }

    line = line.substring(line.indexOf('\r\n') + 2, line.length);
    deviceMessage = '';
    canSend = false;
  } else
      canSend = true;

    while (line.includes('\r\n')) { //Looking for line feeds or carriage returns
      if (line.indexOf('\r\n') == 0) {
        if (commandSend != 'exit') {
          io.sockets.emit('messages', '<br>');
        }
        line = line.substring(line.indexOf('\r\n') + 2, line.length);
      } else {
        deviceMessageAux = deviceMessage + line.substring(0, line.indexOf('\r\n'));
        if (deviceMessage.indexOf('\t') == 0) {
          io.sockets.emit('messages', "<p style='margin-left: 4em'>" + deviceMessageAux + '</p>');
          console.log(deviceMessage);
        }
          else if (deviceMessageAux != commandSend) //Avoid repeated device messages
                  if (!deviceMessageAux.includes('^')){
                    io.sockets.emit('messages', deviceMessage + line.substring(0, line.indexOf('\r\n')));
                    console.log(deviceMessage + line.substring(0, line.indexOf('\r\n')));
                    deviceMessage = '';
                  }
        deviceMessageAux = '';
        line = line.substring(line.indexOf('\r\n') + 2, line.length);
        if (line.length > 108)
          line = printLongString(line);
      }
      if (commandSend == 'exit')
        break;
    }

  if (commandSend.includes('show'))
      line = line + '  ';

  deviceMessage = deviceMessage.concat(line + ' ');

  if ((deviceMessage.indexOf(':') == deviceMessage.length - 2 || deviceMessage.indexOf('>') == deviceMessage.length - 2) && alreadyCompleted) {
        io.sockets.emit('messages', deviceMessage);
        console.log(deviceMessage);
        deviceMessage = '';
  }
  deviceOff = true;
  loadTime = null;
});

function getCurrentDate () {
  var currentDate = new Date();
  return ("0" + currentDate.getDate()).slice(-2) + "/"
                + ("0" + (currentDate.getMonth() + 1)).slice(-2) + "/"
                + currentDate.getFullYear() + " "
                + ("0" + currentDate.getHours()).slice(-2) + ":"
                + ("0" + currentDate.getMinutes()).slice(-2) + ":"
                + ("0" + currentDate.getSeconds()).slice(-2);
};

function shutdownServer (){

      var sql = "UPDATE device SET in_use = 'NO' WHERE Id = '" + deviceId + "'";
      con.query(sql, function (err, result) {
        if (err) throw err;
      });

      var sql = "UPDATE device SET server_status = 'OFF' WHERE Id = '" + deviceId + "'";
      con.query(sql, function (err, result) {
        if (err) throw err;
        else
            exec("kill -9 "+pidCons, (error, data, getter) => { //Close current Node server process
                if(error){
                  console.log("error",error.message);
                  conn.close();
                  return;
                }
            });
      })
}

io.on('connection', (socket) => {

  io.to(socket.id).emit('checkToken', socket.id);

  if (logger == null)
    logger = fs.createWriteStream(rootDir + "/logs/" + deviceName + ".txt", {
        flags: 'a'
    });

    var flag = true;
    if (userNum == 0) {

      var updateTimeCommand = "UPDATE device SET last_command_time = '"  + getCurrentDate() + "' WHERE Id = '" + deviceId + "'";
      con.query(updateTimeCommand, function (err, result) {
        if (err) throw err;
      });

      serverOn = true;

      console.log(username + " is connected. (" + getCurrentDate() + "h.)");
      logger.write(username + " is connected. (" + getCurrentDate() + "h.)\r\n");
      userNum += 1;

      timeout = false;
      var sql = "UPDATE device SET in_use = 'YES' WHERE Id = '" + deviceId + "'" ;
      con.query(sql, function (err, result) {
        if (err) throw err;
        console.log("DB device 'in_use' updated to YES!");

      });

      var updateUsedBy = "UPDATE device SET used_by = '" + username + "' WHERE Id = '" + deviceId + "'" ;
      con.query(updateUsedBy, function (err, result) {
        if (err) throw err;
        console.log("DB device used_by updated to " + username + "!");

      });
    }

  socket.on('disconnect', () => {
    if (connectionRejected) {
      connectionRejected = false;
      return;
    }

    if (userNum == 1) {
      userNum -= 1;
      console.log(username + " is disconnected. (" + getCurrentDate() + "h.)");
      logger.write(username + " is disconnected. (" + getCurrentDate() + "h.)\r\n");

      token = "";

      var sql = "UPDATE device SET in_use = 'NO' WHERE Id = '" + deviceId + "'";
      con.query(sql, function (err, result) {
        if (err) throw err;
        console.log("DB device 'in_use' updated to NO!");
      });

    }
  });

  socket.on('command', (command) => {

    var updateTimeCommand = "UPDATE device SET last_command_time = '"  + getCurrentDate() + "' WHERE Id = '" + deviceId + "'";
    con.query(updateTimeCommand, function (err, result) {
      if (err) throw err;
    });

    if (deviceOff){
      if (loadTime == null)
        loadTime = (new Date().getTime()) / 1000; // convert milliseconds to seconds.
      commandsWithNoResponse += 1;
      if (commandsWithNoResponse >= 5){
        var currentTime = (new Date().getTime()) / 1000;
          if (currentTime - loadTime >= 60) { //Passed more than 1 minute
            io.sockets.emit('messages', 'El dispositivo parece no responder. Si esto sigue así, contacta con el profesorado. <br>');
            loadTime = null;
          }
      }
      commandsWithNoResponse += 1;
    }

    if (command == 'reload') {
      deviceMessage = '';
      deviceMessageAux = '';
      commandSend = 'NO_COMMAND_SEND_YET';
      canSend = false;
      oneWarn = false;
      alreadyCompleted = false;
    }

    if (!command == ''){
      if (command == 'show ?'){
        flag = true;
        io.sockets.emit('messages', 'Send ONE ESPACE to view more commands.');
        io.sockets.emit('messages', 'Send ENTER to escape.');
        io.sockets.emit('messages', '<br>');
        commandSend = command;
        port.write(command);
        console.log(username + " used command: " + command + " (" + getCurrentDate() + "h.)");
        logger.write(username + " used command: " + command + " (" + getCurrentDate() + "h.)\r\n");
      } else
      if (command == ' '){
          port.write(' ');
          console.log(username + " used command: ESPACE BAR (" + getCurrentDate() + "h.)");
          logger.write(username + " used command: ESPACE BAR (" + getCurrentDate() + "h.)\r\n");
      } else {
          commandSend = command;
          port.write(command + '\r\n');
          port.write(' ');
          console.log(username + " used command: " + command + " (" + getCurrentDate() + "h.)");
          logger.write(username + " used command: " + command + " (" + getCurrentDate() + "h.)\r\n");
        }
    } else {
      port.write('\r\n');
      port.write(' ');
      if (commandSend == 'show ?' && flag){
        port.write('\r\n');
        port.write(' ');
        flag = false;
      }
      console.log(username + " used command: ENTER (" + getCurrentDate() + "h.)");
      logger.write(username + " used command: ENTER (" + getCurrentDate() + "h.)\r\n");
    }

    if (command == 'exit'){
      setTimeout(function() {
        port.write('\r\n');
        port.write(' ');
        io.sockets.emit('enable', '');
      }, 2000);
    }

    oneWarn = false;
    canSend = true;
    deviceMessage = '';
  });

  socket.on('token', ({ token, socketId, usuario }) => {
    var checkToken = "SELECT token from device WHERE Id = '" + deviceId + "'" ;
    con.query(checkToken, function (err, result) {
      if (err) throw err;
      if (result[0].token != token){

        if (loggerAttempIntrusion == null)
          loggerAttempIntrusion = fs.createWriteStream(rootDir + "/logs/AttempIntrusionConsole.txt", {
              flags: 'a'
          });

          io.to(socketId).emit('redirect', '/forbidden');
          loggerAttempIntrusion.write("El usuario " + usuario + " intentó entrar en el dispositivo " +  device + ". (" + getCurrentDate() + "h.)\r\n");
          connectionRejected = true;

      }

    });
  });

  socket.on('deviceName', () => {
      io.sockets.emit('returnName', deviceName);
  });
});

function parseDate(date){

    bound = date.indexOf(' ');
    var dateData = date.slice(0, bound).split('/');
    var timeData = date.slice(bound+1, -1).split(':');

    time = Date.UTC(dateData[2],dateData[1]-1,dateData[0],timeData[0],timeData[1],timeData[2]);

    return time;
}

cron.schedule('5 * * * * *', function() { //Checking if user is absent to close server

  console.log('Checking if user is absent...');

if (serverOn) {

	  var sql = "SELECT last_command_time from device WHERE Id = '" + deviceId + "'" ;
	  con.query(sql, function (err, result) {
	    if (err) throw err;
	    if (result[0].last_command_time != null) {
		    var diff = Math.abs(parseDate(getCurrentDate()) - parseDate(result[0].last_command_time));
		    var minutes = Math.floor((diff/1000)/60);

		    if (minutes >= 10 || userNum == 0) {
		      io.sockets.emit('serverOff', '');
		      shutdownServer();
		    }
	     }
	  });
} else {
	var sql = "SELECT server_up_time from device WHERE Id = '" + deviceId + "'" ;
	  con.query(sql, function (err, result) {
	    if (err) throw err;
	    if (result[0].server_up_time != null) {
		    var diff = Math.abs(parseDate(getCurrentDate()) - parseDate(result[0].server_up_time));
		    var minutes = Math.floor((diff/1000)/60);
			
		    if (minutes >= 1) {
		      io.sockets.emit('serverOff', '');
		      shutdownServer();
		    }
	    }
    });
  }
	
});

server.listen(process.argv[3], function(){
  if (userNum == 0)
  var sql = "UPDATE device SET in_use = 'NO' WHERE Id = '" + deviceId +"'";
  con.query(sql, function (err, result) {
    if (err) throw err;
    console.log("Device DB 'in_use' updated to NO!");
  });

  if (deviceId == "")
   deviceId = process.argv[9];

  var updateTimeServerUp = "UPDATE device SET server_up_time = '"  + getCurrentDate() + "' WHERE Id = '" + deviceId + "'";
      con.query(updateTimeServerUp, function (err, result) {
        if (err) throw err;
      });
  console.log('Server (' + process.argv[2] + ') running on port: ' + process.argv[3]);

  var i;
  for (i = 10; i <= process.argv.length-4; i++)
    device = device.concat(process.argv[i] + " ");

  var updateStatus = "UPDATE device SET server_status = 'ON' WHERE Id = '" + deviceId + "'";
        con.query(updateStatus, function (err, result) {
          if (err) throw err;
          console.log("DB device 'server_status' updated to ON!");
        });

});

var con = mysql.createConnection({
  host: "<HOST>",
  user: "<DB_USERNAME>",
  password: "<DB_PASSWORD>",
  database: "<DB_NAME>"
});

con.connect(function(err) {
  if (err) throw err;
  console.log("Connected to DB!");
});


app.get('/', function (req,res) {
  if (pidCons == -1)
    pidCons = req.query.pidConsole;
  if (username == "")
    username = req.query.user;
  if (token == "")
    token = req.query.token;
  if (deviceName == "")
    deviceName = req.query.deviceName;
  res.redirect('/terminal' + deviceId + '?port=' + process.argv[3] + '&username=' + username + '&token=' + req.query.token);
 
});
