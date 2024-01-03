<?php
// Recuperar os dados enviados pelo método POST
$data = json_decode(file_get_contents("php://input"), true);

// Verificar a ação (add ou remove)
$action = $data['action'];

// Obter o token do cabeçalho
$token = $_SERVER['HTTP_AUTHORIZATION'];

// Conexão com o banco de dados
$dsn = 'sqlite:db.sqlite3';

try {
    $conexao = new PDO($dsn);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $email = $data['email'];

    // Verificar se o token está presente nos dados
    if (isset($data->token)) {
        $token = $data->token;

        // Consultar o banco de dados para verificar se o token existe
        $sql = "SELECT token, usuario FROM usuarios WHERE token = :token";
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":token", $token, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $usuario = $row['usuario'];
        }
    }
    
    // Verificar a ação e executar as operações correspondentes
    if ($action === 'add') {
        $sql = "UPDATE usuarios SET notificacao = 1 WHERE token = ? AND email = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(1, $token, PDO::PARAM_STR);
        $stmt->bindValue(2, $email, PDO::PARAM_STR);
        $stmt->execute();
        echo json_encode(['status' => 'success', 'message' => 'E-mail adicionado com sucesso']);
    } elseif ($action === 'remove') {
        $sql = "UPDATE usuarios SET notificacao = 0 WHERE token = ? AND email = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(1, $token, PDO::PARAM_STR);
        $stmt->bindValue(2, NULL, PDO::PARAM_STR);
        $stmt->execute();
        echo json_encode(['status' => 'success', 'message' => 'E-mail removido com sucesso']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Ação inválida']);
    }
} catch (PDOException $e) {
    $response = ['status' => 'error', 'message' => 'Erro ao realizar a operação: ' . $e->getMessage()];
    echo json_encode($response);
}
?>