<?php
session_start();

// Verifica que el usuario sea administrador
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'MySQLConnector.php';
$dbConnector = new MysqlConnector();
$dbConnector->Connect();

$mensaje = $_SESSION['mensaje'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['mensaje'], $_SESSION['error']);

// Obtener artículos con categoría
$query = "SELECT a.*, c.descripcion AS categoria FROM Articulos a
          JOIN Categorias c ON a.idCategoria = c.idCategoria
          ORDER BY a.idArticulo";
$result = $dbConnector->ExecuteQuery($query);
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="Estilo.css">
    <title>Gestión de Artículos</title>
    <meta charset="UTF-8">
</head>
<body>
    <div id="wrap">
        <div id="header">
            <a href="admin_panel.php" class="btn-volver">← Volver al Panel</a>
            <h1>Administrar Artículos</h1>
            <?php if ($mensaje): ?>
                <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
        </div>

        <div id="catalogo">
            <?php while ($articulo = mysqli_fetch_assoc($result)): ?>
                <div class="articulo">
                    <h2><?= htmlspecialchars($articulo['descripcion']) ?></h2>
                    <p>Categoría: <?= htmlspecialchars($articulo['categoria']) ?></p>
                    <p>Precio: $<?= number_format($articulo['precio'], 2) ?></p>
                    <?php if (!empty($articulo['imagen'])): ?>
                        <img class="articulo-img" src="data:image/jpeg;base64,<?= base64_encode($articulo['imagen']) ?>" alt="<?= htmlspecialchars($articulo['descripcion']) ?>">
                    <?php else: ?>
                        <div class="articulo-img"></div>
                    <?php endif; ?>

                    <p><?= htmlspecialchars($articulo['caracteristicas']) ?></p>

                    <!-- Acciones -->
                    <form method="POST" action="editar_articulo.php" style="display:inline;">
                        <input type="hidden" name="idArticulo" value="<?= $articulo['idArticulo'] ?>">
                        <button type="submit" class="btn-editar">Editar</button>
                    </form>

                    <form method="POST" action="eliminar_articulo.php" onsubmit="return confirm('¿Estás seguro de eliminar este artículo?');" style="display:inline;">
                        <input type="hidden" name="idArticulo" value="<?= $articulo['idArticulo'] ?>">
                        <button type="submit" class="btn-eliminar">Eliminar</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>

        <div id="footer">
            <p>Designed by Xitlalic Guadalupe Flores Salcedo.</p>
        </div>
    </div>
</body>
</html>
<?php
$dbConnector->CloseConnection();
?>
