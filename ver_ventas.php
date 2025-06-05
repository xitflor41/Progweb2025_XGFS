<?php
session_start();
require_once 'MySQLConnector.php';

// Verificar si es administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

try {
    $db = new MySQLConnector();
    $db->Connect();

    $query = "
        SELECT 
            V.folio,
            V.fecha,
            C.nombre AS nombreCliente,
            C.apellido AS apellidoCliente,
            A.descripcion AS descripcionArticulo,
            A.precio,
            T.nombre_sucursal AS tienda,
            T.ciudad
        FROM Ventas V
        INNER JOIN Clientes C ON V.idCliente = C.idCliente
        INNER JOIN Articulos A ON V.idArticulo = A.idArticulo
        INNER JOIN Tiendas T ON V.idTienda = T.idTienda
        ORDER BY V.fecha DESC
    ";

    $result = $db->ExecuteQuery($query);
    $ventas = mysqli_fetch_all($result, MYSQLI_ASSOC);

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
    <title>Ventas Realizadas</title>
    <style>

        table{
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        h1 {
            text-align: center;
            letter-spacing: -.5px;
            font-weight: lighter;
        }

        button {
            background-color: #333;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
    </style>
   
</head>
<body>
    <h1>Ventas Realizadas</h1>

    <?php if (empty($ventas)): ?>
        <p>No se han registrado ventas.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID Venta</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Artículo</th>
                    <th>Precio</th>
                    <th>Tienda</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventas as $venta): ?>
                    <tr>
                        <td><?php echo $venta['folio']; ?></td>
                        <td><?php echo $venta['fecha']; ?></td>
                        <td><?php echo htmlspecialchars($venta['nombreCliente'] . ' ' . $venta['apellidoCliente']); ?></td>
                        <td><?php echo htmlspecialchars($venta['descripcionArticulo']); ?></td>
                        <td>$<?php echo number_format($venta['precio'], 2); ?></td>
                        <td><?php echo htmlspecialchars($venta['tienda'] . ' (' . $venta['ciudad'] . ')'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <form method="post" action="generar_pdf_ventas.php" target="_blank">
    <button type="submit">📄 Descargar PDF</button>
    </form>


    <a class="back-link" href="admin_panel.php">← Volver al panel de administración</a>
</body>
</html>
