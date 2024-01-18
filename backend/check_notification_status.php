<?php
$action = isset($_GET['action']) ? $_GET['action'] : null;
$token = null;
$headers = getallheaders();

if (isset($headers['authorization'])) {
    $authorizationHeader = $headers['authorization'];
    if (strpos($authorizationHeader, 'Bearer') === 0) {
        $token = trim(substr($authorizationHeader, 7));
    }
}

// Conexão com o banco de dados
$dsn = 'sqlite:db.sqlite3';

try {
    $conexao = new PDO($dsn);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($token !== null && $action === 'check') {
        $sqlToken = "SELECT token, usuario FROM usuarios WHERE token = '{$token}'";
        $stmt = $conexao->query($sqlToken);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $usuario = $result['usuario']; 
            $sqlNotificacao = "SELECT notificacao, email FROM usuarios WHERE usuario = '{$usuario}'";
            $stmt = $conexao->query($sqlNotificacao);
            $resultNotificacao = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($resultNotificacao) {
                echo json_encode(['notificacao' => $resultNotificacao['notificacao'], 'email' => $resultNotificacao['email']]);
            } else {
                echo json_encode(['notificacao' => $resultNotificacao]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Token inválido ou inexistente',
                'notificacao' => 0,
                'email' => ''
            ]);            
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Token inválido ou inexistente',
            'notificacao' => 0,
            'email' => ''
        ]);            
    }
} catch (PDOException $e) {
    $response = ['status' => 'error', 'message' => 'Erro ao realizar a operação: ' . $e->getMessage()];
    if (isset($response['status']) && isset($response['message'])) {
        echo json_encode($response);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro inesperado']);
    }
}
?>