var token = localStorage.getItem('token');
var userNavItem = document.getElementById('userNavItem');

carregarPagina("home.html");
carregarRodape();

function preload() {
    setTimeout(function () { $(".loader").fadeOut("slow"); }, 500);
}

$(document).ready(function () {
    $("#navbarItems button").on("click", function () {
        $("#navbarItems button").removeClass("active");
        $(this).addClass("active");
    });
});

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
        var token = localStorage.getItem('token');

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

function logout() {
    localStorage.removeItem('token');
    setTimeout(() => {
        carregarPagina('home.html').then(() => {
            location.reload();
        });
    }, 500);
}