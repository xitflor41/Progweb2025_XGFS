<?php
require_once 'MySQLConnector.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Activar reporte de errores como excepciones
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $datos = [
    'nombre' => trim($_POST['nombre']),
    'apellido' => trim($_POST['apellido']),
    'correo' => trim($_POST['correo']),
    'email' => trim($_POST['email']),
    'username' => trim($_POST['username']),
    'password' => trim($_POST['password']),
    'confirm_password' => trim($_POST['confirm_password']),
    'direccion' => trim($_POST['direccion']),
    'colonia' => trim($_POST['colonia']),
    'ciudad' => trim($_POST['ciudad']),
    'estado' => trim($_POST['estado']),
    'pais' => trim($_POST['pais']),
    'codigo_postal' => trim($_POST['codigo_postal'])
];


    if ($datos['password'] !== $datos['confirm_password']) {
        $error = "Las contraseñas no coinciden";
    } elseif (strlen($datos['password']) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres";
    } else {
        try {
            $dbConnector = new MySQLConnector();
            $dbConnector->Connect();

            $conn = $dbConnector->connection;
            if (!$conn) {
                throw new Exception("Error de conexión a la base de datos");
            }

            mysqli_begin_transaction($conn);

            // Insertar en Clientes
            $queryCliente = "INSERT INTO Clientes (nombre, apellido, correo, direccion, colonia, ciudad, estado, pais, codigo_postal) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)";
            $stmtCliente = mysqli_prepare($conn, $queryCliente);
            mysqli_stmt_bind_param($stmtCliente, "sssssssss", 
                $datos['nombre'], $datos['apellido'], $datos['correo'],
                $datos['direccion'], $datos['colonia'], $datos['ciudad'],
                $datos['estado'], $datos['pais'], $datos['codigo_postal']);
            mysqli_stmt_execute($stmtCliente);
            $idCliente = mysqli_insert_id($conn);
            mysqli_stmt_close($stmtCliente);

            $queryCheck = "SELECT idUsuario FROM Usuarios WHERE username = ?";
            $stmtCheck = mysqli_prepare($conn, $queryCheck);
            mysqli_stmt_bind_param($stmtCheck, "s", $datos['username']);
            mysqli_stmt_execute($stmtCheck);
            mysqli_stmt_store_result($stmtCheck);

            if (mysqli_stmt_num_rows($stmtCheck) > 0) {
                $error = "El nombre de usuario ya está en uso.";
            } else {
                // Aquí va el bloque de inserción original
            }



            // Insertar en Usuarios
            $hashed_password = password_hash($datos['password'], PASSWORD_BCRYPT);
            $queryUsuario = "INSERT INTO Usuarios (idCliente, email, password, username) VALUES (?, ?, ?, ?)";
            $stmtUsuario = mysqli_prepare($conn, $queryUsuario);
            mysqli_stmt_bind_param($stmtUsuario, "isss", $idCliente, $datos['email'], $hashed_password, $datos['username']);
            
            mysqli_stmt_execute($stmtUsuario);
            mysqli_stmt_close($stmtUsuario);

            mysqli_commit($conn);

            $success = "Registro exitoso. Ahora puedes iniciar sesión.";
        } catch (mysqli_sql_exception $e) {
            if (isset($conn)) {
                mysqli_rollback($conn);
            }
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $error = "El correo electrónico ya está registrado";
            } else {
                $error = "Error al registrar: " . $e->getMessage();
            }
        } catch (Exception $e) {
            $error = "Error general: " . $e->getMessage();
        } finally {
            if (isset($dbConnector)) {
                $dbConnector->CloseConnection();
            }
        }
    }
}
?>


<!-- HTML del formulario de registro -->
<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="Estilo.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Joyería Suarez</title>
    <style>
        
    </style>
</head>
<body>
    <div class="container">
        <h2>Registro de Nuevo Cliente</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="SignUp.php">
            <div class="two-columns">
                <div class="form-group">
                    <label for="nombre">Nombre(s):</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                
                <div class="form-group">
                    <label for="apellido">Apellidos:</label>
                    <input type="text" id="apellido" name="apellido" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="correo">Correo Electrónico:</label>
                <input type="email" id="correo" name="correo" required>
            </div>

            <div class="form-group">
                <label for="email">Confirmar Correo Electrónico:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="username">Nombre de Usuario:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="two-columns">
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" minlength="6" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña:</label>
                    <input type="password" id="confirm_password" name="confirm_password" minlength="6" required>
                </div>
            </div>
            
            
            <div class="form-group">
                <label for="direccion">Dirección:</label>
                <input type="text" id="direccion" name="direccion">
            </div>
            
            <div class="two-columns">
                <div class="form-group">
                    <label for="colonia">Colonia:</label>
                    <input type="text" id="colonia" name="colonia">
                </div>
                
                <div class="form-group">
                    <label for="ciudad">Ciudad:</label>
                    <input type="text" id="ciudad" name="ciudad">
                </div>
            </div>
            
            <div class="two-columns">
                <div class="form-group">
                    <label for="estado">Estado:</label>
                    <input type="text" id="estado" name="estado">
                </div>
                
                <div class="form-group">
                    <label for="pais">País:</label>
                    <input type="text" id="pais" name="pais">
                </div>
            </div>
            
            <div class="form-group">
                <label for="codigo_postal">Código Postal:</label>
                <input type="text" id="codigo_postal" name="codigo_postal">
            </div>
            
            <button type="submit" class="btn">Registrarse</button>
            <p>¿Ya tienes una cuenta? <a href="Login.php">Inicia sesión aquí</a></p>
            <button type="button" class="btn" onclick="window.location.href='index.php'">Regresar</button>
        </form>
    </div>
</body>
</html>