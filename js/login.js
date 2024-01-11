function submitLoginForm() {
    document.getElementById('loginButton').disabled = true;

    // Obter dados do formulário
    var usuario = document.getElementById('username').value;
    var senha = document.getElementById('password').value;

    // Verificar se os campos não estão vazios
    if (usuario.trim() === '' || senha.trim() === '') {
        displayNotification({ status: 'error', message: 'Por favor, preencha todos os campos.' });
        document.getElementById('loginButton').disabled = false;
        return;
    }

    fetch('backend/login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ usuario, senha })
    })
        .then(response => response.json())
        .then(data => {
            displayNotification(data);
            if (data.status === 'success' && data.token) {
                localStorage.setItem('token', data.token);
                if (data.redirect) {
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            } else {
                document.getElementById('loginButton').disabled = false;
            }
        })
        .catch(error => {
            displayNotification({ status: 'error', message: 'Erro ao enviar dados: ' + error });
        });
}

function initAuthenticatedPageLoad() {
    try {
        verificarSessao(function (data) {
            if (data.usuario) {
                carregarPagina('authenticated.html').then(function () {
                    carregarRodape();
                    loadSearchResults();
                    if (data.usuario === 'admin') {
                        try {
                            const response = fetch('backend/contact_table.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                }
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data && data.length > 0) {
                                        document.getElementById('mensagensRecebidas').style.display = 'flex';
                                        visualizarMensagens(data);
                                    }
                                })
                        } catch (error) {
                            console.error('Erro ao obter dados das mensagens:', error);
                        }
                    }
                    panelClick();
                }).catch(function (error) {
                    console.error('Erro ao carregar a página:', error);
                });
            } else {
                console.log('Usuário não autenticado');
            }
        });
    } catch (error) {
        console.error('Erro ao obter usuário:', error);
    }
}

verificarAutenticacao().then(status => {
    if (status === 'success') {
        document.getElementById('userNavItem').innerHTML = `
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Usuário
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="#" onclick="panelClick();initAuthenticatedPageLoad();">Painel</a>
                    <a class="dropdown-item" href="#" onclick="logout()">Sair</a>
                </div>
            </div>
        `;

        // window.addEventListener('load', async function () {
        //     initAuthenticatedPageLoad();
        // });

        initAuthenticatedPageLoad();

        // } else if (status === 'error') {
    } else {
        fetch('backend/login.php?logout=1', {
            method: 'GET',
        })
        if (token) {
            localStorage.removeItem('token');
        }
        setTimeout(() => {
            carregarPagina('home.html').then(() => {
                scrollToTop();
            });
        }, 500);
        carregarRodape();
    }
});