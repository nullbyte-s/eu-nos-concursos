var token = localStorage.getItem('token');

if (!token) {
    carregarPagina("home.html");
    carregarRodape();
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
                    localStorage.setItem('usuario', data.usuario);
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

function logout() {
    localStorage.removeItem('token');
    setTimeout(() => {
        carregarPagina('home.html').then(() => {
            location.reload();
        });
    }, 500);
}

function apagarConta() {
    // Verificar se o token está presente
    if (!token) {
        console.error('Token não encontrado. Não é possível apagar a conta.');
        return;
    }

    // Exibir o modal de confirmação
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