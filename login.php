<?php
session_start();
require_once 'MySQLConnector.php';


if ($email === 'admin@gmail.com' && $password === 'admin123') {
    // Iniciar sesión como administrador
    $_SESSION['admin'] = true;
    $_SESSION['nombre'] = 'Administrador';
    $_SESSION['email'] = $email;

    header("Location: adminPanel.php"); // o la página de administración que tú crees
    exit();
}


$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        try {
            $dbConnector = new MysqlConnector();
            $dbConnector->Connect();

            // Buscar usuario con datos del cliente
           $query = "SELECT u.idUsuario, u.password, u.idCliente, u.rol, c.nombre, c.apellido 
          FROM Usuarios u
          LEFT JOIN Clientes c ON u.idCliente = c.idCliente
          WHERE u.email = ?";

            
            $stmt = mysqli_prepare($dbConnector->connection, $query);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($usuario = mysqli_fetch_assoc($result)) {
                if (password_verify($password, $usuario['password'])) {
                // Iniciar sesión
                $_SESSION['idUsuario'] = $usuario['idUsuario'];
                $_SESSION['idCliente'] = $usuario['idCliente'];
                $_SESSION['nombre'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
                $_SESSION['email'] = $email;
                $_SESSION['rol'] = $usuario['rol']; // <-- Aquí agregas el rol

                // Actualizar último acceso
                $updateQuery = "UPDATE Usuarios SET ultimo_acceso = NOW() WHERE idUsuario = ?";
                $updateStmt = mysqli_prepare($dbConnector->connection, $updateQuery);
                mysqli_stmt_bind_param($updateStmt, "i", $usuario['idUsuario']);
                mysqli_stmt_execute($updateStmt);

                // Redirigir según el rol
                if ($usuario['rol'] === 'admin') {
                    header("Location: admin_panel.php"); // o la página que tú definas para admins
                } else {
                    header("Location: Articulos.php"); // para clientes
                }
                exit();
            

                } else {
                    $error = "Contraseña incorrecta";
                }
            } else {
                $error = "Usuario no encontrado";
            }
            
            $dbConnector->CloseConnection();
        } catch(Exception $e) {
            $error = "Error al conectar con el sistema. Intente más tarde.";
        }
    } else {
        $error = "Por favor ingrese email y contraseña";
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
    </style>
</head>
<body>
    <div id="header">
            <h1>Joyería Suarez</h1>
        </div>
    <div class="container">

        

        <h2>Iniciar Sesión</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" required>
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