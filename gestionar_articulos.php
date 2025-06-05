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
$idTienda = $_SESSION['idTienda'] ?? 1; // Usa el ID de tienda de la sesión o el 1 por defecto

$query = "SELECT a.*, c.descripcion AS categoria, e.cantidad
        FROM Articulos a
        LEFT JOIN Categorias c ON a.idCategoria = c.idCategoria
        LEFT JOIN Existencia e ON a.idArticulo = e.idArticulo AND e.idTienda = $idTienda
        ORDER BY a.idArticulo
";

$result = $dbConnector->ExecuteQuery($query);




?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="Estilo.css">
    <title>Gestión de Artículos</title>
    <meta charset="UTF-8">
    <style>
        
        .btn-editar{
            padding: 10px 15px;
            margin: 5px;
            background-color:rgb(207, 207, 207);
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn-agregar{
            display: inline-block;
            padding: 10px 15px;
            margin: 5px;
            background-color: rgb(207, 207, 207);
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div id="wrap">
        <div id="header">
            <a href="admin_panel.php" class="logout-btn">← Volver al Panel</a>
            <h1>Administrar Artículos</h1>
            <?php if ($mensaje): ?>
                <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <a href="agregar_articulo.php" class="btn-agregar">Agregar Nuevo Artículo</a>
        </div>

        <div id="catalogo">
           <?php while ($articulo = mysqli_fetch_assoc($result)): ?>
                <div class="articulo">
                    <h2><?= htmlspecialchars($articulo['descripcion']) ?></h2>
                    <p>Categoría: <?= htmlspecialchars($articulo['categoria']) ?></p>
                    <p>Precio: $<?= number_format($articulo['precio'], 2) ?></p>
                    <p><strong>Stock actual:</strong> <?= (int)$articulo['cantidad'] ?> unidades</p>

                    <?php if (!empty($articulo['imagen'])): ?>
                        <img class="articulo-img" src="data:image/jpeg;base64,<?= base64_encode($articulo['imagen']) ?>" alt="<?= htmlspecialchars($articulo['descripcion']) ?>">
                    <?php else: ?>
                        <div class="articulo-img"></div>
                    <?php endif; ?>

                    <p><?= htmlspecialchars($articulo['caracteristicas']) ?></p>

                    <!-- Acciones -->
                    <a href="editar_articulo.php?idArticulo=<?= $articulo['idArticulo'] ?>" class="btn-editar">Editar</a>
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
