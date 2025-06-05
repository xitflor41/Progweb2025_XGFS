<?php
session_start();

if (!isset($_SESSION['idUsuario'])) {
    header("Location: login.php");
    exit();
}

require_once 'MySQLConnector.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idCliente = $_POST['idCliente'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['correo'];
    $direccion = $_POST['direccion'];
    $colonia = $_POST['colonia'];
    $ciudad = $_POST['ciudad'];
    $estado = $_POST['estado'];
    $pais = $_POST['pais'];
    $codigo_postal = $_POST['codigo_postal'];
    $nuevaContrasena = $_POST['nueva_contrasena'];

    $con = new MysqlConnector();
    $con->Connect();

    // Actualizar cliente
    $stmt = $con->PrepareStatement("
        UPDATE Clientes SET
            nombre = ?, apellido = ?, correo = ?, direccion = ?, colonia = ?,
            ciudad = ?, estado = ?, pais = ?, codigo_postal = ?
        WHERE idCliente = ?
    ");
    $stmt->bind_param("sssssssssi", $nombre, $apellido, $correo, $direccion, $colonia, $ciudad, $estado, $pais, $codigo_postal, $idCliente);
    $stmt->execute();
    $stmt->close();

    // Si se ingresó una nueva contraseña, actualizarla en la tabla Usuarios
    if (!empty($nuevaContrasena)) {
    $hash = password_hash($nuevaContrasena, PASSWORD_DEFAULT);

    $stmt2 = $con->PrepareStatement("UPDATE Usuarios SET password = ? WHERE idUsuario = ?");
    if (!$stmt2) {
        die("Error al preparar la actualización de contraseña: " . $con->connection->error);
    }

    $stmt2->bind_param("si", $hash, $_SESSION['idUsuario']);
    if (!$stmt2->execute()) {
        die("Error al actualizar contraseña: " . $stmt2->error);
    }

    $stmt2->close();
}


    $con->CloseConnection();

    // Redireccionar con mensaje (opcional)
    header("Location: perfil.php?actualizado=1");
    exit();
}
?>
