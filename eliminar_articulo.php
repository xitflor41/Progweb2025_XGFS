<?php
session_start();

if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'MySQLConnector.php';

$dbConnector = new MysqlConnector();
$dbConnector->Connect();

$idArticulo = $_POST['id'] ?? null;

if (!$idArticulo) {
    $_SESSION['error'] = "ID de artículo no especificado.";
    header("Location: gestionar_articulos.php");
    exit();
}

$queryDelete = "DELETE FROM Articulos WHERE idArticulo = ?";
$stmt = $dbConnector->connection->prepare($queryDelete);
$stmt->bind_param("i", $idArticulo);

if ($stmt->execute()) {
    $_SESSION['mensaje'] = "Artículo eliminado exitosamente.";
} else {
    $_SESSION['error'] = "Error al eliminar el artículo.";
}

header("Location: gestionar_articulos.php");
exit();
