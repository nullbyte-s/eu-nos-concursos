<?php
// Iniciar a sessão para obter o usuário
session_start();

if (isset($_SESSION['usuario']) && $_SESSION['usuario'] === 'admin') {
    // Conexão com o banco de dados
    $dsn = 'sqlite:db.sqlite3';

    try {
        $conexao = new PDO($dsn);
        $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Consulta para obter os dados da tabela "contato"
        $sql = "SELECT nome, email, mensagem FROM contato";
        $stmt = $conexao->query($sql);

        // Obter os resultados da consulta como array associativo
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Retornar os resultados como JSON
        header('Content-Type: application/json');
        echo json_encode($resultados);

    } catch (PDOException $e) {
        // Retornar erro como JSON
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erro ao carregar mensagens de contato: ' . $e->getMessage()]);
    }
} else {
    // Retornar erro como JSON se o usuário não estiver autenticado como admin
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Usuário não autorizado']);
}
?>