<?php
session_start();
require_once 'MySQLConnector.php';

// Verificar si es cliente
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: login.php");
    exit();
}

// Verificar si tiene asignado un cliente
if (!isset($_SESSION['idCliente'])) {
    die("Error: No se encontró información del cliente en la sesión.");
}

$idCliente = $_SESSION['idCliente'];

try {
    $db = new MysqlConnector();
    $db->Connect();

    $query = "
        SELECT 
            V.folio,
            V.fecha,
            A.descripcion AS descripcionArticulo,
            A.precio,
            T.nombre_sucursal AS tienda,
            T.ciudad
        FROM Ventas V
        INNER JOIN Articulos A ON V.idArticulo = A.idArticulo
        INNER JOIN Tiendas T ON V.idTienda = T.idTienda
        WHERE V.idCliente = ?
        ORDER BY V.fecha DESC
    ";

    $stmt = $db->PrepareStatement($query);
    $stmt->bind_param('i', $idCliente);
    $stmt->execute();
    $result = $stmt->get_result();
    $compras = $result->fetch_all(MYSQLI_ASSOC);

    $db->CloseConnection();
} catch (Exception $e) {
    die("Error al conectar a la base de datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="Estilo.css">
    <meta charset="UTF-8">
    <title>Mis Compras</title>
    
</head>
<body>
    <h1>Mis Compras</h1>

    <?php if (empty($compras)): ?>
        <p>No has realizado compras.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID Compra</th>
                    <th>Fecha</th>
                    <th>Artículo</th>
                    <th>Precio</th>
                    <th>Tienda</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($compras as $compra): ?>
                    <tr>
                        <td><?php echo $compra['folio']; ?></td>
                        <td><?php echo $compra['fecha']; ?></td>
                        <td><?php echo htmlspecialchars($compra['descripcionArticulo']); ?></td>
                        <td>$<?php echo number_format($compra['precio'], 2); ?></td>
                        <td><?php echo htmlspecialchars($compra['tienda'] . ' (' . $compra['ciudad'] . ')'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a class="back-link" href="perfil.php">← Volver al panel del cliente</a>
</body>
</html>
