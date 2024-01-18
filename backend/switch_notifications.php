<?php
// Recuperar os dados enviados pelo método POST
$data = json_decode(file_get_contents("php://input"), true);

// Verificar a ação (add ou remove)
$action = isset($data['action']) ? $data['action'] : null;

// // Obter o token do cabeçalho
// $token = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : null;
$headers = getallheaders();
$token = $headers['authorization'];

// Recuperar o email do payload JSON
$email = isset($data['email']) ? $data['email'] : null;

// Conexão com o banco de dados
$dsn = 'sqlite:db.sqlite3';

try {
    $conexao = new PDO($dsn);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   
    // Verificar a ação e executar as operações correspondentes
    if ($action === 'add') {
        // Verificar se o email foi fornecido
        if (!$email) {
            echo json_encode(['status' => 'error', 'message' => 'Email não fornecido']);
            exit;
        }

        // Definir notificacao
        $sqlNotificacao = "UPDATE usuarios SET notificacao = 1 WHERE token = ?";
        $stmtNotificacao = $conexao->prepare($sqlNotificacao);
        $stmtNotificacao->bindValue(1, $token, PDO::PARAM_STR);
        $stmtNotificacao->execute();

        // Definir email
        $sqlEmail = "UPDATE usuarios SET email = ? WHERE token = ?";
        $stmtEmail = $conexao->prepare($sqlEmail);
        $stmtEmail->bindValue(1, $email, PDO::PARAM_STR);
        $stmtEmail->bindValue(2, $token, PDO::PARAM_STR);
        $stmtEmail->execute();

        echo json_encode(['status' => 'success', 'message' => 'E-mail adicionado com sucesso']);
    } elseif ($action === 'remove') {
        // Definir notificacao
        $sqlNotificacao = "UPDATE usuarios SET notificacao = 0 WHERE token = ?";
        $stmtNotificacao = $conexao->prepare($sqlNotificacao);
        $stmtNotificacao->bindValue(1, $token, PDO::PARAM_STR);
        $stmtNotificacao->execute();
        
        // Limpar email
        $sqlEmail = "UPDATE usuarios SET email = NULL WHERE token = ?";
        $stmtEmail = $conexao->prepare($sqlEmail);
        $stmtEmail->bindValue(1, $token, PDO::PARAM_STR);
        $stmtEmail->execute();

        echo json_encode(['status' => 'success', 'message' => 'E-mail removido com sucesso']);    
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Ação inválida']);
    }
} catch (PDOException $e) {
    $response = ['status' => 'error', 'message' => 'Erro ao realizar a operação: ' . $e->getMessage()];
    echo json_encode($response);
}
?>