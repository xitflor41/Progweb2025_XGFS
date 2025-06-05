<?php
session_start();
require_once 'MySQLConnector.php';

// Verificar sesión y carrito
if (!isset($_SESSION['idCliente'])) {
    header("Location: login.php?redirect=checkout");
    exit();
}

if (empty($_SESSION['carrito'])) {
    header("Location: carrito.php");
    exit();
}

$error = '';
$success = '';
$cliente = [];
$tiendas = [];

try {
    $dbConnector = new MysqlConnector();
    $dbConnector->Connect();

    // Obtener datos del cliente
    $queryCliente = "SELECT * FROM Clientes WHERE idCliente = ?";
    $stmtCliente = mysqli_prepare($dbConnector->connection, $queryCliente);
    mysqli_stmt_bind_param($stmtCliente, "i", $_SESSION['idCliente']);
    mysqli_stmt_execute($stmtCliente);
    $cliente = mysqli_fetch_assoc($stmtCliente->get_result());

    // Obtener lista de tiendas
    $queryTiendas = "SELECT * FROM Tiendas";
    $resultTiendas = $dbConnector->ExecuteQuery($queryTiendas);
    $tiendas = mysqli_fetch_all($resultTiendas, MYSQLI_ASSOC);

    // Calcular total y verificar existencias
    $total = 0;
    $stockDisponible = true;
    $articulos = [];

    foreach ($_SESSION['carrito'] as $idArticulo => $item) {
        // Obtener detalles del artículo
        $queryArticulo = "SELECT * FROM Articulos WHERE idArticulo = ?";
        $stmtArticulo = mysqli_prepare($dbConnector->connection, $queryArticulo);
        mysqli_stmt_bind_param($stmtArticulo, "i", $idArticulo);
        mysqli_stmt_execute($stmtArticulo);
        $articulo = mysqli_fetch_assoc($stmtArticulo->get_result());
        
        // Verificar existencia en tienda seleccionada
        if (isset($_POST['idTienda'])) {
            $queryExistencia = "SELECT cantidad FROM Existencia 
                               WHERE idArticulo = ? AND idTienda = ?";
            $stmtExistencia = mysqli_prepare($dbConnector->connection, $queryExistencia);
            mysqli_stmt_bind_param($stmtExistencia, "ii", $idArticulo, $_POST['idTienda']);
            mysqli_stmt_execute($stmtExistencia);
            $existencia = mysqli_fetch_assoc($stmtExistencia->get_result());
            
            if (!$existencia || $existencia['cantidad'] < $item['cantidad']) {
                $stockDisponible = false;
                $error = "No hay suficiente stock para algunos artículos en la tienda seleccionada";
            }
        }
        
        $articulos[$idArticulo] = $articulo;
        $total += $articulo['precio'] * $item['cantidad'];
    }

    // Procesar el pedido
    if ($_SERVER["REQUEST_METHOD"] == "POST" && $stockDisponible && isset($_POST['idTienda'])) {
        mysqli_begin_transaction($dbConnector->connection);

        try {
            // Registrar cada artículo como venta individual
            foreach ($_SESSION['carrito'] as $idArticulo => $item) {
                $queryVenta = "INSERT INTO Ventas 
                              (idArticulo, idCliente, fecha, idTienda) 
                              VALUES (?, ?, CURDATE(), ?)";
                $stmtVenta = mysqli_prepare($dbConnector->connection, $queryVenta);
                mysqli_stmt_bind_param($stmtVenta, "iii", 
                    $idArticulo, $_SESSION['idCliente'], $_POST['idTienda']);
                mysqli_stmt_execute($stmtVenta);
                
                // Actualizar existencia
                $queryUpdateExistencia = "UPDATE Existencia 
                                        SET cantidad = cantidad - ? 
                                        WHERE idArticulo = ? AND idTienda = ?";
                $stmtUpdate = mysqli_prepare($dbConnector->connection, $queryUpdateExistencia);
                mysqli_stmt_bind_param($stmtUpdate, "iii", 
                    $item['cantidad'], $idArticulo, $_POST['idTienda']);
                mysqli_stmt_execute($stmtUpdate);
            }

            mysqli_commit($dbConnector->connection);
            unset($_SESSION['carrito']);
            $success = "¡Compra realizada con éxito!";
            header("Location: ticket.php?idVenta=último_id_generado");
            exit();

            
        } catch (Exception $e) {
            mysqli_rollback($dbConnector->connection);
            $error = "Error al procesar la compra: " . $e->getMessage();
        }
    }

    $dbConnector->CloseConnection();
} catch (Exception $e) {
    $error = "Error de conexión: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - Joyería Suarez</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        .checkout-section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #8B4513;
            border-bottom: 2px solid #D4AF37;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        select, input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            background-color: #8B4513;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        .btn:hover {
            background-color: #A0522D;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .cart-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }
        .error {
            color: #dc3545;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f8d7da;
            border-radius: 4px;
        }
        .success {
            color: #28a745;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #d4edda;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="checkout-section">
            <h1>Información de Envío</h1>
            
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
                <p><a href="perfil.php">Ver mis pedidos</a></p>
            <?php else: ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Cliente:</label>
                    <input type="text" value="<?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label for="idTienda">Seleccione tienda para recoger:</label>
                    <select id="idTienda" name="idTienda" required>
                        <option value="">-- Seleccione una tienda --</option>
                        <?php foreach ($tiendas as $tienda): ?>
                            <option value="<?php echo $tienda['idTienda']; ?>">
                                <?php echo htmlspecialchars($tienda['nombre_sucursal'] . ' - ' . $tienda['ciudad']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Dirección de facturación:</label>
                    <textarea readonly style="width: 100%; height: 100px;"><?php 
                        echo htmlspecialchars(
                            $cliente['direccion'] . "\n" .
                            $cliente['colonia'] . "\n" .
                            $cliente['ciudad'] . ', ' . $cliente['estado'] . "\n" .
                            $cliente['codigo_postal'] . ', ' . $cliente['pais']
                        ); 
                    ?></textarea>
                </div>
                
                <button type="submit" class="btn">Confirmar Compra</button>
            </form>
            <?php endif; ?>
        </div>

        
        
        <div class="checkout-section">
            <h1>Resumen de Compra</h1>
            
            <?php if (isset($_SESSION['carrito']) && is_array($_SESSION['carrito'])): ?>
            <?php foreach ($_SESSION['carrito'] as $idArticulo => $item): 
                $articulo = $articulos[$idArticulo] ?? ['descripcion' => 'Artículo no encontrado', 'precio' => 0];
            ?>
                <div class="cart-item">
                    <div>
                        <h3><?php echo htmlspecialchars($articulo['descripcion']); ?></h3>
                        <p>Cantidad: <?php echo $item['cantidad']; ?></p>
                        <p>Precio unitario: $<?php echo number_format($articulo['precio'], 2); ?></p>
                    </div>
                    <div>
                        <p>Subtotal: $<?php echo number_format($articulo['precio'] * $item['cantidad'], 2); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay artículos en el carrito.</p>
        <?php endif; ?>

            <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #D4AF37;">
                <h2>Total: $<?php echo number_format($total, 2); ?></h2>
            </div>
        </div>

        <div class="Volver">
            <a href="Articulos.php" class="btn">Volver la pagina principal</a>

        </div>
    </div>
</body>
</html>