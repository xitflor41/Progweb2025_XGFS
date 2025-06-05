<?php
session_start();

// Solo administradores pueden acceder
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'MySQLConnector.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: gestionar_categorias.php?ver=inactivos");
    exit();
}

$idCategoria = intval($_GET['id']);

try {
    $dbConnector = new MysqlConnector();
    $dbConnector->Connect();

    // Activar la categoría
    $stmt = $dbConnector->connection->prepare("UPDATE Categorias SET activo = 1 WHERE idCategoria = ?");
    $stmt->bind_param("i", $idCategoria);

    if (!$stmt->execute()) {
        throw new Exception("Error al activar la categoría: " . $stmt->error);
    }

    $stmt->close();
    $dbConnector->CloseConnection();

    // Redirigir de vuelta a la gestión de categorías
    header("Location: gestionar_categorias.php?ver=inactivos");
    exit();

} catch (Exception $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
}
?>
