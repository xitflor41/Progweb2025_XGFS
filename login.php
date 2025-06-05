<?php
session_start();
require_once 'MySQLConnector.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emailOrUsername = trim($_POST['email']);  // campo puede ser email o username
    $password = trim($_POST['password']);

    if (!empty($emailOrUsername) && !empty($password)) {
        try {
            $dbConnector = new MysqlConnector();
            $dbConnector->Connect();

            // Buscar por email o username, y traer campo 'activo'
            $query = "SELECT u.idUsuario, u.password, u.idCliente, u.rol, u.username, u.activo, c.nombre, c.apellido 
                      FROM Usuarios u
                      LEFT JOIN Clientes c ON u.idCliente = c.idCliente
                      WHERE u.email = ? OR u.username = ?";

            $stmt = mysqli_prepare($dbConnector->connection, $query);
            mysqli_stmt_bind_param($stmt, "ss", $emailOrUsername, $emailOrUsername);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($usuario = mysqli_fetch_assoc($result)) {
                if ($usuario['activo'] == 0) {
                    $error = "Este usuario ha sido deshabilitado o baneado.";
                } elseif (password_verify($password, $usuario['password'])) {
                    // Iniciar sesión
                    $_SESSION['idUsuario'] = $usuario['idUsuario'];
                    $_SESSION['idCliente'] = $usuario['idCliente'];
                    $_SESSION['nombre'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
                    $_SESSION['email'] = $usuario['username'];
                    $_SESSION['rol'] = $usuario['rol'];

                    // Actualizar último acceso
                    $updateQuery = "UPDATE Usuarios SET ultimo_acceso = NOW() WHERE idUsuario = ?";
                    $updateStmt = mysqli_prepare($dbConnector->connection, $updateQuery);
                    mysqli_stmt_bind_param($updateStmt, "i", $usuario['idUsuario']);
                    mysqli_stmt_execute($updateStmt);

                    // Redirigir
                    if ($usuario['rol'] === 'admin') {
                        header("Location: admin_panel.php");
                    } else {
                        header("Location: Articulos.php");
                    }
                    exit();
                } else {
                    $error = "Contraseña incorrecta.";
                }
            } else {
                $error = "Usuario o correo no encontrado.";
            }

            $dbConnector->CloseConnection();
        } catch (Exception $e) {
            $error = "Error al conectar con el sistema. Intente más tarde.";
        }
    } else {
        $error = "Por favor ingresa tu correo/usuario y contraseña.";
    }
}
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="Estilo.css">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Joyería Suarez</title>
    <style>
        h2 { color:rgb(14, 11, 8); text-align: center; margin-bottom: 20px; font-family: Times; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        #header .index-btn {
            position: absolute;
            top: 10px;
            left: 20px;
            background-color: #1e1e19;
            font-family: 'Times New Roman', Times, serif;
            font-weight: lighter;
            color: white;
            letter-spacing: 1px;
            border: 1px solid #ffffff;
            padding: 5px 10px;
            font-size: 12px;
            cursor: pointer;
            border-radius: 8px;
            text-decoration: none;
        }

        #header .index-btn:hover {
            background-color: #333;
        }
        </style>
</head>
<body>
    <div id="header">
        <a href="index.php" class="index-btn">Inicio</a>
        <h1>Joyería Suarez</h1>
        
    </div>
    <div class="container">

        <h2>Iniciar Sesión</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Correo o nombre de usuario:</label>
                <input type="text" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Ingresar</button>
        </form>
        
        <div class="register-link">
            ¿No tienes cuenta? <a href="SignUp.php">Regístrate aquí</a>
        </div>
    </div>

    <div id="footer">
                <p>Designed by Xitlalic Guadalupe Flores Salcedo.</p>
            </div>
</body>
</html>