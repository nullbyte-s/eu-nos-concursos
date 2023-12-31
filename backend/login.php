<?php
// Conexão com o banco de dados
$dsn = 'sqlite:db.sqlite3';

try {
    $conexao = new PDO($dsn);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obter os dados do corpo da requisição POST
    $data = json_decode(file_get_contents("php://input"));

    $usuario = isset($data->usuario) ? $data->usuario : null;
    $senha = isset($data->senha) ? $data->senha : null;

    // Recuperar o hash da senha do banco de dados para o usuário informado
    $sql = "SELECT senha FROM usuarios WHERE usuario = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bindValue(1, $usuario, PDO::PARAM_STR);
    $stmt->execute();

    // Verificar se o usuário existe e a senha está correta
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row !== false) {
        $hashSenhaArmazenada = $row['senha'];
    
        // Verificar a senha usando password_verify()
        if (password_verify($senha, $hashSenhaArmazenada)) {
            // Geração do token
            $token = password_hash(uniqid(rand(), true), PASSWORD_DEFAULT);

            // Armazenamento do token no banco de dados
            $sql = "UPDATE usuarios SET token = :token WHERE usuario = :usuario";
            $stmt = $conexao->prepare($sql);
            $stmt->bindParam(":token", $token, PDO::PARAM_STR);
            $stmt->bindParam(":usuario", $usuario, PDO::PARAM_STR);
            $stmt->execute();

            // Senha válida, logar o usuário
            $response = ['status' => 'success', 'message' => 'Login bem-sucedido!', 'token' => $token];
            $response['redirect'] = 'authenticated.html';
            $response['dropdown'] = 'Usuário';
        } else {
            // Senha inválida
            $response = ['status' => 'error', 'message' => 'Usuário ou senha incorretos.'];
        }
    } else {
        // Usuário não encontrado
        $response = ['status' => 'error', 'message' => 'Usuário ou senha incorretos.'];
    }
} catch (PDOException $e) {
    $response = ['status' => 'error', 'message' => 'Erro na autenticação: ' . $e->getMessage()];
}

function verificarToken()
{
    // Obter dados do corpo da requisição JSON
    $data = json_decode(file_get_contents("php://input"));

    // Verificar se o token está presente nos dados
    if (isset($data->token)) {
        $token = $data->token;

        // Consultar o banco de dados para verificar se o token existe
        $sql = "SELECT token, usuario FROM usuarios WHERE token = :token";
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":token", $token, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Token válido, retorna informações do usuário associado ao token
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $usuario = $row['usuario'];
            echo json_encode(['status' => 'success', 'usuario' => $usuario]);
            exit;
        }
    }

    header('Content-Type: application/json');
    // Se não houver token ou o token não for válido, retorna uma resposta indicando falha
    echo json_encode(['status' => 'error', 'message' => 'Token inválido ou inexistente']);
    exit;
}

if (!empty($data->usuario) && !empty($data->senha)) {
    header('Content-Type: application/json');
    echo json_encode($response);
} elseif (!empty($data->token)) {
    if (isset($data->token)) {
        $token = $data->token;

        $sql = "SELECT token, usuario FROM usuarios WHERE token = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(1, $token, PDO::PARAM_STR);
        $stmt->execute();        

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && $result['token'] === $token) {
            echo json_encode(['status' => 'success', 'usuario' => $result['usuario']]);
            exit;
        }
    }

    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Token inválido ou inexistente']);
    exit;
}
?>