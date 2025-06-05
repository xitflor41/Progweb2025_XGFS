<?php
session_start();

// Solo administradores pueden acceder
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'MySQLConnector.php';

$error = '';
$success = '';
$tienda = null;

// Validar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $error = 'ID de tienda no válido.';
} else {
    $idTienda = (int) $_GET['id'];

    try {
        $db = new MysqlConnector();
        $db->Connect();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar y actualizar datos
            $nombre = trim($_POST['nombre_sucursal']);
            $ciudad = trim($_POST['ciudad']);
            $direccion = trim($_POST['direccion']);
            $codigo_postal = trim($_POST['codigo_postal']);
            $horario = trim($_POST['horario']);

            $query = "UPDATE Tiendas SET nombre_sucursal = ?, ciudad = ?, direccion = ?, codigo_postal = ?, horario = ? WHERE idTienda = ?";
            $stmt = $db->connection->prepare($query);
            $stmt->bind_param("sssssi", $nombre, $ciudad, $direccion, $codigo_postal, $horario, $idTienda);
            if ($stmt->execute()) {
                $success = "Tienda actualizada correctamente.";
            } else {
                $error = "Error al actualizar: " . $stmt->error;
            }
        }

        // Cargar datos de la tienda
        $query = "SELECT * FROM Tiendas WHERE idTienda = ?";
        $stmt = $db->connection->prepare($query);
        $stmt->bind_param("i", $idTienda);
        $stmt->execute();
        $result = $stmt->get_result();
        $tienda = $result->fetch_assoc();

        if (!$tienda) {
            $error = "La tienda no existe.";
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Editar Tienda</title>
    <link rel="stylesheet" href="Estilo.css">
</head>
<body>
    <div id="wrap">
        <div id="header">
            <a href="gestionar_tiendas.php" class="btn-volver">← Volver</a>
            <h1>Editar Tienda</h1>
        </div>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($tienda): ?>
            <?php if ($success): ?>
                <div class="success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="post">
                <label>Nombre de la Sucursal:</label>
                <input type="text" name="nombre_sucursal" value="<?= htmlspecialchars($tienda['nombre_sucursal']) ?>" required>

                <label>Ciudad:</label>
                <input type="text" name="ciudad" value="<?= htmlspecialchars($tienda['ciudad']) ?>" required>

                <label>Dirección:</label>
                <input type="text" name="direccion" value="<?= htmlspecialchars($tienda['direccion']) ?>" required>

                <label>Código Postal:</label>
                <input type="text" name="codigo_postal" value="<?= htmlspecialchars($tienda['codigo_postal']) ?>" required>

                <label>Horario:</label>
                <input type="text" name="horario" value="<?= htmlspecialchars($tienda['horario']) ?>" required>

                <button type="submit">Guardar Cambios</button>
            </form>
        <?php endif; ?>

        <div id="footer">
            <p>Designed by Xitlalic Guadalupe Flores Salcedo.</p>
        </div>
    </div>
</body>
</html>
<?php
if (isset($db)) {
    $db->CloseConnection();
}
?>
