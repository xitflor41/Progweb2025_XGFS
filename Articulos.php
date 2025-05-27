<?php
session_start();

// Verificar sesión
if (!isset($_SESSION['idUsuario'])) {
    header("Location: login.php");
    exit();
}

require_once 'MySQLConnector.php';

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Procesar mensajes
$mensaje = $_SESSION['mensaje'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['mensaje'], $_SESSION['error']);

// Conexión a la base de datos
$dbConnector = new MysqlConnector();
$dbConnector->Connect();

// Obtener tiendas disponibles
$queryTiendas = "SELECT idTienda, nombre_sucursal FROM Tiendas";
$tiendas = $dbConnector->ExecuteQuery($queryTiendas)->fetch_all(MYSQLI_ASSOC);

// Establecer tienda seleccionada (por defecto la primera)
if (!isset($_SESSION['tienda_seleccionada']) && !empty($tiendas)) {
    $_SESSION['tienda_seleccionada'] = $tiendas[0]['idTienda'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="Estilo.css">
    <title>Joyería Suarez - Catálogo</title>
    <meta charset="UTF-8">

    <!-- En Articulos.php y otras páginas -->
<?php if ($totalProductos > 0): ?>
    <a href="carrito.php" class="contador-carrito"><?= $totalProductos ?></a>
<?php endif; ?>

    <style>
    </style>
</head>
<body>
    <?php 
    // Calcular total de productos para el contador
    $totalProductos = 0;
    if (isset($_SESSION['carrito'])) {
        foreach ($_SESSION['carrito'] as $item) {
            $totalProductos += $item['cantidad'];
        }
    }
    ?>
    <?php if ($totalProductos > 0): ?>
        <div class="contador-carrito"><?= $totalProductos ?></div>
    <?php endif; ?>


    <div id="wrap">
        <div class="header-carrito">
            
            <a href="carrito.php">
                <img src="carro-de-la-compra.png" alt="Carrito de Compras" width="30" height="30"></a>
                
                
        </div>
        <div id="header">
            <div class="logout-container">
                 <a href="logout.php" class="logout-btn">Cerrar Sesión</a>

            </div>
            <h1>Joyería Suarez</h1>
            <h2>Catálogo de Artículos</h2>
            <div class="profile-header">
                <h2 class="profile-title">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h2>
                
            </div>
            
            <!-- Selector de tienda -->
            <form method="post" action="SeleccionarTienda.php" class="tienda-selector">
                <label>Selecciona tu tienda: </label>
                <select name="idTienda" onchange="this.form.submit()">
                    <?php foreach ($tiendas as $tienda): ?>
                        <option value="<?= $tienda['idTienda'] ?>" 
                            <?= ($_SESSION['tienda_seleccionada'] ?? '') == $tienda['idTienda'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tienda['nombre_sucursal']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            
            <!-- Mostrar mensajes -->
            <?php if ($mensaje): ?>
                <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
        </div>

        <?php
        try {
            // Consulta para obtener artículos
            $query = "SELECT 
            a.*, 
            c.descripcion AS categoria
          FROM Articulos a, Categorias c
          WHERE a.idCategoria = c.idCategoria
          ORDER BY a.idArticulo";
            
            $result = $dbConnector->ExecuteQuery($query);
            
           if(mysqli_num_rows($result) > 0) {
    echo '<div id="catalogo">';
    
    while ($articulo = mysqli_fetch_assoc($result)) {
        try {
            // Obtener existencias para este artículo en la tienda seleccionada
            $queryExistencia = "SELECT cantidad FROM Existencia 
                               WHERE idArticulo = ? AND idTienda = ?";
            
            // Preparar la consulta correctamente
            $stmtExistencia = $dbConnector->connection->prepare($queryExistencia);
            if (!$stmtExistencia) {
                throw new Exception("Error al preparar consulta de existencia: " . $dbConnector->connection->error);
            }
            
            // Vincular parámetros
            $stmtExistencia->bind_param("ii", $articulo['idArticulo'], $_SESSION['tienda_seleccionada']);
            
            // Ejecutar
            if (!$stmtExistencia->execute()) {
                throw new Exception("Error al ejecutar consulta de existencia: " . $stmtExistencia->error);
            }
            
            // Obtener resultados
            $resultExistencia = $stmtExistencia->get_result();
            $existencia = $resultExistencia->fetch_assoc();
            $stock = $existencia['cantidad'] ?? 0;
            
            // Obtener existencias en todas las tiendas
            $queryTodasExistencias = "SELECT t.nombre_sucursal, e.cantidad 
                                    FROM Existencia e
                                    JOIN Tiendas t ON e.idTienda = t.idTienda
                                    WHERE e.idArticulo = ?";
            
            $stmtTodas = $dbConnector->connection->prepare($queryTodasExistencias);
            if (!$stmtTodas) {
                throw new Exception("Error al preparar consulta de todas las existencias: " . $dbConnector->connection->error);
            }
            
            $stmtTodas->bind_param("i", $articulo['idArticulo']);
            if (!$stmtTodas->execute()) {
                throw new Exception("Error al ejecutar consulta de todas las existencias: " . $stmtTodas->error);
            }
            
            $resultTodas = $stmtTodas->get_result();
            $todasExistencias = $resultTodas->fetch_all(MYSQLI_ASSOC);
            
            // Mostrar el artículo
            echo '<div class="articulo">';
            echo '<h2>' . htmlspecialchars($articulo['descripcion']) . '</h2>';
            
            if(!empty($articulo['categoria'])) {
                echo '<p>Categoría: ' . htmlspecialchars($articulo['categoria']) . '</p>';
            }
            
            if(!empty($articulo['imagen'])) {
                $imagenData = base64_encode($articulo['imagen']);
                $imagenSrc = 'data:image/jpeg;base64,' . $imagenData;
                echo '<img class="articulo-img" src="' . $imagenSrc . '" alt="' . htmlspecialchars($articulo['descripcion']) . '">';
            } else {
                echo '<div class="articulo-img"></div>';
            }
            
            echo '<p class="precio">$' . number_format($articulo['precio'], 2) . '</p>';
            
            if(!empty($articulo['caracteristicas'])) {
                echo '<p class="caracteristicas">' . htmlspecialchars($articulo['caracteristicas']) . '</p>';
            }
            
            // Mostrar disponibilidad
            echo '<div class="disponibilidad">';
            echo '<strong>Disponibilidad: </strong>';
            if ($stock > 0) {
                echo '<span class="stock-disponible">' . $stock . ' en stock</span>';
            } else {
                echo '<span class="stock-agotado">Agotado</span>';
            }
            
            if (!empty($todasExistencias)) {
                echo '<details><summary>Ver en otras tiendas</summary><ul>';
                foreach ($todasExistencias as $exist) {
                    echo '<li>' . htmlspecialchars($exist['nombre_sucursal']) . ': ' . $exist['cantidad'] . '</li>';
                }
                echo '</ul></details>';
            }
            echo '</div>';
            
            // Formulario para agregar al carrito
            echo '<form method="POST" action="Comprar.php">';
            echo '<input type="hidden" name="idArticulo" value="' . $articulo['idArticulo'] . '">';
            echo '<input type="hidden" name="accion" value="agregar">';
            
            if ($stock > 0) {
                echo '<button type="submit" class="btn-carrito">Añadir al carrito</button>';
            } else {
                echo '<button type="button" class="btn-carrito" disabled>Agotado</button>';
            }
            echo '</form>';
            
            echo '</div>'; // Cierre de div.articulo
            
            // Cerrar statements
            $stmtExistencia->close();
            $stmtTodas->close();
            
        } catch (Exception $e) {
            echo '<div class="error">Error al cargar artículo: ' . htmlspecialchars($e->getMessage()) . '</div>';
            continue; // Continuar con el siguiente artículo
        }
    }
    
    echo '</div>'; // Cierre de div#catalogo
} else {
    echo '<p>No se encontraron artículos en el catálogo.</p>';
}

mysqli_free_result($result);
        } catch(Exception $e) {
            echo '<div class="error">';
            echo '<h3>Error al cargar el catálogo</h3>';
            
            if($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_ADDR'] == '127.0.0.1') {
                echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '<pre>Consulta ejecutada: ' . htmlspecialchars($query ?? '') . '</pre>';
            } else {
                echo '<p>Por favor, intente nuevamente más tarde.</p>';
            }
            
            echo '</div>';
        } finally {
            if(isset($dbConnector)) {
                $dbConnector->CloseConnection();
            }
        }
        ?>

        <div id="footer">
            <p>Designed by Xitlalic Guadalupe Flores Salcedo.</p>
        </div>
    </div>
</body>
</html>