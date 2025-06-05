<?php
session_start();


if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}


if (isset($_POST['idTienda'])) {
    $_SESSION['idTienda'] = intval($_POST['idTienda']);
}


require_once 'MySQLConnector.php';

$error = '';
$exito = '';
$articulo = null;
$categorias = [];

try {
    $db = new MysqlConnector();
    $db->Connect();

    // Obtener idArticulo desde POST o GET, dependiendo del método
    $idArticulo = null;
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $idArticulo = $_POST['idArticulo'] ?? null;
    } else {
        $idArticulo = $_GET['idArticulo'] ?? null;
    }

    if (!$idArticulo || !is_numeric($idArticulo)) {
        throw new Exception("ID de artículo inválido.");
    }

    $idArticulo = intval($idArticulo);

    // Obtener datos del artículo
    $stmt = $db->connection->prepare("SELECT a.*, e.cantidad 
    FROM Articulos a
    JOIN Existencia e ON a.idArticulo = e.idArticulo
    WHERE a.idArticulo = ?");
    $stmt->bind_param("i", $idArticulo);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("Artículo no encontrado.");
    }
    $articulo = $result->fetch_assoc();
    $stmt->close();

    // Obtener categorías
    $resCategorias = $db->connection->query("SELECT idCategoria, descripcion FROM Categorias");
    while ($row = $resCategorias->fetch_assoc()) {
        $categorias[] = $row;
    }





    
    // Procesar formulario
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $cantidadRestock = isset($_POST['cantidad_restock']) ? intval($_POST['cantidad_restock']) : 0;

// Actualizar artículo
$stmt = $db->connection->prepare("
    UPDATE Articulos 
    SET descripcion = ?, caracteristicas = ?, precio = ?, idCategoria = ?, activo = ?
    WHERE idArticulo = ?
");
$stmt->bind_param("ssdiii", $descripcion, $caracteristicas, $precio, $idCategoria, $activo, $idArticulo);

if ($stmt->execute()) {
    $stmt->close();

    // Si hay restock, actualiza la tabla Existencia
    if ($cantidadRestock > 0) {
        $stmtExistencia = $db->connection->prepare("
            UPDATE Existencia
            SET cantidad = cantidad + ?
            WHERE idArticulo = ? AND idTienda = ?
        ");
        $stmtExistencia->bind_param("iii", $cantidadRestock, $idArticulo, $_SESSION['idTienda']); // 👈 usamos el idTienda del admin
        $stmtExistencia->execute();
        $stmtExistencia->close();
    }

    header("Location: editar_articulo.php?idArticulo=$idArticulo&exito=1");
    exit();
}





        $descripcion = trim($_POST['descripcion']);
        $caracteristicas = trim($_POST['caracteristicas']);
        $precio = floatval($_POST['precio']);
        $idCategoria = !empty($_POST['idCategoria']) ? intval($_POST['idCategoria']) : null;
        $activo = isset($_POST['activo']) ? 1 : 0;

        if (empty($descripcion) || empty($caracteristicas) || $precio <= 0) {
            $error = "Todos los campos obligatorios deben llenarse correctamente.";
        } else {
            $stmt = $db->connection->prepare("
                UPDATE Articulos 
                SET descripcion = ?, caracteristicas = ?, precio = ?, idCategoria = ?, activo = ?
                WHERE idArticulo = ?
            ");
            $stmt->bind_param("ssdiii", $descripcion, $caracteristicas, $precio, $idCategoria, $activo, $idArticulo);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    header("Location: editar_articulo.php?idArticulo=$idArticulo&exito=1");
                    exit();
                } else {
                    $error = "No se realizaron cambios.";
                }
            } else {
                $error = "Error al ejecutar la consulta: " . $stmt->error;
            }

            $stmt->close();
        }
    }

    $db->CloseConnection();
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}

if (isset($_GET['exito'])) {
    $exito = "Artículo actualizado correctamente.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Editar Artículo</title>
    <link rel="stylesheet" href="Estilo.css">
</head>
<body>
<div id="wrap">
    <div id="header">
        <a href="gestionar_articulos.php" class="btn-volver">← Volver</a>
        <h1>Editar Artículo</h1>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($exito): ?>
            <div class="exito"><?= htmlspecialchars($exito) ?></div>
        <?php endif; ?>
    </div>

    <?php if ($articulo): ?>
        <form method="POST">
            <label>Descripción *</label>
            <input type="text" name="descripcion" value="<?= htmlspecialchars($articulo['descripcion']) ?>" required>

            <label>Características *</label>
            <input type="text" name="caracteristicas" value="<?= htmlspecialchars($articulo['caracteristicas']) ?>" required>

            <label>Precio *</label>
            <input type="number" name="precio" step="0.01" value="<?= htmlspecialchars($articulo['precio']) ?>" required>

            <label>Categoría</label>
            <select name="idCategoria">
                <option value="">-- Ninguna --</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['idCategoria'] ?>" <?= ($articulo['idCategoria'] == $cat['idCategoria']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['descripcion']) ?>
                    </option>
                <?php endforeach; ?>
            </select>


            <!-- Mostrar stock actual -->
            <p><strong>Stock actual:</strong> <?= htmlspecialchars($articulo['cantidad']) ?> unidades</p>

            <!-- Campo para restock -->
            <label>Agregar al stock</label>
            <input type="number" name="cantidad_restock" min="0" placeholder="Cantidad a agregar">


            <label>
                <input type="hidden" name="idArticulo" value="<?= $articulo['idArticulo'] ?>">
                <input type="checkbox" name="activo" <?= $articulo['activo'] ? 'checked' : '' ?>>
                Artículo activo
            </label>

            <button type="submit">Guardar Cambios</button>
        </form>
    <?php endif; ?>

    <div id="footer">
        <p>Diseñado por Xitlalic Guadalupe Flores Salcedo.</p>
    </div>
</div>
</body>
</html>
