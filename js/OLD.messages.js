// Dados recebidos do backend
var receivedMessages = obterMensagens();

// Função para carregar as mensagens na tabela
function loadMessages() {
  var tableBody = document.getElementById('messageTableBody');

  // Limpar tabela antes de adicionar novas mensagens
  tableBody.innerHTML = '';

  // Adicionar cada mensagem à tabela
  receivedMessages.forEach(function (message) {
    var row = tableBody.insertRow();
    var cellName = row.insertCell(0);
    var cellEmail = row.insertCell(1);
    var cellMessage = row.insertCell(2);

    cellName.innerHTML = message.nome;
    cellEmail.innerHTML = message.email;
    cellMessage.innerHTML = message.mensagem;
  });
}

// Carregar mensagens ao carregar a página
window.onload = function () {
  loadMessages();
};