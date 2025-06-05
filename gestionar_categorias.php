<?php
session_start();

// Solo administradores pueden acceder
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'MySQLConnector.php';

$error = '';
$categorias = [];
$ver = $_GET['ver'] ?? 'activas';

try {
    $dbConnector = new MysqlConnector();
    $dbConnector->Connect();

    // Filtrar según el parámetro ?ver=
    if ($ver === 'inactivos') {
        $query = "SELECT idCategoria, descripcion, activo FROM Categorias WHERE activo = 0 ORDER BY descripcion ASC";
    } elseif ($ver === 'todo') {
        $query = "SELECT idCategoria, descripcion, activo FROM Categorias ORDER BY descripcion ASC";
    } else {
        $query = "SELECT idCategoria, descripcion, activo FROM Categorias WHERE activo = 1 ORDER BY descripcion ASC";
    }

    $result = $dbConnector->connection->query($query);

    if (!$result) {
        throw new Exception("Error al obtener categorías: " . $dbConnector->connection->error);
    }

    while ($row = $result->fetch_assoc()) {
        $categorias[] = $row;
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestión de Categorías</title>
    <link rel="stylesheet" href="Estilo.css">
</head>
<body>
<div id="wrap">
    <div id="header">
        <a href="admin_panel.php" class="btn-volver">← Volver al Panel</a>
        <h1>Gestión de Categorías</h1>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
    </div>

    <div style="margin-bottom: 1rem;">
        <a href="gestionar_categorias.php" class="button">✅ Ver Activas</a>
        <a href="gestionar_categorias.php?ver=inactivos" class="button">🚫 Ver Inactivas</a>
        <a href="gestionar_categorias.php?ver=todo" class="button">📋 Ver Todas</a>
    </div>

    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Descripción</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($categorias)): ?>
            <tr><td colspan="4">No hay categorías registradas.</td></tr>
        <?php else: ?>
            <?php foreach ($categorias as $c): ?>
                <tr>
                    <td><?= $c['idCategoria'] ?></td>
                    <td><?= htmlspecialchars($c['descripcion']) ?></td>
                    <td><?= $c['activo'] ? '✅ Activa' : '🚫 Inactiva' ?></td>
                    <td>
                        <a href="editar_categoria.php?id=<?= $c['idCategoria'] ?>">✏️</a>
                        <?php if ($c['activo']): ?>
                            <a href="deshabilitar_categoria.php?id=<?= $c['idCategoria'] ?>" onclick="return confirm('¿Deshabilitar esta categoría?')">🗑️</a>
                        <?php else: ?>
                            <a href="activar_categoria.php?id=<?= $c['idCategoria'] ?>" onclick="return confirm('¿Activar esta categoría?')">♻️</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <div style="margin-top: 1rem;">
        <a href="agregar_categoria.php" class="button">➕ Agregar Categoría</a>
    </div>

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
