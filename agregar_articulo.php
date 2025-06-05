<?php
session_start();

// Verifica que el usuario sea administrador
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'MySQLConnector.php';

$error = '';
$mensaje = '';
$categorias = [];

try {
    $dbConnector = new MysqlConnector();
    $dbConnector->Connect();

    // Obtener categorías
    $queryCategorias = "SELECT idCategoria, descripcion FROM Categorias";
    $resultCategorias = $dbConnector->connection->query($queryCategorias);

    if (!$resultCategorias) {
        throw new Exception("Error al obtener categorías: " . $dbConnector->connection->error);
    }

    while ($row = $resultCategorias->fetch_assoc()) {
        $categorias[] = $row;
    }

    // Procesar formulario
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $descripcion = isset($_POST['descripcion']) ? $dbConnector->connection->real_escape_string($_POST['descripcion']) : '';
        $precio = isset($_POST['precio']) ? floatval($_POST['precio']) : 0;
        $caracteristicas = isset($_POST['caracteristicas']) ? $dbConnector->connection->real_escape_string($_POST['caracteristicas']) : '';
        $idCategoria = isset($_POST['idCategoria']) ? intval($_POST['idCategoria']) : 0;

        if (empty($descripcion) || $precio <= 0 || $idCategoria <= 0) {
            throw new Exception("Por favor complete todos los campos requeridos correctamente.");
        }

        // Imagen (opcional)
        $imagen = null;
        if (isset($_FILES['imagen']['tmp_name']) && is_uploaded_file($_FILES['imagen']['tmp_name'])) {
            if ($_FILES['imagen']['size'] > 1048576) {
                throw new Exception("La imagen es demasiado grande. Tamaño máximo: 1MB");
            }
            $imagen = file_get_contents($_FILES['imagen']['tmp_name']);
        }

        // Insertar artículo
        $queryInsert = "INSERT INTO Articulos (descripcion, precio, caracteristicas, imagen, idCategoria)
                        VALUES (?, ?, ?, ?, ?)";
        $stmt = $dbConnector->connection->prepare($queryInsert);

        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $dbConnector->connection->error);
        }

        $stmt->bind_param("sdssi", $descripcion, $precio, $caracteristicas, $imagen, $idCategoria);

        if ($imagen !== null) {
            $stmt->send_long_data(3, $imagen);
        }

        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Artículo agregado exitosamente.";
            header("Location: gestionar_articulos.php");
            exit();
        } else {
            throw new Exception("Error al agregar el artículo: " . $stmt->error);
        }
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Agregar Artículo</title>
    <link rel="stylesheet" href="Estilo.css">
</head>
<body>
    <div id="wrap">
        <div id="header">
            <a href="gestionar_articulos.php" class="btn-volver">← Volver a Artículos</a>
            <h1>Agregar Nuevo Artículo</h1>
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <label>Descripción:*</label><br>
            <input type="text" name="descripcion" maxlength="100" required><br>

            <label>Precio:*</label><br>
            <input type="number" name="precio" step="0.01" min="0" required><br>

            <label>Características:</label><br>
            <textarea name="caracteristicas" maxlength="255"></textarea><br>

            <label>Imagen (opcional, máximo 1MB):</label><br>
            <input type="file" name="imagen" accept="image/jpeg,image/png,image/gif"><br>

            <label>Categoría:*</label><br>
            <select name="idCategoria" required>
                <option value="">Seleccione una categoría</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?= $categoria['idCategoria'] ?>"><?= htmlspecialchars($categoria['descripcion']) ?></option>
                <?php endforeach; ?>
            </select><br><br>

            <button type="submit">Agregar</button>
            <a href="gestionar_articulos.php" class="button">Cancelar</a>
        </form>

        <div id="footer">
            <p>Designed by Xitlalic Guadalupe Flores Salcedo.</p>
        </div>
    </div>
</body>
</html>
<?php
if (isset($dbConnector)) {
    $dbConnector->CloseConnection();
}
?>
