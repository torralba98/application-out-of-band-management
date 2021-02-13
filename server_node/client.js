var socket = null;
var user = null;

function stablishPortConnection(port, username, web_url) {

  if (user == null)
    user = username + ' ';

  if (socket == null)
    socket = io.connect(web_url + ':' + port, { 'forceNew': true});

    socket.on('messages',function(data){
      render(data);
    });

    socket.on('enable', () => {
      enableButton();
    });

    socket.on('disable', () => {
      disableButton();
    });

    socket.on('serverOff', function() {
      serverOff();
    });

    socket.on('redirect', function(destination) {
      window.location.href = destination;
    });

    socket.on('checkToken', function(socketId) {
      checkTokenClient(socketId);
    });

    socket.on('returnName', function(deviceName) {
      document.getElementById("title").textContent = deviceName;
    });
}

function checkToken (token,socketId,username) {
    socket.emit('token', { token: token, socketId: socketId, usuario: username });
}

function getName () {
    socket.emit('deviceName', " ");
}

function render (data) { //Print device messages on HTML
  var html = document.createElement('div');
  html.innerHTML = `<strong> <div class='alert-success'> ${data}</div></strong>`;
  document.getElementById('messages').appendChild(html);
  document.getElementById('comando').scrollIntoView();
};

function sendCommand (e) { //Print commands
  var command = document.getElementById('comando').value;
  var html = document.createElement('div');
  socket.emit('command', command);

  html.innerHTML = `<br><div class='alert-success'>
                    <p style='margin-left: 2em; background-color:inherit;'> ${user}$ <em> ${command}</em>
                    </div><br>`;

  document.getElementById('messages').appendChild(html);
  document.getElementById('comando').scrollIntoView();
}

function checkTokenClient (socketId) { //Print commands
  var html = document.createElement('script');

  html.innerHTML = `checkToken(token,"${socketId}",username);`;

  document.getElementById('alertaObserv').appendChild(html);
  return false;
}

function enableButton () {
  const boton = document.getElementById("enviar");
  setTimeout(function(){ boton.removeAttribute('disabled'); }, 1500);
}

function disableButton () {
  const boton = document.getElementById("enviar");
  boton.setAttribute('disabled', "true");
}

function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

async function serverOff() {

  var html = document.createElement('div');

  html.innerHTML = `<div id='dialog'>
                      <div id='dialog-bg'>
                        <div id='dialog-title'>AVISO</div>
                        <div id='dialog-description'>Se ha cerrado el server debido a que has estado más de 10 minutos inactivo. Serás redireccionado en 5 segundos...</div>
                        </div>
                      </div>
                      <div class=' pageCover'></div>`;

  document.getElementById('principal').appendChild(html);

  await sleep(5000);

  window.location.href = '/inicio';

}
