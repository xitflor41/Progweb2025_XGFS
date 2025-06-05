<?php
session_start();
require_once 'MySQLConnector.php';
require('fpdf/fpdf.php'); // Asegúrate que la ruta sea correcta

// Verificar si es administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

class PDF extends FPDF
{
    // Encabezado
    function Header()
    {
        $this->SetFont('Arial','B',14);
        $this->Cell(0,10,'Reporte de Ventas',0,1,'C');
        $this->Ln(5);
    }

    // Pie de página
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

try {
    $db = new MySQLConnector();
    $db->Connect();

    $query = "
        SELECT 
            V.folio,
            V.fecha,
            C.nombre AS nombreCliente,
            C.apellido AS apellidoCliente,
            A.descripcion AS descripcionArticulo,
            A.precio,
            T.nombre_sucursal AS tienda,
            T.ciudad
        FROM Ventas V
        INNER JOIN Clientes C ON V.idCliente = C.idCliente
        INNER JOIN Articulos A ON V.idArticulo = A.idArticulo
        INNER JOIN Tiendas T ON V.idTienda = T.idTienda
        ORDER BY V.fecha DESC
    ";

    $result = $db->ExecuteQuery($query);
    $ventas = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $db->CloseConnection();
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','B',10);

// Encabezados de tabla
$pdf->Cell(20,10,'ID',1);
$pdf->Cell(30,10,'Fecha',1);
$pdf->Cell(40,10,'Cliente',1);
$pdf->Cell(50,10,'Articulo',1);
$pdf->Cell(25,10,'Precio',1);
$pdf->Cell(25,10,'Tienda',1);
$pdf->Ln();

$pdf->SetFont('Arial','',9);

// Datos
foreach ($ventas as $venta) {
    $pdf->Cell(20,8,$venta['folio'],1);
    $pdf->Cell(30,8,$venta['fecha'],1);
    $pdf->Cell(40,8,$venta['nombreCliente'] . ' ' . $venta['apellidoCliente'],1);
    $pdf->Cell(50,8,$venta['descripcionArticulo'],1);
    $pdf->Cell(25,8,'$' . number_format($venta['precio'], 2),1);
    $pdf->Cell(25,8,$venta['tienda'],1);
    $pdf->Ln();
}

$pdf->Output('I', 'Ventas.pdf'); // "I" para mostrar en el navegador
?>
