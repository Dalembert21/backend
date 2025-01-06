<?php
include_once "consultasAlquiler.php";
header('Content-Type: application/json');

$action = $_GET['action'] ?? ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        default:
            echo json_encode(['mensaje' => 'Acción no válida']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    switch ($action) {
        case 'obtenerLugar':
            ModeloAlquiler::obtenerLugar();
            break;

            case 'obtenerVehiculoPorMatricula':
               
                if (isset($_GET['matricula_vehiculo'])) {
                    $matriculaVehiculo = $_GET['matricula_vehiculo'];
                    ModeloVehiculos::obtenerVehiculoPorMatricula($matriculaVehiculo);
                } else {
                    echo json_encode(['mensaje' => 'Falta la matrícula del vehículo']);
                }
                break;

        default:
            echo json_encode(['mensaje' => 'Acción no válida']);
    }
} else {
    echo json_encode(['mensaje' => 'Método no permitido']);
}
?>
