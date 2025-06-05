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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $descripcion = trim($_POST['descripcion']);

    if (empty($descripcion)) {
        $error = "La descripción no puede estar vacía.";
    } else {
        try {
            $db = new MysqlConnector();
            $db->Connect();

            $query = "INSERT INTO Categorias (descripcion, activo) VALUES (?, 1)";
            $stmt = $db->connection->prepare($query);
            $stmt->bind_param("s", $descripcion);

            if ($stmt->execute()) {
                $exito = "Categoría agregada correctamente.";
            } else {
                $error = "Error al agregar categoría: " . $stmt->error;
            }

            $stmt->close();
            $db->CloseConnection();
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Categoría</title>
    <link rel="stylesheet" href="Estilo.css">
</head>
<body>
    <div id="header">
        <a href="gestionar_categorias.php" class="btn-volver">← Volver a Categorías</a>
        <h1>Agregar Nueva Categoría</h1>
    </div>

    <div class="container">
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($exito): ?>
            <div class="success"><?= htmlspecialchars($exito) ?></div>
        <?php endif; ?>

        <form method="POST" action="agregar_categoria.php">
            <div class="form-group">
                <label for="descripcion">Descripción de la categoría:</label>
                <input type="text" id="descripcion" name="descripcion" required>
            </div>

            <button type="submit" class="btn">Guardar</button>
        </form>
    </div>

    <div id="footer">
        <p>Designed by Xitlalic Guadalupe Flores Salcedo.</p>
    </div>
</body>
</html>
