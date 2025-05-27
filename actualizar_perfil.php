<?php
require_once 'MySQLConnector.php';

$con = new MysqlConnector();
$con->Connect();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

    $query = "UPDATE Clientes SET nombre=?, apellido=?, correo=?, direccion=?, colonia=?, ciudad=?, estado=?, pais=?, codigo_postal=? WHERE idCliente=?";
    $stmt = $con->PrepareStatement($query);
    $stmt->bind_param("ssssssssii", $nombre, $apellido, $correo, $direccion, $colonia, $ciudad, $estado, $pais, $codigo_postal, $idCliente);

    if ($stmt->execute()) {
        header("Location: perfil.php?actualizado=1");
        exit();
    } else {
        echo "Error al actualizar: " . $stmt->error;
    }
}
$con->CloseConnection();
// Redirigir a la página de perfil
header("Location: perfil.php");
exit();
?>
