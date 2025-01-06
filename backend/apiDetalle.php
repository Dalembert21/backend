<?php
header("Content-Type: application/json");
include_once "detalles.php";

if (isset($_GET['id'])) {
    $idReserva = intval($_GET['id']);

    // Verificar si la acción es 'confirmar'
    if (isset($_GET['action']) && $_GET['action'] === 'confirmar') {
        // Verificar que todos los parámetros necesarios estén presentes
        if (isset($_GET['numeroTarjeta'], $_GET['fechaVencimiento'], $_GET['cvv'], $_GET['monto'])) {
            // Obtener los parámetros
            $numeroTarjeta = $_GET['numeroTarjeta'];
            $fechaVencimiento = $_GET['fechaVencimiento'];
            $cvv = $_GET['cvv'];
            $monto = $_GET['monto'];

            // Llamar al método confirmarReserva
            $respuesta = detalles::confirmarReserva($idReserva, $numeroTarjeta, $fechaVencimiento, $cvv, $monto);
            echo json_encode($respuesta);
        } else {
            echo json_encode(["error" => "Faltan parámetros para confirmar la reserva."]);
        }
    }
    // Verificar si la acción es 'cancelar'
    elseif (isset($_GET['action']) && $_GET['action'] === 'cancelar') {
        // Llamar al método cancelarReserva
        $respuesta = detalles::cancelarReserva($idReserva);
        echo json_encode($respuesta);
    } else {
        // Si no es la acción 'confirmar' ni 'cancelar', obtener los detalles de la reserva
        $datos = detalles::obtenerDetalles($idReserva);

        if ($datos) {
            echo json_encode($datos);
        } else {
            echo json_encode(["error" => "No se encontraron detalles para esta reserva."]);
        }
    }
} else {
    echo json_encode(["error" => "ID de reserva no proporcionado."]);
}
?>
