<?php
include_once "conexion.php";

class Modelo {

    public static function registrarReserva($id_usuario, $nombre_reservante, $cedula_reservante, $correo_reservante, $telefono_reservante, $matricula_vehiculo, $fecha_inicio, $fecha_fin, $id_impuesto, $id_ubicacion_recogida, $id_ubicacion_entrega) {
        try {
           
            $objetoConexion = new Conexion();
            $con = $objetoConexion->conectar();
    
            $fechaInicio = new DateTime($fecha_inicio);
            $fechaFin = new DateTime($fecha_fin);
            $dias_reserva = $fechaInicio->diff($fechaFin)->days;
    
            if ($dias_reserva <= 0) {
                return ["error" => "La fecha de fin debe ser posterior a la fecha de inicio"];
            }
    
          
            $queryVehiculo = "SELECT precio_vehiculo FROM vehiculos WHERE matricula_vehiculo = :matricula_vehiculo";
            $stmtVehiculo = $con->prepare($queryVehiculo);
            $stmtVehiculo->bindParam(':matricula_vehiculo', $matricula_vehiculo);
            $stmtVehiculo->execute();
            $vehiculo = $stmtVehiculo->fetch(PDO::FETCH_ASSOC);
    
            if (!$vehiculo) {
                return ["error" => "No se encontró el vehículo con la matrícula especificada"];
            }
            $precio_diario = $vehiculo['precio_vehiculo'];
    
           
            $queryImpuesto = "SELECT porcentaje_iva FROM impuestos WHERE id_impuesto = :id_impuesto";
            $stmtImpuesto = $con->prepare($queryImpuesto);
            $stmtImpuesto->bindParam(':id_impuesto', $id_impuesto);
            $stmtImpuesto->execute();
            $impuesto = $stmtImpuesto->fetch(PDO::FETCH_ASSOC);
    
            if (!$impuesto) {
                return ["error" => "No se encontró el impuesto con el ID especificado"];
            }
            $porcentaje_iva = $impuesto['porcentaje_iva'];
    
            // Calcular el precio total
            $precio_base = $precio_diario * $dias_reserva;
            $iva = $precio_base * ($porcentaje_iva / 100);
            $precio_total = $precio_base + $iva;
    
            // Insertar la reserva
            $sql = "INSERT INTO reservas (id_usuario, nombre_reservante, cedula_reservante, correo_reservante, telefono_reservante, matricula_vehiculo, fecha_inicio, fecha_fin, dias_reserva, precio, precio_total, id_impuesto, estado_reserva, id_ubicacion_recogida, id_ubicacion_entrega) 
                    VALUES (:id_usuario, :nombre_reservante, :cedula_reservante, :correo_reservante, :telefono_reservante, :matricula_vehiculo, :fecha_inicio, :fecha_fin, :dias_reserva, :precio, :precio_total, :id_impuesto, 'Pendiente', :id_ubicacion_recogida, :id_ubicacion_entrega)";
            $stmt = $con->prepare($sql);
    
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->bindParam(':nombre_reservante', $nombre_reservante);
            $stmt->bindParam(':cedula_reservante', $cedula_reservante);
            $stmt->bindParam(':correo_reservante', $correo_reservante);
            $stmt->bindParam(':telefono_reservante', $telefono_reservante);
            $stmt->bindParam(':matricula_vehiculo', $matricula_vehiculo);
            $stmt->bindParam(':fecha_inicio', $fecha_inicio);
            $stmt->bindParam(':fecha_fin', $fecha_fin);
            $stmt->bindParam(':dias_reserva', $dias_reserva);
            $stmt->bindParam(':precio', $precio_base);
            $stmt->bindParam(':precio_total', $precio_total);
            $stmt->bindParam(':id_impuesto', $id_impuesto);
            $stmt->bindParam(':id_ubicacion_recogida', $id_ubicacion_recogida);
            $stmt->bindParam(':id_ubicacion_entrega', $id_ubicacion_entrega);
    
            $stmt->execute();
    
            $id_reserva = $con->lastInsertId();
    
            if ($stmt->rowCount() > 0) {
                return ["message" => "Reserva registrada con éxito", "id_reserva" => $id_reserva];
            } else {
                return ["error" => "No se pudo registrar la reserva"];
            }
        } catch (PDOException $e) {
            return ["error" => "Error en la base de datos: " . $e->getMessage()];
        } finally {
            $con = null;
        }
    }
    
    
}

// Verificar si es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if (isset($data->id_usuario, $data->nombre_reservante, $data->cedula_reservante, $data->correo_reservante, $data->telefono_reservante, $data->matricula_vehiculo, $data->fecha_inicio, $data->fecha_fin, $data->id_impuesto, $data->id_ubicacion_recogida, $data->id_ubicacion_entrega)) {
        $response = Modelo::registrarReserva(
            $data->id_usuario,
            $data->nombre_reservante,
            $data->cedula_reservante,
            $data->correo_reservante,
            $data->telefono_reservante,
            $data->matricula_vehiculo,
            $data->fecha_inicio,
            $data->fecha_fin,
            $data->id_impuesto,
            $data->id_ubicacion_recogida,
            $data->id_ubicacion_entrega
        );

        echo json_encode($response);
    } else {
        echo json_encode(["error" => "Faltan datos necesarios"]);
    }
}

?>
