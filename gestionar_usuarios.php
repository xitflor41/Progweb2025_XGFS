<?php
session_start();

// Solo administradores pueden acceder
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'MySQLConnector.php';

$error = '';
$usuarios = [];

try {
    $dbConnector = new MysqlConnector();
    $dbConnector->Connect();

    // Obtener todos los usuarios con datos del cliente si existen
    $query = "
        SELECT u.idUsuario, u.username, u.email, u.fecha_registro, u.ultimo_acceso, u.activo, u.rol,
               c.nombre AS nombre_cliente, c.apellido AS apellido_cliente
        FROM Usuarios u
        LEFT JOIN Clientes c ON u.idCliente = c.idCliente
        ORDER BY u.fecha_registro DESC
    ";
    $result = $dbConnector->connection->query($query);

    if (!$result) {
        throw new Exception("Error al obtener usuarios: " . $dbConnector->connection->error);
    }

    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="Estilo.css">
</head>
<body>
    <div id="wrap">
        <div id="header">
            <a href="admin_panel.php" class="btn-volver">← Volver al Panel</a>
            <h1>Gestión de Usuarios</h1>
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Cliente</th>
                    <th>Rol</th>
                    <th>Activo</th>
                    <th>Último Acceso</th>
                    <th>Registrado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr><td colspan="9">No hay usuarios registrados.</td></tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?= $u['idUsuario'] ?></td>
                            <td><?= htmlspecialchars($u['username']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= $u['nombre_cliente'] ? htmlspecialchars($u['nombre_cliente'] . ' ' . $u['apellido_cliente']) : '-' ?></td>
                            <td><?= $u['rol'] ?></td>
                            <td><?= $u['activo'] ? 'Sí' : 'No' ?></td>
                            <td><?= $u['ultimo_acceso'] ?: 'N/A' ?></td>
                            <td><?= $u['fecha_registro'] ?></td>
                            <td>
                                <a href="editar_usuario.php?id=<?= $u['idUsuario'] ?>">✏️</a>
                                <a href="activar_usuario.php?id=<?= $u['idUsuario'] ?>&accion=<?= $u['activo'] ? 'desactivar' : 'activar' ?>">
                                    <?= $u['activo'] ? '🚫' : '✅' ?>
                                </a>
                                <a href="deshabilitar_usuario.php?id=<?= $u['idUsuario'] ?>" onclick="return confirm('¿Seguro que deseas deshabilitar este usuario?')">🗑️</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div style="margin-top: 1rem;">
            <a href="agregar_usuario.php" class="button">➕ Agregar Usuario</a>
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
