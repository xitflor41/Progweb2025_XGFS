<?php
session_start();
require_once 'MySQLConnector.php';

if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: gestionar_tiendas.php?error=ID inválido");
    exit();
}

$idTienda = intval($_GET['id']);

try {
    $db = new MysqlConnector();
    $db->Connect();

    $stmt = $db->connection->prepare("UPDATE Tiendas SET activo = 1 WHERE idTienda = ?");
    $stmt->bind_param("i", $idTienda);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $stmt->close();
        $db->CloseConnection();
        header("Location: gestionar_tiendas.php?exito=Tienda activada correctamente");
        exit();
    } else {
        throw new Exception("No se pudo activar la tienda.");
    }
} catch (Exception $e) {
    header("Location: gestionar_tiendas.php?error=" . urlencode($e->getMessage()));
    exit();
}
