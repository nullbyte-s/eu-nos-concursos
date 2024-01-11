<?php
session_start();

// Retornar as informações da sessão como JSON
header('Content-Type: application/json');

if (isset($_SESSION['usuario'])) {
    echo json_encode(['usuario' => $_SESSION['usuario']]);
} else {
    echo json_encode(['usuario' => null]);
}
?>