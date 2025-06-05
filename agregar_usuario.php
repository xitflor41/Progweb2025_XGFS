<?php
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'MySQLConnector.php';

$error = '';
$exito = '';
$clientes = [];

try {
    $db = new MysqlConnector();
    $db->Connect();

    // Obtener lista de clientes para el dropdown
    $result = $db->connection->query("SELECT idCliente, nombre, apellido FROM Clientes");
    while ($row = $result->fetch_assoc()) {
        $clientes[] = $row;
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $rol = $_POST['rol'];
        $idCliente = !empty($_POST['idCliente']) ? intval($_POST['idCliente']) : null;

        if (empty($username) || empty($email) || empty($password)) {
            $error = "Todos los campos marcados con * son obligatorios.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "El correo no es válido.";
        } else {
            // Hashear contraseña
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            // Insertar nuevo usuario
            $stmt = $db->connection->prepare("
                INSERT INTO Usuarios (username, email, password, rol, idCliente)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("ssssi", $username, $email, $passwordHash, $rol, $idCliente);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $exito = "Usuario agregado exitosamente.";
            } else {
                $error = "Error al agregar usuario. Verifica si el correo o usuario ya existen.";
            }

            $stmt->close();
        }
    }

    $db->CloseConnection();
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Agregar Usuario</title>
    <link rel="stylesheet" href="Estilo.css">
</head>
<body>
<div id="wrap">
    <div id="header">
        <a href="gestionar_usuarios.php" class="btn-volver">← Volver</a>
        <h1>Agregar Nuevo Usuario</h1>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($exito): ?>
            <div class="exito"><?= htmlspecialchars($exito) ?></div>
        <?php endif; ?>
    </div>

    <form method="POST" action="">
        <label for="username">Nombre de Usuario *</label>
        <input type="text" name="username" required>

        <label for="email">Correo Electrónico *</label>
        <input type="email" name="email" required>

        <label for="password">Contraseña *</label>
        <input type="password" name="password" required>

        <label for="rol">Rol *</label>
        <select name="rol" required>
            <option value="cliente">Cliente</option>
            <option value="admin">Administrador</option>
        </select>

        <label for="idCliente">Cliente Asociado (opcional)</label>
        <select name="idCliente">
            <option value="">-- Ninguno --</option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?= $cliente['idCliente'] ?>">
                    <?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Agregar Usuario</button>
    </form>

    <div id="footer">
        <p>Designed by Xitlalic Guadalupe Flores Salcedo.</p>
    </div>
</div>
</body>
</html>
