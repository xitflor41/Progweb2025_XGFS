<?php
session_start();
require_once 'MySQLConnector.php';

// Verificar si el usuario es administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Verificar que se haya enviado el ID del artículo por POST
if (!isset($_POST['idArticulo']) || empty($_POST['idArticulo'])) {
    die("ID de artículo no proporcionado.");
}

$idArticulo = intval($_POST['idArticulo']);

try {
    $db = new MySQLConnector();
    $db->Connect();

    // Marcar el artículo como inactivo
    $query = "UPDATE Articulos SET activo = 0 WHERE idArticulo = ?";
    $stmt = $db->PrepareStatement($query);

    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $db->connection->error);
    }

    $stmt->bind_param("i", $idArticulo);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: gestionar_articulos.php?msg=Articulo+deshabilitado+correctamente");
    } else {
        echo "No se pudo deshabilitar el artículo o ya está deshabilitado.";
    }

    $stmt->close();
    $db->CloseConnection();

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
