<?php

include_once "reservas.php";

// Verificar si se ha enviado el parámetro idUsuario
if (isset($_GET['idUsuario'])) {
    $idUsuario = $_GET['idUsuario'];  // Obtener el idUsuario desde la URL
    
    // Llamar al método verMisReservas
    $reservas = reservar::verMisReservas($idUsuario);
    
    // Verificar si se obtuvieron reservas
    if ($reservas) {
        // Enviar respuesta en formato JSON
        echo json_encode($reservas);
    } else {
        // Si no hay reservas, enviar mensaje
        echo json_encode(["message" => "No tienes reservas"]);
    }
} else {
    echo json_encode(["error" => "Falta el parámetro idUsuario"]);
}
?>
