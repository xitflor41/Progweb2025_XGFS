<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="Estilo.css">
    <title>Joyería Suarez - Catálogo</title>
    <meta charset="UTF-8">
    <style>
    </style>
</head>
<body>
    <div id="wrap">
        <div id="header">
            <div class="header-buttons">
                <button class="header-btn" onclick="location.href='login.php'">Acceder</button>
                <button class="header-btn" onclick="location.href='SignUp.php'">Crear cuenta</button>
                <button class="header-btn" onclick="location.href='index.php'">Volver al inicio</button>

            </div>
            <h1>Joyería Suarez</h1>
            <h2>Catálogo de Artículos</h2>

        <?php
        require_once 'MySQLConnector.php';
        
        try {
            $dbConnector = new MysqlConnector();
            $dbConnector->Connect();
            
            // Consulta corregida usando 'descripcion' en lugar de 'nombre'
            $query = "SELECT a.*, c.descripcion as categoria 
                      FROM Articulos a
                      LEFT JOIN Categorias c ON a.idCategoria = c.idCategoria
                      ORDER BY a.idArticulo";
            
            $result = $dbConnector->ExecuteQuery($query);
            
            if(mysqli_num_rows($result) > 0) {
                echo '<div id="catalogo">';
                
                while ($articulo = mysqli_fetch_assoc($result)) {
                    echo '<div class="articulo">';
                    echo '<h2>' . htmlspecialchars($articulo['descripcion']) . '</h2>';
                    
                    // Mostrar categoría si existe
                    if(!empty($articulo['categoria'])) {
                        echo '<p>Categoría: ' . htmlspecialchars($articulo['categoria']) . '</p>';
                    }
                    
                    // Mostrar imagen si existe (convertida de BLOB a base64)
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
                    
                    echo '</div>'; // Cierre de div.articulo
                }
                
                echo '</div>'; // Cierre de div#catalogo
            } else {
                echo '<p>No se encontraron artículos en el catálogo.</p>';
            }
            
            mysqli_free_result($result);
            
        } catch(Exception $e) {
            echo '<div class="error">';
            echo '<h3>Error al cargar el catálogo</h3>';
            
            // Mostrar detalles del error solo en entorno de desarrollo
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