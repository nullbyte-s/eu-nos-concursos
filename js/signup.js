function submitRegisterForm() {

    document.getElementById('registerButton').disabled = true;

    var nome = document.getElementById('name').value;
    var usuario = document.getElementById('username').value;
    var senha = document.getElementById('password').value;

    // Verificar se os campos não estão vazios
    if (nome.trim() === '' || usuario.trim() === '' || senha.trim() === '') {
        displayNotification({ status: 'error', message: 'Por favor, preencha todos os campos.' });
        document.getElementById('registerButton').disabled = false;
        return;
    }

    fetch('backend/register.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ usuario, senha, nome })
    })
        .then(response => response.json())
        .then(data => {
            displayNotification(data);

            if (data.status === 'success' && data.redirect) {
                setTimeout(() => {
                    carregarPagina(data.redirect);
                }, 1500);
            } else {
                document.getElementById('registerButton').disabled = false;
            }
        })
        .catch(error => {
            displayNotification({ status: 'error', message: 'Erro ao enviar dados: ' + error });
        });
}