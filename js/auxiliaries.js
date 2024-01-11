var token = localStorage.getItem('token');

if (!token) {
    carregarPagina('home.html').then(() => {
        carregarRodape();
    });
}

function preload() {
    setTimeout(function () { $(".loader").fadeOut("slow"); }, 500);
}

$(document).ready(function () {
    $("#navbarItems button").on("click", function () {
        $("#navbarItems button").removeClass("active");
        $(this).addClass("active");
    });
});

function panelClick() {
    $(".dropdown-item").on("click", function () {
        $("#navbarItems button").removeClass("active");
    });
}

function displayNotification(response) {
    var message = response.message;

    if (response.status === 'success') {
        $.notify(message, 'success');
    } else {
        $.notify(message, 'error');
    }
}

function handleEnterKey(event, submitFunction) {
    if (event.key === 'Enter') {
        submitFunction();
    }
}

function scrollToTop() {
    var currentPosition = document.documentElement.scrollTop || document.body.scrollTop;
    var targetPosition = 0;
    var distance = targetPosition - currentPosition;
    var duration = 500;

    function animateScroll(timestamp) {
        var progress = Math.min(1, (timestamp - start) / duration);
        document.documentElement.scrollTop = document.body.scrollTop = currentPosition + distance * progress;
        if (progress < 1) {
            requestAnimationFrame(animateScroll);
        }
    }

    var start = null;

    requestAnimationFrame(function (timestamp) {
        start = timestamp;
        animateScroll(timestamp);
    });
}

function verificarAutenticacao() {
    return new Promise((resolve, reject) => {

        if (!token) {
            resolve('error');
            return;
        }

        fetch('backend/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ token: token })
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    resolve('success');
                } else {
                    resolve('error');
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                resolve('error');
            });
    });
}

function verificarSessao(callback) {
    fetch('backend/get_session.php')
        .then(response => response.json())
        .then(data => {
            callback(data);
        })
        .catch(error => {
            console.error('Erro ao verificar sessão:', error);
        });
}

function submitMessage() {
    // Obter valores dos campos
    var nome = document.getElementById('nome').value;
    var email = document.getElementById('email').value;
    var mensagem = document.getElementById('mensagem').value;

    // Verificar se os campos não estão vazios
    if (nome.trim() === '' || email.trim() === '' || mensagem.trim() === '') {
        document.querySelector('#notificacao').innerHTML = '<div class="alert alert-danger">Por favor, preencha todos os campos.</div>';
        setTimeout(function () {
            document.querySelector('#notificacao').innerHTML = '';
        }, 1500);
        return;
    }

    // Desativar temporariamente o botão "Enviar"
    document.querySelector('#notificacao').innerHTML = '';
    // document.querySelector('button').disabled = true;
    document.getElementById('sendButton').disabled = true;

    // Obter os dados do formulário
    const formData = new FormData(document.getElementById('contactForm'));

    // Enviar os dados para o backend usando AJAX
    $.ajax({
        url: 'backend/contact.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        contentType: false,
        processData: false,
        success: function (data) {
            document.querySelector('#notificacao').innerHTML = `<div class="alert alert-${data.status === 'success' ? 'success' : 'danger'}">${data.message}</div>`;
            document.getElementById('contactForm').reset();
        },
        error: function (error) {
            document.querySelector('#notificacao').innerHTML = '<div class="alert alert-danger">Erro no envio da mensagem.</div>';
        },
        complete: function () {
            setTimeout(function () {
                document.querySelector('#notificacao').innerHTML = '';
            }, 1500);
            setTimeout(() => {
                carregarPagina('home.html').then(() => {
                    scrollToTop();
                });
                $("#navbarItems button").removeClass("active");
            }, 2200);
        }
    });
}

function logout() {
    fetch('backend/login.php?logout=1', {
        method: 'GET',
    })
        .then(response => {
            if (response.ok) {
                localStorage.removeItem('token');
                setTimeout(() => {
                    carregarPagina('home.html').then(() => {
                        location.reload();
                    });
                }, 500);
            } else {
                console.error('Erro ao realizar logout');
            }
        })
        .catch(error => {
            console.error('Erro ao realizar logout:', error);
        });
}

