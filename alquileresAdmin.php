<?php
include_once "conexion.php";
header('Content-Type: application/json');
class modeloAdminGestionar {
    public static function obtenerReservasAdmin() {
        $objetoConexion = new Conexion();
        $conexion = $objetoConexion->conectar();
    
        $sql = "SELECT 
                    r.id_reserva,
                    r.nombre_reservante,
                    r.cedula_reservante,
                    r.correo_reservante,
                    r.telefono_reservante,
                    r.fecha_inicio,
                    r.fecha_fin,
                    r.precio_total,
                    r.estado_reserva,
                    p.id_estado_pago,
                    ep.estado AS estado_pago,
                    v.precio_vehiculo  -- Aquí obtenemos el precio del vehículo
                FROM reservas r
                JOIN pagos p ON r.id_reserva = p.id_reserva
                JOIN estado_pago ep ON p.id_estado_pago = ep.id_estado_pago
                JOIN vehiculos v ON r.matricula_vehiculo = v.matricula_vehiculo"; 
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        return $result;
    }
    public static function actualizarEstadoReserva($id_reserva, $nuevo_estado_pago = null, $nuevo_estado_reserva = null) {
        $objetoConexion = new Conexion();
        $conexion = $objetoConexion->conectar();
        try {
            // Actualizar el estado de pago en la tabla pagos (si se envió el parámetro)
            if ($nuevo_estado_pago !== null) {
                $sqlPago = "UPDATE pagos 
                            SET id_estado_pago = :nuevo_estado_pago 
                            WHERE id_reserva = :id_reserva";
                $stmtPago = $conexion->prepare($sqlPago);
                $stmtPago->bindParam(":id_reserva", $id_reserva, PDO::PARAM_INT);
                $stmtPago->bindParam(":nuevo_estado_pago", $nuevo_estado_pago, PDO::PARAM_INT);
                $stmtPago->execute();
            }
            // Actualizar el estado de la reserva (si se envió el parámetro)
            if ($nuevo_estado_reserva !== null) {
                $sqlReserva = "UPDATE reservas 
                               SET estado_reserva = :nuevo_estado_reserva 
                               WHERE id_reserva = :id_reserva";
                $stmtReserva = $conexion->prepare($sqlReserva);
                $stmtReserva->bindParam(":id_reserva", $id_reserva, PDO::PARAM_INT);
                $stmtReserva->bindParam(":nuevo_estado_reserva", $nuevo_estado_reserva, PDO::PARAM_STR);
                $stmtReserva->execute();
                
                // Si la reserva fue cancelada, cambiar el estado de disponibilidad del vehículo a "Mantenimiento" (valor 3)
                if ($nuevo_estado_reserva === 'Cancelada') {
                    // Obtener el id_disponibilidad_pertenece del vehículo asociado a la reserva
                    $sqlVehiculo = "SELECT v.id_disponibilidad_pertenece 
                                    FROM vehiculos v 
                                    JOIN reservas r ON v.matricula_vehiculo = r.matricula_vehiculo
                                    WHERE r.id_reserva = :id_reserva";
                    $stmtVehiculo = $conexion->prepare($sqlVehiculo);
                    $stmtVehiculo->bindParam(":id_reserva", $id_reserva, PDO::PARAM_INT);
                    $stmtVehiculo->execute();
                    $vehiculo = $stmtVehiculo->fetch(PDO::FETCH_ASSOC);

                    if ($vehiculo) {
                        // Actualizar el estado de disponibilidad del vehículo a "Mantenimiento" (valor 3)
                        $sqlEstadoDisponibilidad = "UPDATE disponibilidad_vehiculo 
                                                    SET estado_disponibilidad = 3 
                                                    WHERE id_disponibilidad_vehiculo = :id_disponibilidad_vehiculo";
                        $stmtEstadoDisponibilidad = $conexion->prepare($sqlEstadoDisponibilidad);
                        $stmtEstadoDisponibilidad->bindParam(":id_disponibilidad_vehiculo", $vehiculo['id_disponibilidad_pertenece'], PDO::PARAM_INT);
                        $stmtEstadoDisponibilidad->execute();
                    }
                }    
                // Si la reserva fue confirmada, cambiar el estado de disponibilidad del vehículo a "No Disponible" (valor 2)
                if ($nuevo_estado_reserva === 'Confirmada') {
                    // Obtener el id_disponibilidad_pertenece del vehículo asociado a la reserva
                    $sqlVehiculo = "SELECT v.id_disponibilidad_pertenece 
                                    FROM vehiculos v 
                                    JOIN reservas r ON v.matricula_vehiculo = r.matricula_vehiculo
                                    WHERE r.id_reserva = :id_reserva";
                    $stmtVehiculo = $conexion->prepare($sqlVehiculo);
                    $stmtVehiculo->bindParam(":id_reserva", $id_reserva, PDO::PARAM_INT);
                    $stmtVehiculo->execute();
                    $vehiculo = $stmtVehiculo->fetch(PDO::FETCH_ASSOC);
    
                    if ($vehiculo) {
                        // Actualizar el estado de disponibilidad del vehículo a "No Disponible" (valor 2)
                        $sqlEstadoDisponibilidad = "UPDATE disponibilidad_vehiculo 
                                                    SET estado_disponibilidad = 2 
                                                    WHERE id_disponibilidad_vehiculo = :id_disponibilidad_vehiculo";
                        $stmtEstadoDisponibilidad = $conexion->prepare($sqlEstadoDisponibilidad);
                        $stmtEstadoDisponibilidad->bindParam(":id_disponibilidad_vehiculo", $vehiculo['id_disponibilidad_pertenece'], PDO::PARAM_INT);
                        $stmtEstadoDisponibilidad->execute();
                    }
                }
            }
            // Si alguna actualización fue exitosa, devolver true
            return true;
        } catch (Exception $e) {
            // Captura y muestra el error SQL
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    public static function eliminarReserva($id_reserva) {
        $objetoConexion = new Conexion();
        $conexion = $objetoConexion->conectar();    
        try {
            // Eliminar los pagos asociados a la reserva
            $sqlPagos = "DELETE FROM pagos WHERE id_reserva = :id_reserva";
            $stmtPagos = $conexion->prepare($sqlPagos);
            $stmtPagos->bindParam(":id_reserva", $id_reserva, PDO::PARAM_INT);
            $stmtPagos->execute();
            
            // Eliminar la reserva de la tabla de reservas
            $sqlReserva = "DELETE FROM reservas WHERE id_reserva = :id_reserva";
            $stmtReserva = $conexion->prepare($sqlReserva);
            $stmtReserva->bindParam(":id_reserva", $id_reserva, PDO::PARAM_INT);
            $stmtReserva->execute();
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    public static function obtenerIdReserva($id_reserva){
        $objetoConexion = new Conexion();
        $conexion = $objetoConexion->conectar();
        try {
            // Consulta SQL para obtener solo el id_reserva
            $sql = "SELECT id_reserva 
                    FROM reservas 
                    WHERE id_reserva = :id_reserva";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(":id_reserva", $id_reserva, PDO::PARAM_INT);
            $stmt->execute();
            // Obtener el resultado de la consulta
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar si se encontró el id_reserva
            if ($resultado) {
                return $resultado['id_reserva']; // Devuelve solo el id_reserva
            } else {
                return null; // No se encontró la reserva
            }
        } catch (Exception $e) {
            // Manejar el error si ocurre
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    public static function insertarDevolucion($id_reserva, $fecha_devolucion, $estado_tanque, $estado_limpieza, $estado_danio, $cargos_adicionales_retraso, $cargos_adicionales, $total_pagar, $observaciones, $estado_devolucion = 'Por Cancelar') {
        $objetoConexion = new Conexion();
        $conexion = $objetoConexion->conectar();
    
        try {
            // Insertar la devolución
            $sql = "INSERT INTO devolucion (
                        id_reserva, 
                        fecha_devolucion, 
                        estado_tanque, 
                        estado_limpieza, 
                        estado_danio, 
                        cargos_adicionales_retraso, 
                        cargos_adicionales, 
                        total_pagar, 
                        observaciones, 
                        estado_devolucion
                    ) VALUES (
                        :id_reserva, 
                        :fecha_devolucion, 
                        :estado_tanque, 
                        :estado_limpieza, 
                        :estado_danio, 
                        :cargos_adicionales_retraso, 
                        :cargos_adicionales, 
                        :total_pagar, 
                        :observaciones, 
                        :estado_devolucion
                    )";
    
            $stmt = $conexion->prepare($sql);
    
            // Vincular los parámetros
            $stmt->bindParam(':id_reserva', $id_reserva, PDO::PARAM_INT);
            $stmt->bindParam(':fecha_devolucion', $fecha_devolucion, PDO::PARAM_STR);
            $stmt->bindParam(':estado_tanque', $estado_tanque, PDO::PARAM_INT);
            $stmt->bindParam(':estado_limpieza', $estado_limpieza, PDO::PARAM_INT);
            $stmt->bindParam(':estado_danio', $estado_danio, PDO::PARAM_INT);
            $stmt->bindParam(':cargos_adicionales_retraso', $cargos_adicionales_retraso, PDO::PARAM_STR);
            $stmt->bindParam(':cargos_adicionales', $cargos_adicionales, PDO::PARAM_STR);
            $stmt->bindParam(':total_pagar', $total_pagar, PDO::PARAM_STR);
            $stmt->bindParam(':observaciones', $observaciones, PDO::PARAM_STR);
            $stmt->bindParam(':estado_devolucion', $estado_devolucion, PDO::PARAM_STR);
    
            // Ejecutar la consulta
            $stmt->execute();
    
            // Si el estado de la devolución es 'Procesado', actualizar el estado de la reserva a 'Cancelada'
            if ($estado_devolucion === 'Procesado') {
                // Actualizar el estado de la reserva
                $sqlReserva = "UPDATE reservas 
                               SET estado_reserva = 'Cancelada' 
                               WHERE id_reserva = :id_reserva";
                $stmtReserva = $conexion->prepare($sqlReserva);
                $stmtReserva->bindParam(":id_reserva", $id_reserva, PDO::PARAM_INT);
                $stmtReserva->execute();
            }
    
            return true; // Inserción exitosa
        } catch (Exception $e) {
            // Captura y muestra el error SQL
            echo "Error al insertar la devolución: " . $e->getMessage();
            return false; // Error al insertar
        }
    }
    

    public static function obtenerVehiculosEnMantenimiento() {
        try {
            $objetoConexion = new Conexion();
            $con = $objetoConexion->conectar();
    
            $sql = "SELECT 
                dv.id_detalle,
                dv.matricula_vehiculo,
                v.imagen_vehiculo,
                v.año_vehiculo,
                v.color_vehiculo,
                v.pasajeros_vehiculo,
                v.precio_vehiculo,
                m.marca_vehiculo,
                mo.modelo_vehiculo,
                c.tipo_combustible,
                t.tipo_transmision,
                tv.tipo_vehiculo, -- Tipo de vehículo
                dv.caracteristicas
            FROM 
                detalle_vehiculo dv
            JOIN 
                vehiculos v ON dv.matricula_vehiculo = v.matricula_vehiculo
            JOIN 
                marcas m ON dv.id_marca_vehiculo = m.id_marca
            JOIN 
                modelos mo ON dv.id_modelo_vehiculo = mo.id_modelo
            JOIN 
                combustible c ON v.id_combustible_pertenece = c.id_combustible
            JOIN 
                transmision t ON v.id_transmision_pertenece = t.id_transmision
            JOIN 
                tipo_vehiculos tv ON v.id_tipo_vehiculo = tv.id_tipo_vehiculo
            JOIN 
                disponibilidad_vehiculo d ON v.id_disponibilidad_pertenece = d.id_disponibilidad_vehiculo
            WHERE 
                d.estado_disponibilidad = 'Mantenimiento';";
    
            $stmt = $con->prepare($sql);
            $stmt->execute();
    
            $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($vehiculos);
        } catch (PDOException $e) {
            echo json_encode(['mensaje' => 'Error al obtener vehículos: ' . $e->getMessage()]);
        }
    }

    public static function obtenerTalleres(){
        $objetoConexion = new Conexion();
        $con = $objetoConexion->conectar();
        $sql = "SELECT id_taller, nombre_taller FROM talleres";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

  
    public static function registrarMantenimiento($matriculaVehiculo, $kilometrajeActual, $fechaIngreso, $costoEstimado, $fechaFinalizacion, $descripcionMantenimiento, $idTaller, $mantenimientoBasico, $revisionAdicional){
        $objetoConexion = new Conexion();
        $con = $objetoConexion->conectar();
        
        // Iniciar una transacción para asegurarse de que ambas operaciones se realicen de manera atómica
        $con->beginTransaction();
        
        try {
            // Sentencia SQL para insertar el mantenimiento
            $sql = "INSERT INTO mantenimiento (matricula_vehiculo, kilometraje_actual, fecha_ingreso, costo_estimado, fecha_finalizacion, descripcion_mantenimiento, id_taller, mantenimiento_basico, revision_adicional) 
                    VALUES (:matricula_vehiculo, :kilometraje_actual, :fecha_ingreso, :costo_estimado, :fecha_finalizacion, :descripcion_mantenimiento, :id_taller, :mantenimiento_basico, :revision_adicional)";
            
            // Preparar la sentencia para insertar el mantenimiento
            $stmt = $con->prepare($sql);
            
            // Vincular los parámetros
            $stmt->bindParam(':matricula_vehiculo', $matriculaVehiculo);
            $stmt->bindParam(':kilometraje_actual', $kilometrajeActual);
            $stmt->bindParam(':fecha_ingreso', $fechaIngreso);
            $stmt->bindParam(':costo_estimado', $costoEstimado);
            $stmt->bindParam(':fecha_finalizacion', $fechaFinalizacion);
            $stmt->bindParam(':descripcion_mantenimiento', $descripcionMantenimiento);
            $stmt->bindParam(':id_taller', $idTaller);
            $stmt->bindValue(':mantenimiento_basico', $mantenimientoBasico, PDO::PARAM_STR);
            $stmt->bindValue(':revision_adicional', $revisionAdicional, PDO::PARAM_STR);
            
            // Ejecutar la sentencia para insertar el mantenimiento
            $stmt->execute();
            
            // Sentencia SQL para actualizar la disponibilidad del vehículo
            $sqlUpdate = "UPDATE vehiculos 
                          SET id_disponibilidad_pertenece = (SELECT id_disponibilidad_vehiculo FROM disponibilidad_vehiculo WHERE estado_disponibilidad = 'Disponible' LIMIT 1)
                          WHERE matricula_vehiculo = :matricula_vehiculo";
            
            // Preparar la sentencia para actualizar la disponibilidad
            $stmtUpdate = $con->prepare($sqlUpdate);
            $stmtUpdate->bindParam(':matricula_vehiculo', $matriculaVehiculo);
            
            // Ejecutar la sentencia para actualizar la disponibilidad
            $stmtUpdate->execute();
            
            // Confirmar la transacción
            $con->commit();
            
            return "Mantenimiento registrado y vehículo disponible con éxito.";
        } catch (Exception $e) {
            // Si ocurre un error, revertir la transacción
            $con->rollBack();
            return "Error al registrar el mantenimientoOOOOOOO: " . $e->getMessage();
        }
    }
    
    
   
}
?>
