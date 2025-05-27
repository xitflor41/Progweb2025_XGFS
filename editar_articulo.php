<?php
session_start();

// Verificación de autenticación y rol
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'MySQLConnector.php';

// Inicializar variables
$error = '';
$mensaje = '';
$articulo = [];
$categorias = [];

try {
    $dbConnector = new MysqlConnector();
    $dbConnector->Connect();

    // Validar ID del artículo
    $idArticulo = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    if ($idArticulo <= 0) {
        throw new Exception("ID de artículo no válido o no especificado.");
    }

    // Obtener datos del artículo (compatible con MySQL 5)
    $query = "SELECT idArticulo, idCategoria, descripcion, caracteristicas, precio, imagen FROM Articulos WHERE idArticulo = ?";
    $stmt = $dbConnector->connection->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $dbConnector->connection->error);
    }
    
    $stmt->bind_param("i", $idArticulo);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }
    
    $stmt->bind_result($id, $cat, $desc, $caract, $precio, $img);
    
    if (!$stmt->fetch()) {
        throw new Exception("No se encontró el artículo con ID: $idArticulo");
    }
    
    // Almacenar datos del artículo
    $articulo = [
        'idArticulo' => $id,
        'idCategoria' => $cat,
        'descripcion' => $desc,
        'caracteristicas' => $caract,
        'precio' => $precio,
        'imagen' => $img
    ];
    
    $stmt->close();

    // Obtener categorías (compatible con MySQL 5)
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

        // Validar datos requeridos
        if (empty($descripcion) || $precio <= 0 || $idCategoria <= 0) {
            throw new Exception("Por favor complete todos los campos requeridos correctamente.");
        }

        // Manejo de la imagen
        $imagen = $articulo['imagen'];
        if (isset($_FILES['imagen']['tmp_name']) && is_uploaded_file($_FILES['imagen']['tmp_name'])) {
            // Verificar tamaño de la imagen (máximo 1MB)
            if ($_FILES['imagen']['size'] > 1048576) {
                throw new Exception("La imagen es demasiado grande. Tamaño máximo permitido: 1MB");
            }
            
            $imagen = file_get_contents($_FILES['imagen']['tmp_name']);
        }

        // Actualizar artículo (compatible con MySQL 5)
        $queryUpdate = "UPDATE Articulos SET 
                        descripcion = ?, 
                        precio = ?, 
                        caracteristicas = ?, 
                        imagen = ?, 
                        idCategoria = ? 
                        WHERE idArticulo = ?";
        
        $stmt = $dbConnector->connection->prepare($queryUpdate);
        
        if (!$stmt) {
            throw new Exception("Error al preparar la actualización: " . $dbConnector->connection->error);
        }
        
        $null = null;
        $stmt->bind_param("sdssii", $descripcion, $precio, $caracteristicas, $null, $idCategoria, $idArticulo);
        $stmt->send_long_data(3, $imagen); // Para manejar el campo BLOB
        
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Artículo actualizado exitosamente.";
            header("Location: gestionar_articulos.php");
            exit();
        } else {
            throw new Exception("Error al actualizar el artículo: " . $stmt->error);
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
    <title>Editar Artículo</title>
    <link rel="stylesheet" href="Estilo.css">
    <style>
        .error { color: red; }
        .success { color: green; }
        .image-preview { max-width: 200px; max-height: 200px; }
    </style>
</head>
<body>
    <h1>Editar Artículo</h1>
    
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if (!empty($mensaje)): ?>
        <div class="success"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="idArticulo" value="<?php echo $articulo['idArticulo']; ?>">
        
        <label>Descripción:*</label><br>
        <input type="text" name="descripcion" value="<?php echo htmlspecialchars($articulo['descripcion']); ?>" maxlength="100" required><br>

        <label>Precio:*</label><br>
        <input type="number" name="precio" step="0.01" min="0" value="<?php echo htmlspecialchars($articulo['precio']); ?>" required><br>

        <label>Características:</label><br>
        <textarea name="caracteristicas" maxlength="100"><?php echo htmlspecialchars($articulo['caracteristicas']); ?></textarea><br>

        <label>Imagen actual:</label><br>
        <?php if (!empty($articulo['imagen'])): ?>
            <img class="image-preview" src="data:image/jpeg;base64,<?php echo base64_encode($articulo['imagen']); ?>"><br>
        <?php else: ?>
            <p>No hay imagen</p>
        <?php endif; ?>
        
        <label>Nueva imagen (opcional, máximo 1MB):</label><br>
        <input type="file" name="imagen" accept="image/jpeg,image/png,image/gif"><br>

        <label>Categoría:*</label><br>
        <select name="idCategoria" required>
            <option value="">Seleccione una categoría</option>
            <?php foreach ($categorias as $categoria): ?>
                <option value="<?php echo $categoria['idCategoria']; ?>" <?php echo ($categoria['idCategoria'] == $articulo['idCategoria']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($categoria['descripcion']); ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <button type="submit">Actualizar</button>
        <a href="gestionar_articulos.php" class="button">Cancelar</a>
    </form>
</body>
</html>