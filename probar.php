<?php
include_once "conexion.php";

class Modelo {

    public static function registrarReserva($id_usuario, $nombre_reservante, $cedula_reservante, $correo_reservante, $telefono_reservante, $matricula_vehiculo, $fecha_inicio, $fecha_fin, $id_impuesto, $id_ubicacion_recogida, $id_ubicacion_entrega) {
        try {
            // Crear una instancia de la conexión
            $objetoConexion = new Conexion();
            $con = $objetoConexion->conectar();
            
            // Consultar el precio del vehículo basado en la matrícula
            $queryVehiculo = "SELECT precio_vehiculo FROM vehiculos WHERE matricula_vehiculo = :matricula_vehiculo";
            $stmtVehiculo = $con->prepare($queryVehiculo);
            $stmtVehiculo->bindParam(':matricula_vehiculo', $matricula_vehiculo);
            $stmtVehiculo->execute();
            $vehiculo = $stmtVehiculo->fetch(PDO::FETCH_ASSOC);
            
            // Verificar si se encontró el vehículo
            if (!$vehiculo) {
                return ["error" => "No se encontró el vehículo con la matrícula especificada"];
            }
            
            // Obtener el precio del vehículo
            $precio = $vehiculo['precio_vehiculo'];
            
            // Definir la consulta SQL para insertar la reserva
            $sql = "INSERT INTO reservas (id_usuario, nombre_reservante, cedula_reservante, correo_reservante, telefono_reservante, matricula_vehiculo, fecha_inicio, fecha_fin, precio, id_impuesto, estado_reserva, id_ubicacion_recogida, id_ubicacion_entrega) 
                    VALUES (:id_usuario, :nombre_reservante, :cedula_reservante, :correo_reservante, :telefono_reservante, :matricula_vehiculo, :fecha_inicio, :fecha_fin, :precio, :id_impuesto, 'Pendiente', :id_ubicacion_recogida, :id_ubicacion_entrega)";
            
            // Preparar la consulta
            $stmt = $con->prepare($sql);
            
            // Vincular los parámetros con los valores
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->bindParam(':nombre_reservante', $nombre_reservante);
            $stmt->bindParam(':cedula_reservante', $cedula_reservante);
            $stmt->bindParam(':correo_reservante', $correo_reservante);
            $stmt->bindParam(':telefono_reservante', $telefono_reservante);
            $stmt->bindParam(':matricula_vehiculo', $matricula_vehiculo);
            $stmt->bindParam(':fecha_inicio', $fecha_inicio);
            $stmt->bindParam(':fecha_fin', $fecha_fin);
            $stmt->bindParam(':precio', $precio);
            $stmt->bindParam(':id_impuesto', $id_impuesto);
            $stmt->bindParam(':id_ubicacion_recogida', $id_ubicacion_recogida);
            $stmt->bindParam(':id_ubicacion_entrega', $id_ubicacion_entrega);
            
            // Ejecutar la consulta
            $stmt->execute();
            
            // Verificar si la reserva se registró correctamente
            if ($stmt->rowCount() > 0) {
                return ["message" => "Reserva registrada con éxito"];
            } else {
                return ["error" => "No se pudo registrar la reserva"];
            }
        } catch (PDOException $e) {
            // Manejar error de conexión o consulta
            return ["error" => "Error en la base de datos: " . $e->getMessage()];
        } finally {
            // Cerrar la conexión
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
