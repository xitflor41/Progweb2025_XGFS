<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['idUsuario'])) {
    header("Location: login.php");
    exit();
}

require_once 'MySQLConnector.php';


?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="StyleTienda.css">
        <style>  
        </style>
        <title>Joyería Suarez</title>
        <meta charset="UTF-8">
        
    </head>
    <body>
        <div id="wrap">
            <div id="header">
                <h1>Joyería Suarez</h1>

                <div class="profile-header">
                    <h2 class="profile-title">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h2>
                    <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
                </div>
        
            </div>
            <div id="content">
                <h2>Tiendas</h2>
                <h1>Nuestras Boutiques</h1>

                <div id="principio">
                    <h1>UN NUEVO CONCEPTO DE TIENDAS</h1>
                    <p>
                        Encuentre su tienda más cercana y descubra nuestra historia, nuestras colecciones y nuestra esencia.
                    </p>
                </div>

                <?php
                // Incluir el conector MySQL
                require_once 'MySQLConnector.php';
                
                // Crear instancia del conector
                $dbConnector = new MysqlConnector();
                
                try {
                    // Conectar a la base de datos
                    $dbConnector->Connect();
                    
                    // Consulta para obtener todas las tiendas
                    $query = "SELECT * FROM Tiendas ORDER BY nombre_sucursal";
                    $result = $dbConnector->ExecuteQuery($query);
                    
                    // Mostrar cada tienda
                    while ($tienda = mysqli_fetch_assoc($result)) {
                        echo '<div class="aparted">';
                        echo '<h1>' . htmlspecialchars($tienda['nombre_sucursal']) . '</h1>';
                        
                        echo '<p>' . htmlspecialchars($tienda['direccion']) . ',<br>';
                        echo htmlspecialchars($tienda['codigo_postal']) . ' ' . htmlspecialchars($tienda['ciudad']) . '</p>';
                        echo '<hr>';
                        echo '<h2>HORARIO:</h2>';
                        echo '<p><strong>' . htmlspecialchars($tienda['horario']) . '</strong></p>';
                        echo '</div>';
                    }
                    
                    // Liberar resultado
                    mysqli_free_result($result);
                    
                } catch(Exception $e) {
                    // En caso de error, mostrar mensaje y continuar con datos estáticos como respaldo
                    echo '<div class="error">Error al obtener datos de tiendas: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    
                    // Datos estáticos de respaldo (ejemplo con una tienda)
                    echo '<div class="aparted">';
                    echo '<h1>SUAREZ ALICANTE</h1>';
                    echo '<p>C/Maisonnave, 43,<br>30003 Alicante</p>';
                    echo '<hr>';
                    echo '<h2>HORARIO:</h2>';
                    echo '<p><strong>Lunes a Sábado 10:00 - 14:00 y 16:30 - 20:30</strong></p>';
                    echo '</div>';
                } finally {
                    // Cerrar conexión
                    if (isset($dbConnector)) {
                        $dbConnector->CloseConnection();
                    }
                }
                ?>

            </div>
            <div id="footer">
                <p>Designed by Xitlalic Guadalupe Flores Salcedo.</p>
            </div>
        </div>
    </body>
</html>