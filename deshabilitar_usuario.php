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

// Prevenir que el administrador se deshabilite a sí mismo
if ($_SESSION['idUsuario'] == $idUsuario) {
    header("Location: gestionar_usuarios.php?error=No puedes deshabilitar tu propio usuario");
    exit();
}

try {
    $db = new MysqlConnector();
    $db->Connect();

    // Deshabilitar el usuario (no eliminarlo)
    $stmt = $db->connection->prepare("UPDATE Usuarios SET activo = 0 WHERE idUsuario = ?");
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $stmt->close();
        $db->CloseConnection();
        header("Location: gestionar_usuarios.php?exito=Usuario deshabilitado correctamente");
        exit();
    } else {
        throw new Exception("No se pudo deshabilitar el usuario.");
    }
} catch (Exception $e) {
    header("Location: gestionar_usuarios.php?error=" . urlencode($e->getMessage()));
    exit();
}
