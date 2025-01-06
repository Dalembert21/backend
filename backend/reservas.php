<?php

include_once "conexion.php";

class reservar{
    public static function verMisReservas($idUsuario) {
        $conexion = (new Conexion())->conectar();
        
        $sql = "SELECT 
        r.id_reserva,
        r.fecha_inicio,
        r.fecha_fin,
        r.estado_reserva,
        v.matricula_vehiculo,
        m.marca_vehiculo,
        mo.modelo_vehiculo,
        v.imagen_vehiculo,
        u1.nombre_ubicacion AS ubicacion_recogida,
        u2.nombre_ubicacion AS ubicacion_devolucion
    FROM reservas r
    INNER JOIN vehiculos v ON r.matricula_vehiculo = v.matricula_vehiculo
    INNER JOIN detalle_vehiculo dv ON v.matricula_vehiculo = dv.matricula_vehiculo
    INNER JOIN marcas m ON dv.id_marca_vehiculo = m.id_marca
    INNER JOIN modelos mo ON dv.id_modelo_vehiculo = mo.id_modelo
    INNER JOIN ubicacion u1 ON r.id_ubicacion_recogida = u1.id_ubicacion
    INNER JOIN ubicacion u2 ON r.id_ubicacion_entrega = u2.id_ubicacion
    WHERE r.id_usuario = :idUsuario";
        
        try {
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(":idUsuario", $idUsuario, PDO::PARAM_INT);
            $stmt->execute();
            
            $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
           
            foreach ($reservas as &$reserva) {
                $reserva['imagen_vehiculo'] = "http://localhost/app-Alquiler-Autos/fotosVehiculos/" . $reserva['imagen_vehiculo'];
                
               
                $reserva['vehiculo'] = $reserva['marca_vehiculo'] . " " . $reserva['modelo_vehiculo'];
            }
            
            return $reservas ?: null;
        } catch (PDOException $e) {
            echo "Error al obtener las reservas: " . $e->getMessage();
            return null;
        }
    }
}

?>
