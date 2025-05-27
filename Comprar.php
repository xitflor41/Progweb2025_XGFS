<?php
session_start();

if (!isset($_SESSION['idUsuario'])) {
    header("Location: login.php");
    exit();
}

require_once 'MySQLConnector.php';
$dbConnector = new MysqlConnector();
$dbConnector->Connect();

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Procesar acciones del carrito
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['idArticulo']) && isset($_POST['accion'])) {
    $idArticulo = intval($_POST['idArticulo']);
    $accion = $_POST['accion'];
    $idTienda = $_SESSION['tienda_seleccionada'];

    // Verificar existencia en la tienda
    $queryExistencia = "SELECT cantidad FROM Existencia 
                       WHERE idArticulo = ? AND idTienda = ?";
    $stmtExistencia = $dbConnector->connection->prepare($queryExistencia);
    if (!$stmtExistencia) {
        die("Error al preparar consulta: " . $dbConnector->connection->error);
    }
    $stmtExistencia->bind_param("ii", $idArticulo, $idTienda);
    $stmtExistencia->execute();
    $existencia = $stmtExistencia->get_result()->fetch_assoc();

    // Obtener información del artículo
    $queryArticulo = "SELECT descripcion, precio FROM Articulos WHERE idArticulo = ?";
    $stmtArticulo = $dbConnector->connection->prepare($queryArticulo);
    if (!$stmtArticulo) {
        die("Error al preparar consulta: " . $dbConnector->connection->error);
    }
    $stmtArticulo->bind_param("i", $idArticulo);
    $stmtArticulo->execute();
    $articulo = $stmtArticulo->get_result()->fetch_assoc();

    if ($articulo && $existencia) {
        // Convertir el precio a float correctamente
        $precio = (float)$articulo['precio'];
        
        $stockDisponible = (int)$existencia['cantidad'];
        $enCarrito = $_SESSION['carrito'][$idArticulo]['cantidad'] ?? 0;

        switch ($accion) {
            case 'agregar':
                if ($stockDisponible > $enCarrito) {
                    if (isset($_SESSION['carrito'][$idArticulo])) {
                        $_SESSION['carrito'][$idArticulo]['cantidad']++;
                    } else {
                        $_SESSION['carrito'][$idArticulo] = [
                            'descripcion' => $articulo['descripcion'],
                            'precio' => $precio, // Usamos el precio convertido
                            'cantidad' => 1,
                            'idTienda' => $idTienda
                        ];
                    }
                    $_SESSION['mensaje'] = "Producto agregado al carrito";
                } else {
                    $_SESSION['error'] = "No hay suficiente stock disponible";
                }
                break;

            case 'eliminar':
                unset($_SESSION['carrito'][$idArticulo]);
                $_SESSION['mensaje'] = "Producto eliminado del carrito";
                break;
                
            case 'actualizar':
                if (isset($_POST['cantidad'])) {
                    $nuevaCantidad = (int)$_POST['cantidad'];
                    if ($nuevaCantidad > 0 && $nuevaCantidad <= $stockDisponible) {
                        $_SESSION['carrito'][$idArticulo]['cantidad'] = $nuevaCantidad;
                    }
                }
                break;
        }
        
        // Forzar escritura de la sesión
        session_write_close();
    } else {
        $_SESSION['error'] = "El producto no está disponible en esta tienda";
    }
    
    // Cerrar statements
    $stmtExistencia->close();
    $stmtArticulo->close();
}

header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'Articulos.php'));
exit();
?>