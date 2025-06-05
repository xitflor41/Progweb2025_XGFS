<?php
session_start();
require_once 'MySQLConnector.php';

if (!isset($_GET['idVenta'])) {
    die("Venta no especificada.");
}

$idVenta = intval($_GET['idVenta']);

// Conectarse y obtener datos de la venta
$db = new MySQLConnector();
$db->Connect();

// Aquí asumo que hay una tabla `Ventas` y `Articulos` relacionadas
$query = "SELECT v.*, a.descripcion, a.precio, c.nombre, c.apellido 
          FROM Ventas v
          JOIN Articulos a ON v.idArticulo = a.idArticulo
          JOIN Clientes c ON v.idCliente = c.idCliente
          WHERE v.idCliente = ? AND v.fecha = CURDATE()";

$stmt = mysqli_prepare($db->connection, $query);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['idCliente']);
mysqli_stmt_execute($stmt);
$result = $stmt->get_result();
$ventas = mysqli_fetch_all($result, MYSQLI_ASSOC);
$db->CloseConnection();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de Venta</title>
    <style>
        body {
            font-family: monospace;
            padding: 20px;
            background: #fff;
            color: #000;
        }
        .ticket {
            max-width: 400px;
            margin: 0 auto;
            border: 1px dashed #000;
            padding: 20px;
        }
        h2, h3 {
            text-align: center;
        }
        .line {
            display: flex;
            justify-content: space-between;
        }
        .total {
            font-weight: bold;
            margin-top: 10px;
        }
        @media print {
            button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Joyería Suárez</h1>
        <p style="text-align:center;">Ticket de Venta</p>
        <a href="Articulos.php">Volver al inicio</a>
    </div>   


    <div class="ticket">
        <h2>Joyería Suárez</h2>
        <h3>Ticket de Venta</h3>
        <p>Cliente: <?php echo htmlspecialchars($ventas[0]['nombre'] . ' ' . $ventas[0]['apellido']); ?></p>
        <p>Fecha: <?php echo date("d/m/Y"); ?></p>
        <hr>
        <?php $total = 0; ?>
        <?php foreach ($ventas as $venta): ?>
            <div class="line">
                <span><?php echo htmlspecialchars($venta['descripcion']); ?> x1</span>
                <span>$<?php echo number_format($venta['precio'], 2); ?></span>
            </div>
            <?php $total += $venta['precio']; ?>
        <?php endforeach; ?>
        <hr>
        <div class="line total">
            <span>Total:</span>
            <span>$<?php echo number_format($total, 2); ?></span>
        </div>
        <p style="text-align:center; margin-top:20px;">¡Gracias por su compra!</p>
    </div>

    <button onclick="window.print()">Imprimir / Guardar como PDF</button>

    <script>
        window.onload = () => {
            setTimeout(() => window.print(), 500);
        };
    </script>
</body>
</html>
