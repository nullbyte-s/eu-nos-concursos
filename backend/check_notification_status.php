<?php
// Recuperar os dados enviados pelo método POST
$data = json_decode(file_get_contents("php://input"), true);

// Verificar a ação (check)
$action = isset($data['action']) ? $data['action'] : null;

// Obter o token do cabeçalho
$token = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : null;

// Conexão com o banco de dados
$dsn = 'sqlite:db.sqlite3';

try {
    $conexao = new PDO($dsn);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar se o token está presente nos dados
    if (isset($data['token'])) {
        $token = $data['token'];

        // Consultar o banco de dados para verificar se o token existe
        $sql = "SELECT token, usuario FROM usuarios WHERE token = :token";
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":token", $token, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $usuario = $row['usuario'];
        }else{
            header('Content-Type: application/json');
            // Se não houver token ou o token não for válido, retorna uma resposta indicando falha
            echo json_encode(['status' => 'error', 'message' => 'Token inválido ou inexistente']);
            exit;
        }
    }

    // Verificar a ação e retornar o status da notificação
    if ($action === 'check') {
        $stmt = $conexao->prepare('SELECT notificacao, email FROM usuarios WHERE token = ?');
        $stmt->bindValue(1, $token, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['notificacao' => $result['notificacao'], 'email' => $result['email']]);
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