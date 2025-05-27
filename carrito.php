<?php
session_start();

if (!isset($_SESSION['idUsuario'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

require_once 'MySQLConnector.php';
$dbConnector = new MysqlConnector();
$dbConnector->Connect();

$total = 0;
$totalProductos = 0;

foreach ($_SESSION['carrito'] as $idArticulo => &$item) {
    // Consultar stock disponible en la tienda correspondiente
    $query = "SELECT cantidad FROM Existencia WHERE idArticulo = ? AND idTienda = ?";
    $stmt = $dbConnector->connection->prepare($query);

    if ($stmt) {
        $stmt->bind_param("ii", $idArticulo, $item['idTienda']);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $existencia = $resultado->fetch_assoc();
        $stmt->close();

        // Verificamos existencia y si excede el stock
        $stockDisponible = isset($existencia['cantidad']) ? (int)$existencia['cantidad'] : 0;
        $item['stock_disponible'] = $stockDisponible;
        $item['excede_stock'] = $item['cantidad'] > $stockDisponible;

        // Calcular subtotal y acumular totales
        $precio = (float)$item['precio'];
        $cantidad = (int)$item['cantidad'];
        $subtotal = $precio * $cantidad;
        $total += $subtotal;
        $totalProductos += $cantidad;
    } else {
        $item['stock_disponible'] = 0;
        $item['excede_stock'] = true;
    }
}
unset($item); // Romper la referencia

?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="Estilo.css">
    <title>Joyería Suarez - Carrito</title>
    <meta charset="UTF-8">
    <style>
        /* [Tus estilos actuales...] */
    </style>
</head>
<body>
    <?php if ($totalProductos > 0): ?>
        <div class="contador-carrito"><?= $totalProductos ?></div>
    <?php endif; ?>

    <div class="container">
        <h1>Carrito de Compras</h1>
        
        <?php if (empty($_SESSION['carrito'])): ?>
            <p>Tu carrito está vacío</p>
            <a href="Articulos.php" class="btn">Volver al catálogo</a>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Artículo</th>
                        <th>Precio Unitario</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['carrito'] as $id => $item): 
                        $subtotal = (float)$item['precio'] * (int)$item['cantidad'];
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($item['descripcion']) ?></td>
                            <td>$<?= number_format((float)$item['precio'], 2) ?></td>
                            <td>
                                <form method="post" action="Comprar.php" style="display:inline;">
                                    <input type="hidden" name="idArticulo" value="<?= $id ?>">
                                    <input type="hidden" name="accion" value="actualizar">
                                    <input type="number" name="cantidad" value="<?= $item['cantidad'] ?>" 
                                           min="1" max="<?= $item['stock_disponible'] ?>" 
                                           class="cantidad-input <?= $item['excede_stock'] ? 'error-stock' : '' ?>"
                                           onchange="this.form.submit()">
                                    <?php if ($item['excede_stock']): ?>
                                        <span class="stock-error">(Solo <?= $item['stock_disponible'] ?> disponibles)</span>
                                    <?php endif; ?>
                                </form>
                            </td>
                            <td>$<?= number_format($subtotal, 2) ?></td>
                            <td>
                                <form method="post" action="Comprar.php" style="display:inline;">
                                    <input type="hidden" name="idArticulo" value="<?= $id ?>">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <button type="submit" class="btn btn-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="total">
                Total productos: <?= $totalProductos ?> | Total a pagar: $<?= number_format($total, 2) ?>
            </div>
            
            <div class="actions">
                <a href="Articulos.php" class="btn-sig">Seguir comprando</a>
                <a href="checkout.php" class="btn-success">Proceder al pago</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>