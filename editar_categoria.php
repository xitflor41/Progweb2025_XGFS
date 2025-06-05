<?php
session_start();

// Solo administradores pueden acceder
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'MySQLConnector.php';

$error = '';
$exito = '';
$idCategoria = $_GET['id'] ?? null;
$descripcionActual = '';

if (!$idCategoria || !is_numeric($idCategoria)) {
    $error = "ID de categoría inválido.";
} else {
    try {
        $db = new MysqlConnector();
        $db->Connect();

        // Obtener datos actuales
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $query = "SELECT descripcion FROM Categorias WHERE idCategoria = ?";
            $stmt = $db->connection->prepare($query);
            $stmt->bind_param("i", $idCategoria);
            $stmt->execute();
            $stmt->bind_result($descripcionActual);

            if (!$stmt->fetch()) {
                $error = "Categoría no encontrada.";
            }

            $stmt->close();
        }

        // Actualizar si se envió el formulario
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $nuevaDescripcion = trim($_POST['descripcion']);

            if (empty($nuevaDescripcion)) {
                $error = "La descripción no puede estar vacía.";
            } else {
                $query = "UPDATE Categorias SET descripcion = ? WHERE idCategoria = ?";
                $stmt = $db->connection->prepare($query);
                $stmt->bind_param("si", $nuevaDescripcion, $idCategoria);

                if ($stmt->execute()) {
                    $exito = "Categoría actualizada correctamente.";
                    $descripcionActual = $nuevaDescripcion;
                } else {
                    $error = "Error al actualizar: " . $stmt->error;
                }

                $stmt->close();
            }
        }

        $db->CloseConnection();
    } catch (Exception $e) {
        $error = "Error de sistema: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Categoría</title>
    <link rel="stylesheet" href="Estilo.css">
</head>
<body>
    <div id="header">
        <a href="gestionar_categorias.php" class="btn-volver">← Volver a Categorías</a>
        <h1>Editar Categoría</h1>
    </div>

    <div class="container">
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($exito): ?>
            <div class="success"><?= htmlspecialchars($exito) ?></div>
        <?php endif; ?>

        <?php if (!$error): ?>
        <form method="POST" action="editar_categoria.php?id=<?= htmlspecialchars($idCategoria) ?>">
            <div class="form-group">
                <label for="descripcion">Nueva Descripción:</label>
                <input type="text" id="descripcion" name="descripcion" value="<?= htmlspecialchars($descripcionActual) ?>" required>
            </div>

            <button type="submit" class="btn">Guardar Cambios</button>
        </form>
        <?php endif; ?>
    </div>

    <div id="footer">
        <p>Designed by Xitlalic Guadalupe Flores Salcedo.</p>
    </div>
</body>
</html>
