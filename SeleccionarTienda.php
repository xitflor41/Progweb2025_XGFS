<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['idTienda'])) {
    $_SESSION['tienda_seleccionada'] = intval($_POST['idTienda']);
    
    // Verificar que los artículos en el carrito estén disponibles en la nueva tienda
    if (isset($_SESSION['carrito'])) {
        require_once 'MySQLConnector.php';
        $dbConnector = new MysqlConnector();
        $dbConnector->Connect();
        
        foreach ($_SESSION['carrito'] as $idArticulo => $item) {
            $query = "SELECT cantidad FROM Existencia 
                     WHERE idArticulo = ? AND idTienda = ?";
            $stmt = $dbConnector->PrepareStatement($query);
            $stmt->bind_param("ii", $idArticulo, $_SESSION['tienda_seleccionada']);
            $stmt->execute();
            $existencia = $stmt->get_result()->fetch_assoc();
            
            if (!$existencia || $item['cantidad'] > $existencia['cantidad']) {
                unset($_SESSION['carrito'][$idArticulo]);
                $_SESSION['error'] = "Algunos productos no están disponibles en la nueva tienda y fueron removidos";
            }
        }
    }
}

header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'Articulos.php'));
exit();
?>