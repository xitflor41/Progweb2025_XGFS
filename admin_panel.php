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
        <link rel="stylesheet" href="Estilo.css">
    <meta charset="UTF-8">
    <title>Panel de Administrador - Joyería Suarez</title>

    <style>

    .container ul{
        list-style: none;
    padding: 0;
    margin: 0;
    }

    .container ul li {
        
        margin: 10px 0;
    text-align: center;
    }

    .container ul li a {
       display: inline-block;
    padding: 6px 12px;
    font-size: 14px;
    background-color: #e0e0e0;
    border-radius: 5px;
    transition: background-color 0.3s;
    text-decoration: none;
    color: #333;
    }

    .container ul li a:hover {
        background-color: #d0d0d0;
    }
    .container p{
        text-align: center;
        font-size: 1.2em;
        color: #555;
    }
    


    </style>
</head>
<body>
    <div class="container">
        <a class="logout-btn" href="logout.php">Cerrar sesión</a>
        <h1>Bienvenida, administradora Xitlalic</h1>
        <p>¿Que deseas hacer el dia de hoy?</p>

        <ul>
            <li><a href="gestionar_usuarios.php">Gestionar usuarios</a></li>
            <li><a href="gestionar_articulos.php">Gestionar artículos</a></li>
            <li><a href="ver_ventas.php">Ver reportes de ventas</a></li>
            <li><a href="gestionar_tiendas.php">Gestionar tiendas</a></li>
            <li><a href="gestionar_categorias.php">Gestionar categorías</a></li>
            <li><a href="perfil2.php">Gestionar mi perfil</a></li>
            <!-- Agrega más enlaces según las funciones de tu sistema -->
        </ul>
    </div>
</body>
</html>
