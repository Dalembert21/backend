<?php
include_once "conexion.php";

header('Content-Type: application/json'); 

class ModeloVehiculos {

    public static function obtenerVehiculos() {
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
                d.estado_disponibilidad = 'Disponible';";
    
            $stmt = $con->prepare($sql);
            $stmt->execute();
    
            $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($vehiculos);
        } catch (PDOException $e) {
            echo json_encode(['mensaje' => 'Error al obtener vehículos: ' . $e->getMessage()]);
        }
    }
    

    public static function obtenerTiposVehiculos() {
        $objetoConexion = new Conexion();
        $con = $objetoConexion->conectar();
        // Modificamos la consulta para obtener el id_tipo_vehiculo y el nombre tipo_vehiculo
        $sql = "SELECT id_tipo_vehiculo, tipo_vehiculo FROM tipo_vehiculos";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // Similar para obtener los años de vehículos
    public static function obtenerAniosVehiculos() {
        $objetoConexion = new Conexion();
        $con = $objetoConexion->conectar();
        $sql = "SELECT DISTINCT año_vehiculo FROM vehiculos ORDER BY año_vehiculo DESC";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // Similar para obtener transmisiones
    public static function obtenerTransmisiones() {
        $objetoConexion = new Conexion();
        $con = $objetoConexion->conectar();
        $sql = "SELECT id_transmision, tipo_transmision FROM transmision";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // Similar para obtener combustibles
    public static function obtenerCombustibles() {
        $objetoConexion = new Conexion();
        $con = $objetoConexion->conectar();
        $sql = "SELECT id_combustible, tipo_combustible FROM combustible";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    public static function obtenerMarca() {
        $objetoConexion = new Conexion();
        $con = $objetoConexion->conectar();
        $sql = "SELECT *FROM marcas"; 
        $stmt = $con->prepare($sql);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)); // Devolver tanto id_marca_vehiculo como marca_vehiculo
    }
    public static function obtenerModelo() {
      // Crear la conexión
$objetoConexion = new Conexion();
$con = $objetoConexion->conectar();

try {
    // Verificar si se ha recibido el parámetro id_marca
    if (isset($_GET['id_marca'])) {
        $id_marca = intval($_GET['id_marca']); // Asegurarse de que sea un entero

        // Consulta para obtener los modelos filtrados por la marca seleccionada
        $sql = "SELECT id_modelo, modelo_vehiculo FROM modelos WHERE id_marca = :id_marca";
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':id_marca', $id_marca, PDO::PARAM_INT); // Enlazar el parámetro
        $stmt->execute();

        // Obtener los resultados y devolverlos en formato JSON
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } else {
        // Si no se recibe el parámetro, devolver un mensaje de error
        echo json_encode(['error' => 'No se ha proporcionado el id_marca']);
    }
} catch (PDOException $e) {
    // Manejo de errores
    echo json_encode(['error' => 'Error en la consulta: ' . $e->getMessage()]);
}
    }
    
    public static function eliminarVehiculo($matriculaVehiculo) {
        try {
            $objetoConexion = new Conexion();
            $con = $objetoConexion->conectar();
    
            // Iniciar la transacción para asegurar que ambas eliminaciones se hagan correctamente
            $con->beginTransaction();
    
            // Eliminar el detalle del vehículo en la tabla 'detalle_vehiculo'
            $sqlDetalle = "DELETE FROM detalle_vehiculo WHERE matricula_vehiculo = ?";
            $stmtDetalle = $con->prepare($sqlDetalle);
            $stmtDetalle->bindParam(1, $matriculaVehiculo);  // Asegúrate de usar el índice 1
            $stmtDetalle->execute();
    
            // Eliminar el vehículo en la tabla 'vehiculos'
            $sqlVehiculo = "DELETE FROM vehiculos WHERE matricula_vehiculo = ?";
            $stmtVehiculo = $con->prepare($sqlVehiculo);
            $stmtVehiculo->bindParam(1, $matriculaVehiculo);  // Asegúrate de usar el índice 1
            $stmtVehiculo->execute();
    
            // Confirmar la transacción
            $con->commit();
    
            // Responder con éxito
            echo json_encode(['mensaje' => 'Vehículo eliminado exitosamente']);
        } catch (PDOException $e) {
            // En caso de error, hacer rollback de la transacción
            $con->rollBack();
            echo json_encode(['mensaje' => 'Error al eliminar vehículo: ' . $e->getMessage()]);
        }
    }
    public static function registrarVehiculo() {
        // Comprobar si la solicitud es de tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Crear conexión a la base de datos
        $objetoConexion = new Conexion();
        $con = $objetoConexion->conectar();
        
        // Obtener los datos enviados en la solicitud
        $matriculaVehiculo = $_POST['matriculaVehiculo'];
      
        $marcaVehiculo = $_POST['marcaVehiculo'];
        $modeloVehiculo = $_POST['modeloVehiculo'];
        $anioVehiculo = $_POST['anioVehiculo'];
        $colorVehiculo = $_POST['colorVehiculo'];
        $combustibleVehiculo = $_POST['combustibleVehiculo'];
        $pasajerosVehiculo = $_POST['pasajerosVehiculo'];
        $transmisionVehiculo = $_POST['transmisionVehiculo'];
        $precioVehiculo = $_POST['precioVehiculo'];
        $tipoVehiculo = $_POST['tipoVehiculo'];
        $caracteristicas = $_POST['caracteristicas'];
        $disponibilidadVehiculo = 1; // Asumiendo que la disponibilidad es 1 por defecto
        
        // Validación: Verificar si el id_tipo_vehiculo existe en la tabla tipo_vehiculos
        $sqlVerificarTipo = "SELECT COUNT(*) FROM tipo_vehiculos WHERE id_tipo_vehiculo = :tipo";
        $stmtVerificarTipo = $con->prepare($sqlVerificarTipo);
        $stmtVerificarTipo->bindParam(':tipo', $tipoVehiculo);
        $stmtVerificarTipo->execute();
        $existeTipo = $stmtVerificarTipo->fetchColumn();

        if ($existeTipo == 0) {
            // Si el tipo de vehículo no existe, devolver un mensaje de error
            echo json_encode(['mensaje' => 'El tipo de vehículo no existe']);
            return; // Salir de la función para evitar el registro
        }

        // Comprobar si la imagen fue subida correctamente
        if (isset($_FILES['imagen_vehiculo']) && $_FILES['imagen_vehiculo']['error'] == 0) {
            // Obtener la extensión de la imagen
            $imageFileType = strtolower(pathinfo($_FILES['imagen_vehiculo']['name'], PATHINFO_EXTENSION));

            // Verificar si la extensión es una de las permitidas
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'jfif'];
            if (!in_array($imageFileType, $allowedExtensions)) {
                echo json_encode(['mensaje' => 'Formato de imagen no permitido.']);
                return;
            }

            // Generar un nombre único para la imagen
            $imageName = uniqid('vehiculo_') . '.' . $imageFileType;

            // Definir la carpeta de destino donde se guardarán las imágenes
            $targetDirectory = "../fotosVehiculos/";

            // Mover el archivo de imagen a la carpeta de destino
            $targetFile = $targetDirectory . $imageName;
            move_uploaded_file($_FILES['imagen_vehiculo']['tmp_name'], $targetFile);

            // Solo se guarda el nombre de la imagen (y no su contenido)
            $imagenVehiculo = $imageName;
        } else {
            // Si no se sube una imagen, manejarlo según lo necesites
            throw new Exception('Error al subir la imagen.');
        }

        // Iniciar la transacción para asegurar que ambas inserciones se hagan correctamente
        $con->beginTransaction();

        // Insertar el vehículo en la tabla 'vehiculos'
        $sqlVehiculo = "INSERT INTO vehiculos (
            matricula_vehiculo,  
           
            imagen_vehiculo, 
            año_vehiculo, 
            color_vehiculo, 
            id_combustible_pertenece, 
            pasajeros_vehiculo, 
            id_transmision_pertenece, 
            precio_vehiculo, 
            id_tipo_vehiculo, 
            id_disponibilidad_pertenece
        ) VALUES (
            :matricula, 
            
            :imagen, 
            :anio, 
            :color, 
            :combustible, 
            :pasajeros, 
            :transmision, 
            :precio, 
            :tipo,  
            :disponibilidad
        )";

        $stmtVehiculo = $con->prepare($sqlVehiculo);
        $stmtVehiculo->bindParam(':matricula', $matriculaVehiculo);  
        
        $stmtVehiculo->bindParam(':imagen', $imagenVehiculo); 
        $stmtVehiculo->bindParam(':anio', $anioVehiculo);
        $stmtVehiculo->bindParam(':color', $colorVehiculo);
        $stmtVehiculo->bindParam(':combustible', $combustibleVehiculo);
        $stmtVehiculo->bindParam(':pasajeros', $pasajerosVehiculo);
        $stmtVehiculo->bindParam(':transmision', $transmisionVehiculo);
        $stmtVehiculo->bindParam(':precio', $precioVehiculo);
        $stmtVehiculo->bindParam(':tipo', $tipoVehiculo);
        $stmtVehiculo->bindParam(':disponibilidad', $disponibilidadVehiculo);

        $stmtVehiculo->execute();

        // Insertar los detalles del vehículo en la tabla 'detalle_vehiculo'
        $sqlDetalle = "INSERT INTO detalle_vehiculo (matricula_vehiculo, id_marca_vehiculo, id_modelo_vehiculo, caracteristicas) 
                       VALUES (:matricula, :marca, :modelo, :caracteristicas)";

        $stmtDetalle = $con->prepare($sqlDetalle);
        $stmtDetalle->bindParam(':matricula', $matriculaVehiculo);  
        $stmtDetalle->bindParam(':marca', $marcaVehiculo);
        $stmtDetalle->bindParam(':modelo', $modeloVehiculo);
        $stmtDetalle->bindParam(':caracteristicas', $caracteristicas);
        $stmtDetalle->execute();

        // Confirmar la transacción
        $con->commit();

        // Responder con éxito
        echo json_encode(['mensaje' => 'Vehículo registrado exitosamente']);
    } catch (PDOException $e) {
        // En caso de error, hacer rollback de la transacción
        $con->rollBack();
        echo json_encode(['mensaje' => 'Error al registrar vehículo: ' . $e->getMessage()]);
    } catch (Exception $e) {
        // Manejar otros errores
        echo json_encode(['mensaje' => $e->getMessage()]);
    }
} else {
    echo json_encode(['mensaje' => 'Método HTTP no permitido.']);
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
    
    
    public static function editarVehiculo() {
        // Comprobar si la solicitud es de tipo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Crear conexión a la base de datos
                $objetoConexion = new Conexion();
                $con = $objetoConexion->conectar();
    
                // Obtener los datos enviados en la solicitud
                $matriculaVehiculo = $_POST['matriculaVehiculo'];
                $marcaVehiculo = $_POST['marcaVehiculo'];
                $modeloVehiculo = $_POST['modeloVehiculo'];
                $anioVehiculo = $_POST['anioVehiculo'];
                $colorVehiculo = $_POST['colorVehiculo'];
                $combustibleVehiculo = $_POST['combustibleVehiculo'];
                $pasajerosVehiculo = $_POST['pasajerosVehiculo'];
                $transmisionVehiculo = $_POST['transmisionVehiculo'];
                $precioVehiculo = $_POST['precioVehiculo'];
                $tipoVehiculo = $_POST['tipoVehiculo'];
                $caracteristicas = $_POST['caracteristicas'];
    
                // Verificar si se subió una nueva imagen
                $imagenVehiculo = null;
                if (isset($_FILES['imagen_vehiculo']) && $_FILES['imagen_vehiculo']['error'] == 0) {
                    $imageFileType = strtolower(pathinfo($_FILES['imagen_vehiculo']['name'], PATHINFO_EXTENSION));
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'jfif'];
    
                    if (!in_array($imageFileType, $allowedExtensions)) {
                        echo json_encode(['mensaje' => 'Formato de imagen no permitido.']);
                        return;
                    }
    
                    $imageName = uniqid('vehiculo_') . '.' . $imageFileType;
                    $targetDirectory = "../fotosVehiculos/";
                    $targetFile = $targetDirectory . $imageName;
                    move_uploaded_file($_FILES['imagen_vehiculo']['tmp_name'], $targetFile);
                    $imagenVehiculo = $imageName;
                }
    
                // Iniciar la transacción
                $con->beginTransaction();
    
                // Actualizar la tabla 'vehiculos'
                $sqlVehiculo = "UPDATE vehiculos SET 
                    año_vehiculo = :anio,
                    color_vehiculo = :color,
                    id_combustible_pertenece = :combustible,
                    pasajeros_vehiculo = :pasajeros,
                    id_transmision_pertenece = :transmision,
                    precio_vehiculo = :precio,
                    id_tipo_vehiculo = :tipo
                    " . ($imagenVehiculo ? ", imagen_vehiculo = :imagen" : "") . "
                    WHERE matricula_vehiculo = :matricula";
    
                $stmtVehiculo = $con->prepare($sqlVehiculo);
                $stmtVehiculo->bindParam(':anio', $anioVehiculo);
                $stmtVehiculo->bindParam(':color', $colorVehiculo);
                $stmtVehiculo->bindParam(':combustible', $combustibleVehiculo);
                $stmtVehiculo->bindParam(':pasajeros', $pasajerosVehiculo);
                $stmtVehiculo->bindParam(':transmision', $transmisionVehiculo);
                $stmtVehiculo->bindParam(':precio', $precioVehiculo);
                $stmtVehiculo->bindParam(':tipo', $tipoVehiculo);
                if ($imagenVehiculo) {
                    $stmtVehiculo->bindParam(':imagen', $imagenVehiculo);
                }
                $stmtVehiculo->bindParam(':matricula', $matriculaVehiculo);
                $stmtVehiculo->execute();
    
                // Actualizar la tabla 'detalle_vehiculo'
                $sqlDetalle = "UPDATE detalle_vehiculo SET 
                    id_marca_vehiculo = :marca,
                    id_modelo_vehiculo = :modelo,
                    caracteristicas = :caracteristicas
                    WHERE matricula_vehiculo = :matricula";
    
                $stmtDetalle = $con->prepare($sqlDetalle);
                $stmtDetalle->bindParam(':marca', $marcaVehiculo);
                $stmtDetalle->bindParam(':modelo', $modeloVehiculo);
                $stmtDetalle->bindParam(':caracteristicas', $caracteristicas);
                $stmtDetalle->bindParam(':matricula', $matriculaVehiculo);
                $stmtDetalle->execute();
    
                // Confirmar la transacción
                $con->commit();
    
                echo json_encode(['mensaje' => 'Vehículo actualizado exitosamente']);
            } catch (PDOException $e) {
                // En caso de error, hacer rollback
                $con->rollBack();
                echo json_encode(['mensaje' => 'Error al actualizar vehículo: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['mensaje' => 'Método no permitido']);
        }
    }

    public static function filtrarPorTipo($tipoVehiculo) {
        try {
            $objetoConexion = new Conexion();
            $con = $objetoConexion->conectar();
            
            $sql = "SELECT * FROM detalle_vehiculo dv
                    JOIN vehiculos v ON dv.matricula_vehiculo = v.matricula_vehiculo
                    WHERE v.id_tipo_vehiculo = :tipo";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':tipo', $tipoVehiculo, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($resultado) {
                echo json_encode($resultado);
            } else {
                echo json_encode(['mensaje' => 'No se encontraron resultados.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['mensaje' => 'Error al filtrar por tipo: ' . $e->getMessage()]);
        }
    }
    
    public static function filtrarPorAnio($anio) {
        try {
            $objetoConexion = new Conexion();
            $con = $objetoConexion->conectar();
            
            $sql = "SELECT * FROM detalle_vehiculo dv
                    JOIN vehiculos v ON dv.matricula_vehiculo = v.matricula_vehiculo
                    WHERE v.año_vehiculo = :anio";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':anio', $anio, PDO::PARAM_INT);
            $stmt->execute();
            
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            echo json_encode(['mensaje' => 'Error al filtrar por año: ' . $e->getMessage()]);
        }
    }
    
    public static function filtrarPorTransmision($transmision) {
        try {
            $objetoConexion = new Conexion();
            $con = $objetoConexion->conectar();
            
            $sql = "SELECT * FROM detalle_vehiculo dv
                    JOIN vehiculos v ON dv.matricula_vehiculo = v.matricula_vehiculo
                    JOIN transmision t ON v.id_transmision_pertenece = t.id_transmision
                    WHERE t.tipo_transmision = :transmision";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':transmision', $transmision, PDO::PARAM_STR);
            $stmt->execute();
            
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            echo json_encode(['mensaje' => 'Error al filtrar por transmisión: ' . $e->getMessage()]);
        }
    }
    
    public static function filtrarPorCombustible($combustible) {
        try {
            $objetoConexion = new Conexion();
            $con = $objetoConexion->conectar();
            
            $sql = "SELECT * FROM detalle_vehiculo dv
                    JOIN vehiculos v ON dv.matricula_vehiculo = v.matricula_vehiculo
                    JOIN combustible c ON v.id_combustible_pertenece = c.id_combustible
                    WHERE c.tipo_combustible = :combustible";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':combustible', $combustible, PDO::PARAM_STR);
            $stmt->execute();
            
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            echo json_encode(['mensaje' => 'Error al filtrar por combustible: ' . $e->getMessage()]);
        }
    }
    
    
}
?>
