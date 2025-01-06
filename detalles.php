<?php
include_once "conexion.php";

class detalles {
    
    public static function obtenerDetalles($idReserva) {
        $conexion = (new Conexion())->conectar();
    
        $sql = "SELECT 
        r.id_reserva,
        r.nombre_reservante,
        r.cedula_reservante,
        r.correo_reservante,
        r.telefono_reservante,
        r.fecha_inicio,
        r.fecha_fin,
        r.estado_reserva,
        r.precio_total, 
        r.dias_reserva, 
        v.matricula_vehiculo,
        v.año_vehiculo,
        v.precio_vehiculo,
        m.marca_vehiculo,
        mo.modelo_vehiculo,
        t.tipo_transmision,
        u1.nombre_ubicacion AS ubicacion_recogida,
        u2.nombre_ubicacion AS ubicacion_devolucion,
        c.tipo_combustible,
        v.imagen_vehiculo
    FROM reservas r
    INNER JOIN vehiculos v ON r.matricula_vehiculo = v.matricula_vehiculo
    INNER JOIN marcas m ON v.id_combustible_pertenece = m.id_marca
    INNER JOIN modelos mo ON v.id_tipo_vehiculo = mo.id_modelo
    INNER JOIN transmision t ON v.id_transmision_pertenece = t.id_transmision
    INNER JOIN ubicacion u1 ON r.id_ubicacion_recogida = u1.id_ubicacion
    INNER JOIN ubicacion u2 ON r.id_ubicacion_entrega = u2.id_ubicacion
    INNER JOIN combustible c ON v.id_combustible_pertenece = c.id_combustible
    WHERE r.id_reserva = :idReserva";
    
        try {
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(":idReserva", $idReserva, PDO::PARAM_INT);
            $stmt->execute();
    
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($resultado) {
                $resultado['imagen_vehiculo'] = "http://localhost/app-Alquiler-Autos/fotosVehiculos/" . $resultado['imagen_vehiculo'];
            }
    
            return $resultado ?: null;
        } catch (PDOException $e) {
            echo "Error al obtener detalles: " . $e->getMessage();
            return null;
        }
    }

    public static function cancelarReserva($idReserva) {
        $conexion = (new Conexion())->conectar();
    
        $sql = "DELETE FROM reservas WHERE id_reserva = :idReserva";
    
        try {
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(":idReserva", $idReserva, PDO::PARAM_INT);
            $stmt->execute();
    
            return ["success" => "Reserva cancelada con éxito"];
        } catch (PDOException $e) {
            return ["error" => "Error al cancelar la reserva: " . $e->getMessage()];
        }
    }

    public static function confirmarReserva($idReserva, $numeroTarjeta, $fechaVencimiento, $cvv, $monto) {
        $conexion = (new Conexion())->conectar();
    
        // Asignar valores predeterminados
        $idMetodoPago = 1;  // Tarjeta
        $idEstadoPago = 2;  // Pendiente
        $estadoNoDisponible = 4; 
    
        // Iniciar la transacción
        $conexion->beginTransaction();
    
        try {
            // Primero, actualizamos el estado de la reserva a "Pendiente" (estado_reserva = 1)
            $sqlActualizarReserva = "UPDATE reservas SET estado_reserva = 1 WHERE id_reserva = :idReserva";
            $stmtActualizarReserva = $conexion->prepare($sqlActualizarReserva);
            $stmtActualizarReserva->bindParam(":idReserva", $idReserva, PDO::PARAM_INT);
            $stmtActualizarReserva->execute();
    
            // Luego, insertamos la información de pago en la tabla de pagos
            $sqlInsertarPago = "INSERT INTO pagos (id_reserva, monto_pago, id_metodo_pago, id_estado_pago, fecha_vencimiento, numero_tarjeta, cvv) 
                                VALUES (:idReserva, :monto, :idMetodoPago, :idEstadoPago, :fechaVencimiento, :numeroTarjeta, :cvv)";
            $stmtInsertarPago = $conexion->prepare($sqlInsertarPago);
            $stmtInsertarPago->bindParam(":idReserva", $idReserva, PDO::PARAM_INT);
            $stmtInsertarPago->bindParam(":monto", $monto, PDO::PARAM_STR);
            $stmtInsertarPago->bindParam(":idMetodoPago", $idMetodoPago, PDO::PARAM_INT);
            $stmtInsertarPago->bindParam(":idEstadoPago", $idEstadoPago, PDO::PARAM_INT);
            $stmtInsertarPago->bindParam(":fechaVencimiento", $fechaVencimiento, PDO::PARAM_STR);
            $stmtInsertarPago->bindParam(":numeroTarjeta", $numeroTarjeta, PDO::PARAM_STR);
            $stmtInsertarPago->bindParam(":cvv", $cvv, PDO::PARAM_STR);
            $stmtInsertarPago->execute();
    
            // Cambiamos el estado del vehículo a "No Disponible"
            $sqlActualizarVehiculo = "UPDATE vehiculos v
                                      INNER JOIN reservas r ON v.matricula_vehiculo = r.matricula_vehiculo
                                      SET v.id_disponibilidad_pertenece = :estadoNoDisponible
                                      WHERE r.id_reserva = :idReserva";
            $stmtActualizarVehiculo = $conexion->prepare($sqlActualizarVehiculo);
            $stmtActualizarVehiculo->bindParam(":estadoNoDisponible", $estadoNoDisponible, PDO::PARAM_INT);
            $stmtActualizarVehiculo->bindParam(":idReserva", $idReserva, PDO::PARAM_INT);
            $stmtActualizarVehiculo->execute();
    
            // Confirmamos la transacción
            $conexion->commit();
    
            return ["success" => "Reserva en proceso de pago"];
        } catch (PDOException $e) {
            // Si ocurre un error, revertimos la transacción
            $conexion->rollBack();
            return ["error" => "Error al confirmar la reserva: " . $e->getMessage()];
        }
    }
    
    

    public static function verMisReservas($idUsuario) {
        $conexion = (new Conexion())->conectar();
        
        // Consulta para obtener las reservas del usuario
        $sql = "SELECT 
                    r.id_reserva,
                    r.fecha_inicio,
                    r.fecha_fin,
                    r.estado_reserva,
                    v.matricula_vehiculo,
                    v.marca_vehiculo,
                    v.modelo_vehiculo,
                    v.imagen_vehiculo,
                    u1.nombre_ubicacion AS ubicacion_recogida,
                    u2.nombre_ubicacion AS ubicacion_devolucion
                FROM reservas r
                INNER JOIN vehiculos v ON r.matricula_vehiculo = v.matricula_vehiculo
                INNER JOIN ubicacion u1 ON r.id_ubicacion_recogida = u1.id_ubicacion
                INNER JOIN ubicacion u2 ON r.id_ubicacion_entrega = u2.id_ubicacion
                WHERE r.id_usuario = :idUsuario";  // Filtrar por el ID del usuario
        
        try {
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(":idUsuario", $idUsuario, PDO::PARAM_INT);
            $stmt->execute();
            
            $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Modificar la ruta de la imagen del vehículo
            foreach ($reservas as &$reserva) {
                $reserva['imagen_vehiculo'] = "http://localhost/app-Alquiler-Autos/fotosVehiculos/" . $reserva['imagen_vehiculo'];
            }
            
            return $reservas ?: null;
        } catch (PDOException $e) {
            echo "Error al obtener las reservas: " . $e->getMessage();
            return null;
        }
    }
    
    
    
    
    
}
?>