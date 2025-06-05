<?php
session_start();

// Solo administradores pueden acceder
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'MySQLConnector.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de categoría no válido.");
}

$idCategoria = (int)$_GET['id'];

try {
    $db = new MysqlConnector();
    $db->Connect();

    // Actualizar campo activo a 0 (deshabilitado)
    $query = "UPDATE Categorias SET activo = 0 WHERE idCategoria = ?";
    $stmt = $db->connection->prepare($query);
    $stmt->bind_param("i", $idCategoria);

    if (!$stmt->execute()) {
        throw new Exception("Error al deshabilitar categoría: " . $stmt->error);
    }

    $stmt->close();
    $db->CloseConnection();

    // Redirigir de nuevo a la gestión
    header("Location: gestionar_categorias.php");
    exit();

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