function apagarConta() {
    if (!token) {
        console.error('Token não encontrado. Não é possível apagar a conta.');
        return;
    }

    // Modal de confirmação
    var confirmDeleteAccountModal = new bootstrap.Modal(document.getElementById('confirmDeleteAccountModal'));
    confirmDeleteAccountModal.show();

    fetch('backend/delete_account.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ token: token })
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                confirmDeleteAccountModal.hide();
                alert('Conta apagada com sucesso.');
                setTimeout(() => {
                    localStorage.removeItem('token');
                    carregarPagina('home.html').then(() => {
                        location.reload();
                    });
                }, 500);
            } else {
                confirmDeleteAccountModal.hide();
                alert('Falha ao apagar a conta. Por favor, tente novamente.');
            }
        })
        .catch(error => {
            // Fechar o modal em caso de erro na requisição
            confirmDeleteAccountModal.hide();
            console.error('Erro na requisição:', error);
        });
}

function checkNotificationStatus() {
    fetch('backend/check_notification_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': token
        },
        body: JSON.stringify({ action: 'check' })
    })
        .then(response => {
            // Verificar se a resposta contém um erro HTTP
            if (!response.ok) {
                throw new Error('Erro HTTP: ' + response.status);
            }
            // Retornar a resposta como JSON
            return response.json();
        })
        .then(data => {
            // Verificar se a resposta é um JSON válido
            if (data && typeof data === 'object') {
                var checkbox = document.getElementById('s1-14');
                checkbox.checked = data.notificacao === 1;
                var emailInput = document.getElementById('email');
                emailInput.value = data.email;
            } else {
                throw new Error('Resposta inválida: ' + JSON.stringify(data));
            }
        })
        .catch(error => {
            console.error('Erro ao verificar o status da notificação:', error);
        });
}


function switchNotifications() {
    var emailInput = document.getElementById('email');
    var checkbox = document.getElementById('s1-14');

    // Verificar se a caixa de seleção está marcada
    if (checkbox.checked) {
        var email = emailInput.value;
        addEmailToTable(email, token);
    } else {
        removeEmailFromTable(token);
        emailInput.value = '';
    }
}

function addEmailToTable(email, token) {
    fetch('backend/switch_notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': token
        },
        body: JSON.stringify({ email, action: 'add' })
    })
        .then(response => response.json())
        .then(data => {
            displayNotification(data);
        })
        .catch(error => {
            console.error('Erro ao enviar dados para o backend:', error);
        });
}

function removeEmailFromTable(token) {
    fetch('backend/switch_notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': token
        },
        body: JSON.stringify({ action: 'remove' })
    })
        .then(response => response.json())
        .then(data => {
            displayNotification(data);
        })
        .catch(error => {
            console.error('Erro ao enviar dados para o backend:', error);
        });
}

function setupContactTable() {
    var contactContainer = document.getElementById('contactContainer');
    if (!contactContainer) {
        console.error('Elemento #contactContainer não encontrado.');
        return;
    }

    // Criar tabela
    var table = document.createElement('table');
    table.classList.add('table', 'table-striped', 'table-bordered');

    // Adicionar rótulos das colunas
    var thead = document.createElement('thead');
    var tr = document.createElement('tr');
    tr.innerHTML = '<th>Nome</th><th>Email</th><th>Mensagem</th>';
    thead.appendChild(tr);
    table.appendChild(thead);

    // Adicionar corpo da tabela
    var tbody = document.createElement('tbody');
    table.appendChild(tbody);

    // Adicionar tabela ao contêiner
    contactContainer.appendChild(table);

    return tbody;
}

function visualizarMensagens(data) {
    var tableBody = setupContactTable();

    // Limpar o conteúdo atual da tabela
    tableBody.innerHTML = '';

    // Iterar sobre os dados recebidos e preencher a tabela
    data.forEach(function (row) {
        var tr = document.createElement('tr');
        tr.innerHTML = '<td>' + row.nome + '</td>' +
            '<td>' + row.email + '</td>' +
            '<td>' + row.mensagem + '</td>';
        tableBody.appendChild(tr);
    });

    // Adicionar botão para limpar todas as mensagens
    var clearButton = document.createElement('button');
    clearButton.textContent = 'Limpar Mensagens';
    clearButton.classList.add('btn', 'btn-danger', 'mt-3');
    clearButton.addEventListener('click', function () {
        limparMensagens();
    });

    // Adicionar o botão abaixo da tabela
    var tableContainer = document.getElementById('contactContainer');
    tableContainer.appendChild(clearButton);
}

function limparMensagens() {
    fetch('backend/clear_messages.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                console.error('Erro ao limpar mensagens:', data.message);
            }
        })
        .catch(error => {
            console.error('Erro ao limpar mensagens:', error);
        });
}