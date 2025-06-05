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
        .header {
            font-family: 'Times New Roman', Times, serif;
            text-align: center;
            font-weight: lighter;
            letter-spacing: -1px;
        }

        .menu {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        .menu ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.menu ul li {
    margin: 10px 0;
    text-align: center; /* Centra el botón si es inline-block */
}

.menu ul li a {
    display: inline-block;
    padding: 6px 12px;
    font-size: 14px;
    background-color: #e0e0e0;
    border-radius: 5px;
    transition: background-color 0.3s;
    text-decoration: none;
    color: #333;
}

.menu ul li a:hover {
    background-color: #d0d0d0;
}

    </style>
</head>
<body>
    <div class="header">
        
            <h1>Joyería Suarez</h1>
        
    </div>
    
    <div class="container">
        <div class="profile-header">
            <h2 class="profile-title">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h2>

            <div class="header-buttons">
            <button class="header-btn" onclick="location.href='logout.php'">Cerrar Sesion</button>
            <button class="header-btn" onclick="location.href='Articulos.php'">Volver a la pagina principal</button>
            </div>
           
        </div>

        <div class="menu">

            <h2>¿Que deseas hacer hoy?</h2>
            <ul>
                <li><a href="editar_perfil.php">Editar mis datos</a></li>
                <li><a href="ver_compras.php">Mis compras</a></li>
            </ul>
        </div>
    </div>
</body>
</html>
