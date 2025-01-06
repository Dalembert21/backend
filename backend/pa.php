<?php
include_once "conexion.php";

try {
            $objetoConexion = new Conexion();
            $con = $objetoConexion->conectar();
            
            $sql = "SELECT * FROM detalle_vehiculo dv
                    JOIN vehiculos v ON dv.matricula_vehiculo = v.matricula_vehiculo
                    WHERE v.id_tipo_vehiculo = :tipo";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':tipo', $tipoVehiculo, PDO::PARAM_INT);
            $stmt->execute();
            
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            echo json_encode(['mensaje' => 'Error al filtrar por tipo: ' . $e->getMessage()]);
        }