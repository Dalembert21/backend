<?php
include_once "ConsultasCards.php"; 

header('Content-Type: application/json');

// Obtiene la acción a realizar
$action = $_GET['action'] ?? ''; 

// Valida el método HTTP
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'registrarVehiculo':
            ModeloVehiculos::registrarVehiculo();
            break;

        case 'editarVehiculo':
            ModeloVehiculos::editarVehiculo();
            break;

        case 'eliminarVehiculo':
            if (isset($_POST['matricula_vehiculo'])) {
                $matriculaVehiculo = $_POST['matricula_vehiculo'];
                ModeloVehiculos::eliminarVehiculo($matriculaVehiculo);
            } else {
                echo json_encode(['mensaje' => 'Falta la matrícula del vehículo']);
            }
            break;

        default:
            echo json_encode(['mensaje' => 'Acción no válida']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    switch ($action) {
        case 'obtenerVehiculos':
            ModeloVehiculos::obtenerVehiculos();
            break;

        case 'obtenerTiposVehiculos':
            ModeloVehiculos::obtenerTiposVehiculos();
            break;

        case 'obtenerAniosVehiculos':
            ModeloVehiculos::obtenerAniosVehiculos();
            break;

        case 'obtenerTransmisiones':
            ModeloVehiculos::obtenerTransmisiones();
            break;

        case 'obtenerCombustibles':
            ModeloVehiculos::obtenerCombustibles();
            break;

        case 'obtenerMarca':
            ModeloVehiculos::obtenerMarca();
            break;

        case 'obtenerModelo':
            ModeloVehiculos::obtenerModelo();
            break;

        case 'obtenerVehiculoPorMatricula':
            if (isset($_GET['matricula_vehiculo'])) {
                $matriculaVehiculo = $_GET['matricula_vehiculo'];
                ModeloVehiculos::obtenerVehiculoPorMatricula($matriculaVehiculo);
            } else {
                echo json_encode(['mensaje' => 'Falta la matrícula del vehículo']);
            }
            break;

        case 'filtrarVehiculos':
            if (isset($_GET['tipo'])) {
                ModeloVehiculos::filtrarPorTipo($_GET['tipo']);
            } elseif (isset($_GET['anio'])) {
                ModeloVehiculos::filtrarPorAnio($_GET['anio']);
            } elseif (isset($_GET['transmision'])) {
                ModeloVehiculos::filtrarPorTransmision($_GET['transmision']);
            } elseif (isset($_GET['combustible'])) {
                ModeloVehiculos::filtrarPorCombustible($_GET['combustible']);
            } else {
                echo json_encode(['mensaje' => 'No se ha especificado un filtro válido']);
            }
            break;

        default:
            echo json_encode(['mensaje' => 'Acción no válida']);
            break;
    }
} else {
    echo json_encode(['mensaje' => 'Método no permitido']);
}
?>
