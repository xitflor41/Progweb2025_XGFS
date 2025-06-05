<?php
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'MySQLConnector.php';

$error = '';
$exito = '';
$usuario = null;
$clientes = [];

try {
    $db = new MysqlConnector();
    $db->Connect();

    // Obtener ID del usuario a editar
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception("ID de usuario inválido.");
    }
    $idUsuario = intval($_GET['id']);

    // Obtener datos del usuario
    $stmt = $db->connection->prepare("SELECT * FROM Usuarios WHERE idUsuario = ?");
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("Usuario no encontrado.");
    }
    $usuario = $result->fetch_assoc();
    $stmt->close();

    // Obtener lista de clientes
    $resClientes = $db->connection->query("SELECT idCliente, nombre, apellido FROM Clientes");
    while ($row = $resClientes->fetch_assoc()) {
        $clientes[] = $row;
    }

    // Si se envió el formulario
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $rol = $_POST['rol'];
        $activo = isset($_POST['activo']) ? 1 : 0;
        $idCliente = !empty($_POST['idCliente']) ? intval($_POST['idCliente']) : null;
        $password = $_POST['password'];

        if (empty($username) || empty($email)) {
    $error = "Nombre de usuario y correo electrónico son obligatorios.";
} else {
    // Comprobación de duplicados
    $stmtCheck = $db->connection->prepare("SELECT idUsuario FROM Usuarios WHERE (email = ? OR username = ?) AND idUsuario != ?");
    $stmtCheck->bind_param("ssi", $email, $username, $idUsuario);
    $stmtCheck->execute();
    $stmtCheck->store_result();
    if ($stmtCheck->num_rows > 0) {
        $error = "El correo electrónico o nombre de usuario ya están en uso por otro usuario.";
    } else {
        if (!empty($password)) {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->connection->prepare("
                UPDATE Usuarios 
                SET username = ?, email = ?, password = ?, rol = ?, activo = ?, idCliente = ?
                WHERE idUsuario = ?
            ");
            $stmt->bind_param("ssssiii", $username, $email, $passwordHash, $rol, $activo, $idCliente, $idUsuario);
            
        } else {
            $stmt = $db->connection->prepare("
                UPDATE Usuarios 
                SET username = ?, email = ?, rol = ?, activo = ?, idCliente = ?
                WHERE idUsuario = ?
            ");
            $stmt->bind_param("sssiii", $username, $email, $rol, $activo, $idCliente, $idUsuario);

        }

        if ($stmt === false) {
            $error = "Error al preparar la consulta: " . $db->connection->error;
        } else {
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    header("Location: editar_usuario.php?id=$idUsuario&exito=1");
                    exit();
                } else {
                    $error = "No se realizaron cambios. Verifica si los datos son diferentes.";
                }
            } else {
                if ($stmt->errno === 1062) {
                    $error = "El correo electrónico o nombre de usuario ya están registrados.";
                } else {
                    $error = "Error al ejecutar la consulta: " . $stmt->error;
                }
            }
        }
    }



            $stmt->close();
        }
    }

    $db->CloseConnection();
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}

// Mensaje de éxito tras redirección
if (isset($_GET['exito'])) {
    $exito = "Usuario actualizado correctamente.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="Estilo.css">
</head>
<body>
<div id="wrap">
    <div id="header">
        <a href="gestionar_usuarios.php" class="btn-volver">← Volver</a>
        <h1>Editar Usuario</h1>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($exito): ?>
            <div class="exito"><?= htmlspecialchars($exito) ?></div>
        <?php endif; ?>
    </div>

    <?php if ($usuario): ?>
        <form method="POST">
            <label>Nombre de Usuario *</label>
            <input type="text" name="username" value="<?= htmlspecialchars($usuario['username']) ?>" required>

            <label>Correo Electrónico *</label>
            <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>

            <label>Contraseña (deja en blanco para no cambiarla)</label>
            <input type="password" name="password">

            <label>Rol *</label>
            <select name="rol" required>
                <option value="cliente" <?= $usuario['rol'] === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                <option value="admin" <?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
            </select>

            <label>Cliente Asociado</label>
            <select name="idCliente">
                <option value="">-- Ninguno --</option>
                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?= $cliente['idCliente'] ?>"
                        <?= ($usuario['idCliente'] == $cliente['idCliente']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>
                <input type="checkbox" name="activo" <?= $usuario['activo'] ? 'checked' : '' ?>>
                Usuario activo
            </label>

            <button type="submit">Guardar Cambios</button>
        </form>
    <?php endif; ?>

    <div id="footer">
        <p>Diseñado por Xitlalic Guadalupe Flores Salcedo.</p>
    </div>
</div>
</body>
</html>
