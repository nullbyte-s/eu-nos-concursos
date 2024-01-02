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

verificarAutenticacao().then(status => {
    if (status === 'success') {
        document.getElementById('userNavItem').innerHTML = `
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Usuário
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="#" onclick="panelClick();carregarPagina('authenticated.html');carregarRodape();loadSearchResults();">Painel</a>
                    <a class="dropdown-item" href="#" onclick="logout()">Sair</a>
                </div>
            </div>
        `;
        carregarPagina('authenticated.html');
        carregarRodape();
        loadSearchResults();
    }
});