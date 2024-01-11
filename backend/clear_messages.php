<?php
session_start();

if (isset($_SESSION['usuario']) && $_SESSION['usuario'] === 'admin') {
    
    $dsn = 'sqlite:db.sqlite3';

    try {
        $conexao = new PDO($dsn);
        $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "DELETE FROM contato";
        $conexao->exec($sql);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Mensagens apagadas com sucesso']);

    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Erro ao apagar mensagens: ' . $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Usuário não autorizado']);
}
?>
