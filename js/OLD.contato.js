function submitForm() {
    // Obter dados do formulário
    var nome = document.getElementById('nome').value;
    var email = document.getElementById('email').value;
    var mensagem = document.getElementById('mensagem').value;

    // Criar objeto JSON
    var formData = {
        nome: nome,
        email: email,
        mensagem: mensagem
    };

    // Enviar dados
    try {
        if (inserirMensagem(formData)) {
            throw new Error("Envio da mensagem falhou!");
        }
        $(notificacao).notify("Mensagem enviada com sucesso!", "success");
        document.getElementById('contactForm').reset();
    } catch (error) {
        $(notificacao).notify(error.message, "error");
        document.getElementById('contactForm').reset();
    }

}