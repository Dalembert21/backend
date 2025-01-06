<?php
include_once "conexion.php";
class ModeloAlquiler{
public static function obtenerLugar(){
    try {
        // Establecer la conexión a la base de datos
        $objetoConexion = new Conexion();
        $con = $objetoConexion->conectar();
    
        // Consulta SQL para obtener los roles
        $sql = "SELECT id_ubicacion, nombre_ubicacion FROM ubicacion";
        $stmt = $con->prepare($sql);
        $stmt->execute();
    
    
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(['mensaje' => 'Error: ' . $e->getMessage()]);
    }
}


public static function obtenerVehiculoPorMatricula($matriculaVehiculo) {
    try {
        $objetoConexion = new Conexion();
        $con = $objetoConexion->conectar();

        $sql = "SELECT 
            v.matricula_vehiculo,
            v.imagen_vehiculo,
            v.año_vehiculo,
            v.color_vehiculo,
            v.id_combustible_pertenece AS tipo_combustible,
            v.pasajeros_vehiculo,
            v.id_transmision_pertenece AS tipo_transmision,
            v.precio_vehiculo,
            v.id_tipo_vehiculo AS tipo_vehiculo,
            t.tipo_vehiculo AS tipo_vehiculo_nombre,
            d.estado_disponibilidad,
            det.id_marca_vehiculo AS marca_vehiculo,
            det.id_modelo_vehiculo AS modelo_vehiculo,
            det.caracteristicas
        FROM 
            vehiculos v
        JOIN 
            tipo_vehiculos t ON v.id_tipo_vehiculo = t.id_tipo_vehiculo
        JOIN 
            disponibilidad_vehiculo d ON v.id_disponibilidad_pertenece = d.id_disponibilidad_vehiculo
        JOIN 
            detalle_vehiculo det ON v.matricula_vehiculo = det.matricula_vehiculo
        WHERE 
            v.matricula_vehiculo = :matricula";

        $stmt = $con->prepare($sql);
        $stmt->bindParam(':matricula', $matriculaVehiculo);
        $stmt->execute();

        // Comprobamos si se encontró el vehículo
        $vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($vehiculo) {
            echo json_encode($vehiculo);
        } else {
            echo json_encode(['mensaje' => 'Vehículo no encontrado']);
        }
    } catch (PDOException $e) {
        // Se mejoró la información de error
        echo json_encode(['mensaje' => 'Error al obtener vehículo: ' . $e->getMessage()]);
    }
}


}


?>