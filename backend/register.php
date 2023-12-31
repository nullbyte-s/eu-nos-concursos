<?php
// Conexão com o banco de dados
$dsn = 'sqlite:db.sqlite3';

try {
    $conexao = new PDO($dsn);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar a quantidade atual de usuários cadastrados
    $contarUsuariosSql = "SELECT COUNT(*) FROM usuarios";
    $stmtContarUsuarios = $conexao->query($contarUsuariosSql);
    $quantidadeUsuarios = $stmtContarUsuarios->fetchColumn();

    // Definir o limite máximo de usuários
    $limiteUsuarios = 50;

    if ($quantidadeUsuarios >= $limiteUsuarios) {
        // Se atingir o limite, envie uma resposta JSON com erro
        $response = ['status' => 'error', 'message' => 'Limite máximo alcançado. Não é possível cadastrar mais usuários.'];
    } else {
        // Obter os dados do corpo da requisição POST
        $data = json_decode(file_get_contents("php://input"));
        $usuario = $data->usuario;
        $senha = $data->senha;
        $nome = $data->nome;

        // Verificar se o usuário já existe no banco de dados
        $verificarUsuarioSql = "SELECT COUNT(*) FROM usuarios WHERE usuario = :usuario";
        $stmtVerificar = $conexao->prepare($verificarUsuarioSql);
        $stmtVerificar->bindParam(":usuario", $usuario, PDO::PARAM_STR);
        $stmtVerificar->execute();

        $usuarioExistente = $stmtVerificar->fetchColumn();

        if ($usuarioExistente > 0) {
            // Se o usuário já existe, envia uma resposta JSON com erro
            $response = ['status' => 'error', 'message' => 'Usuário já existe. Escolha outro nome de usuário.'];
        } else {
            // Cria um hash da senha
            $hashSenha = password_hash($senha, PASSWORD_DEFAULT);

            // Armazena o nome de usuário e o hash da senha no banco de dados
            $inserirUsuarioSql = "INSERT INTO usuarios (usuario, senha, nome) VALUES (:usuario, :senha, :nome)";
            $stmtInserir = $conexao->prepare($inserirUsuarioSql);
            $stmtInserir->bindParam(":usuario", $usuario, PDO::PARAM_STR);
            $stmtInserir->bindParam(":senha", $hashSenha, PDO::PARAM_STR);
            $stmtInserir->bindParam(":nome", $nome, PDO::PARAM_STR);
            $stmtInserir->execute();

            $response = ['status' => 'success', 'message' => 'Usuário cadastrado com sucesso!'];
            $response['redirect'] = 'login.html';
        }
    }
} catch (PDOException $e) {
    $response = ['status' => 'error', 'message' => 'Erro no cadastro: ' . $e->getMessage()];
}

// Enviar cabeçalhos JSON
header('Content-Type: application/json');

// Enviar resposta JSON
echo json_encode($response);
?>