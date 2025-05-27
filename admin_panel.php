<?php
session_start();

// Verifica si hay sesión y si el rol es 'admin'
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administrador - Joyería Suarez</title>
    <link rel="stylesheet" href="Estilo.css">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f3f3f3; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px #ccc; }
        h1 { color: #444; }
        .logout-btn {
            background-color: #c0392b;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            float: right;
        }
        .logout-btn:hover {
            background-color: #e74c3c;
        }
        ul li { margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <a class="logout-btn" href="logout.php">Cerrar sesión</a>
        <h1>Bienvenido, Administrador</h1>
        <p>Este es el panel de administración. Desde aquí puedes gestionar el sistema.</p>

        <ul>
            <li><a href="gestionar_usuarios.php">Gestionar usuarios</a></li>
            <li><a href="gestionar_articulos.php">Gestionar artículos</a></li>
            <li><a href="ver_ventas.php">Ver reportes de ventas</a></li>
            <!-- Agrega más enlaces según las funciones de tu sistema -->
        </ul>
    </div>
</body>
</html>
