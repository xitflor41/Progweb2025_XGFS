<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['idUsuario'])) {
    header("Location: login.php");
    exit();
}


require_once 'MySQLConnector.php';

$con = new MysqlConnector();
$con->Connect();

// Obtener el idUsuario desde la sesión
$idUsuario = $_SESSION['idUsuario'];

$query = "SELECT C.* 
          FROM Usuarios U 
          JOIN Clientes C ON U.idCliente = C.idCliente 
          WHERE U.idUsuario = ?";

$stmt = $con->PrepareStatement($query);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$result = $stmt->get_result();
$cliente = $result->fetch_assoc();

$_SESSION['nombre'] = $cliente['nombre'];
$_SESSION['email'] = $cliente['correo'];
$stmt->close();
$con->CloseConnection();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="Estilo.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Joyería Suarez</title>
    <style>
        .profile-info { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .info-group { margin-bottom: 15px; }
        .info-label { font-weight: bold; color: #555; }
        input[type="text"], input[type="email"], input[type="number"] {
            width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;
        }
        .update-btn { margin-top: 20px; padding: 10px 20px; background: #333; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Joyería Suarez</h1>
        </div>
    </div>
    
    <div class="container">
        <div class="profile-header">
            <h2 class="profile-title">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h2>
            <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
        </div>

        <form action="actualizar_perfil.php" method="POST" class="profile-info">
            <input type="hidden" name="idCliente" value="<?php echo $cliente['idCliente']; ?>">

            <div>
                <div class="info-group">
                    <div class="info-label">Nombre:</div>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required>
                </div>
                <div class="info-group">
                    <div class="info-label">Apellido:</div>
                    <input type="text" name="apellido" value="<?php echo htmlspecialchars($cliente['apellido']); ?>" required>
                </div>
                <div class="info-group">
                    <div class="info-label">Email:</div>
                    <input type="email" name="correo" value="<?php echo htmlspecialchars($cliente['correo']); ?>" required>
                </div>
                <div class="info-group">
                    <div class="info-label">Dirección:</div>
                    <input type="text" name="direccion" value="<?php echo htmlspecialchars($cliente['direccion']); ?>">
                </div>
                <div class="info-group">
                    <div class="info-label">Colonia:</div>
                    <input type="text" name="colonia" value="<?php echo htmlspecialchars($cliente['colonia']); ?>">
                </div>
            </div>

            <div>
                <div class="info-group">
                    <div class="info-label">Ciudad:</div>
                    <input type="text" name="ciudad" value="<?php echo htmlspecialchars($cliente['ciudad']); ?>">
                </div>
                <div class="info-group">
                    <div class="info-label">Estado:</div>
                    <input type="text" name="estado" value="<?php echo htmlspecialchars($cliente['estado']); ?>">
                </div>
                <div class="info-group">
                    <div class="info-label">País:</div>
                    <input type="text" name="pais" value="<?php echo htmlspecialchars($cliente['pais']); ?>">
                </div>
                <div class="info-group">
                    <div class="info-label">Código Postal:</div>
                    <input type="number" name="codigo_postal" value="<?php echo htmlspecialchars($cliente['codigo_postal']); ?>">
                </div>
                <button type="submit" class="update-btn">Actualizar Perfil</button>
            </div>
        </form>
    </div>
</body>
</html>
