<?php
session_start();

// Solo administradores pueden acceder
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'MySQLConnector.php';

$error = '';
$tiendas = [];
$mostrarInactivas = isset($_GET['inactivas']) && $_GET['inactivas'] == 1;

try {
    $dbConnector = new MysqlConnector();
    $dbConnector->Connect();

    // Obtener tiendas activas o inactivas según el parámetro
    $query = "
        SELECT idTienda, nombre_sucursal, ciudad, direccion, codigo_postal, horario, activo
        FROM Tiendas
        WHERE activo = ?
        ORDER BY nombre_sucursal ASC
    ";

    $activo = $mostrarInactivas ? 0 : 1;
    $stmt = $dbConnector->connection->prepare($query);
    $stmt->bind_param("i", $activo);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $tiendas[] = $row;
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestión de Tiendas</title>
    <link rel="stylesheet" href="Estilo.css">
</head>
<body>
    <div id="wrap">
        <div id="header">
            <a href="admin_panel.php" class="btn-volver">← Volver al Panel</a>
            <h1>Gestión de Tiendas <?= $mostrarInactivas ? "(Inactivas)" : "(Activas)" ?></h1>
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
        </div>

        <div style="margin-bottom: 1rem;">
            <a href="gestionar_tiendas.php?inactivas=<?= $mostrarInactivas ? 0 : 1 ?>" class="button">
                <?= $mostrarInactivas ? "🔙 Ver activas" : "🛑 Ver inactivas" ?>
            </a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Sucursal</th>
                    <th>Ciudad</th>
                    <th>Dirección</th>
                    <th>Código Postal</th>
                    <th>Horario</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tiendas)): ?>
                    <tr><td colspan="7">No hay tiendas <?= $mostrarInactivas ? "inactivas" : "activas" ?> registradas.</td></tr>
                <?php else: ?>
                    <?php foreach ($tiendas as $t): ?>
                        <tr>
                            <td><?= $t['idTienda'] ?></td>
                            <td><?= htmlspecialchars($t['nombre_sucursal']) ?></td>
                            <td><?= htmlspecialchars($t['ciudad']) ?></td>
                            <td><?= htmlspecialchars($t['direccion']) ?></td>
                            <td><?= $t['codigo_postal'] ?></td>
                            <td><?= htmlspecialchars($t['horario']) ?></td>
                            <td>
                                <a href="editar_tienda.php?id=<?= $t['idTienda'] ?>">✏️</a>
                                <?php if ($t['activo'] == 1): ?>
                                    <a href="deshabilitar_tienda.php?id=<?= $t['idTienda'] ?>" onclick="return confirm('¿Seguro que deseas deshabilitar esta tienda?')">🛑</a>
                                <?php else: ?>
                                    <a href="activar_tienda.php?id=<?= $t['idTienda'] ?>" onclick="return confirm('¿Seguro que deseas activar esta tienda?')">✅</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div style="margin-top: 1rem;">
            <a href="agregar_tienda.php" class="button">➕ Agregar Tienda</a>
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
