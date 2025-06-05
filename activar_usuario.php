<?php
session_start();

if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'MySQLConnector.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: gestionar_usuarios.php?error=ID inválido");
    exit();
}

$idUsuario = intval($_GET['id']);

try {
    $db = new MysqlConnector();
    $db->Connect();

    // Activar el usuario
    $stmt = $db->connection->prepare("UPDATE Usuarios SET activo = 1 WHERE idUsuario = ?");
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $stmt->close();
        $db->CloseConnection();
        header("Location: gestionar_usuarios.php?exito=Usuario activado correctamente");
        exit();
    } else {
        throw new Exception("No se pudo activar el usuario.");
    }
} catch (Exception $e) {
    header("Location: gestionar_usuarios.php?error=" . urlencode($e->getMessage()));
    exit();
}
