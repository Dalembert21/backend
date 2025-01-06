<?php
include_once "conexion.php";


class ModeloAdministrador {
    public static function registroEmpleado() {
        try {
            if (!isset($_POST['cedula_Empleado']) || !isset($_POST['nombre_Empleado'])) {
                echo json_encode(['mensaje' => 'Faltan datos']);
                return;
            }

            $cedulaEmpleado = $_POST['cedula_Empleado'];
            $nombreEmpleado = $_POST['nombre_Empleado'];
            $apellidoEmpleado = $_POST['apellido_Empleado'];
            $correoEmpleado = $_POST['correo_Empleado'];
            $claveEmpleado = password_hash($_POST['clave_Empleado'], PASSWORD_DEFAULT);
            $rol = $_POST['rol'];
            $objetoConexion = new Conexion();
            $con = $objetoConexion->conectar();
        
            $sql = "INSERT INTO empleados (cedula_Empleado, nombre_Empleado, apellido_Empleado, correo_Empleado, clave_Empleado, rol) VALUES (?, ?, ?, ?, ?, ?)";
            $datos = $con->prepare($sql);

            if ($datos->execute([$cedulaEmpleado, $nombreEmpleado, $apellidoEmpleado, $correoEmpleado, $claveEmpleado, $rol])) {
                echo json_encode(['mensaje' => 'Empleado creado correctamente']);
            } else {
                echo json_encode(['mensaje' => 'Empleado no creado: error al ejecutar la consulta']);
            }

        } catch (PDOException $e) {
            echo json_encode(['mensaje' => 'Error: ' . $e->getMessage()]);
        }
    }
    public static function obtenerRoles() {
        try {
            // Establecer la conexión a la base de datos
            $objetoConexion = new Conexion();
            $con = $objetoConexion->conectar();
    
            // Consulta SQL para obtener los roles
            $sql = "SELECT DISTINCT rol FROM empleados";
            $stmt = $con->prepare($sql);
            $stmt->execute();
    
            // Devolver los roles en formato JSON
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            echo json_encode(['mensaje' => 'Error: ' . $e->getMessage()]);
        }
    }
    public static function buscarEmpleadoPorCedula() {
        try {
            // Verificar que se reciba la cédula del empleado desde la URL
            if (!isset($_GET['cedula_Empleado'])) {
                echo json_encode(['mensaje' => 'Debe proporcionar la cédula del empleado']);
                return;
            }
            // Asignar la cédula del empleado a la variable
            $cedulaEmpleado = $_GET['cedula_Empleado'];
        
            // Establecer la conexión a la base de datos
            $objetoConexion = new Conexion();
            $con = $objetoConexion->conectar();
        
            // Consulta SQL para buscar al empleado por cédula
            $sql = "SELECT cedula_Empleado, nombre_Empleado, apellido_Empleado, correo_Empleado, clave_Empleado, rol 
                    FROM empleados WHERE cedula_Empleado = ?";
        
            $datos = $con->prepare($sql);
            $datos->execute([$cedulaEmpleado]);
        
            // Verificar si se encontró el empleado
            $empleado = $datos->fetch(PDO::FETCH_ASSOC);
        
            if ($empleado) {
                echo json_encode($empleado);  // Devolver los datos del empleado
            } else {
                echo json_encode(['mensaje' => 'Empleado no encontrado']);
            }
        } catch (PDOException $e) {
            echo json_encode(['mensaje' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    
    public static function seleccionarEmpleados(){
        try{
    
            $objetoConexion = new Conexion();
            $con = $objetoConexion->conectar();
            $sql = "SELECT cedula_Empleado, nombre_Empleado,apellido_Empleado, correo_Empleado, rol FROM empleados";
            $datos = $con->prepare($sql);
            $datos->execute();
            $empleados = $datos->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($empleados);
        
        }catch(PDOException $e){
            echo json_encode(['mensaje' => 'Error: ' . $e->getMessage()]);
        
        }
    }

    public static function eliminarEmpleado($cedulaEmpleado) {
        try {
            $objetoConexion = new Conexion();
            $con = $objetoConexion->conectar();
            
            // Consulta SQL para eliminar el empleado
            $sql = "DELETE FROM empleados WHERE cedula_Empleado = ?";
            $datos = $con->prepare($sql);
    
            if ($datos->execute([$cedulaEmpleado])) {
                echo json_encode(['mensaje' => 'Empleado eliminado correctamente']);
            } else {
                echo json_encode(['mensaje' => 'Error al eliminar el empleado']);
            }
        } catch (PDOException $e) {
            echo json_encode(['mensaje' => 'Error: ' . $e->getMessage()]);
        }
    }

    public static function editarEmpleado() {
        try {
            // Verificar que al menos se reciba la cédula para identificar al empleado
            if (!isset($_POST['cedula_Empleado'])) {
                echo json_encode(['mensaje' => 'Debe proporcionar la cédula del empleado']);
                return;
            }
        
            // Recibir los datos enviados por POST
            $cedulaEmpleado = $_POST['cedula_Empleado'];
            $nombreEmpleado = isset($_POST['nombre_Empleado']) ? $_POST['nombre_Empleado'] : null;
            $apellidoEmpleado = isset($_POST['apellido_Empleado']) ? $_POST['apellido_Empleado'] : null;
            $correoEmpleado = isset($_POST['correo_Empleado']) ? $_POST['correo_Empleado'] : null;
            $claveEmpleado = isset($_POST['clave_Empleado']) ? $_POST['clave_Empleado'] : null;
            $rol = isset($_POST['rol']) ? $_POST['rol'] : null;
        
            // Establecer la conexión a la base de datos
            $objetoConexion = new Conexion();
            $con = $objetoConexion->conectar();
        
            // Construir la consulta SQL dinámicamente
            $sql = "UPDATE empleados SET ";
            $campos = [];
            $valores = [];
        
            if ($nombreEmpleado !== null) {
                $campos[] = "nombre_Empleado = ?";
                $valores[] = $nombreEmpleado;
            }
            if ($apellidoEmpleado !== null) {
                $campos[] = "apellido_Empleado = ?";
                $valores[] = $apellidoEmpleado;
            }
            if ($correoEmpleado !== null) {
                $campos[] = "correo_Empleado = ?";
                $valores[] = $correoEmpleado;
            }
            // Si se recibe una nueva clave, encriptarla antes de actualizar
            if ($claveEmpleado !== null) {
                $claveEmpleado = password_hash($claveEmpleado, PASSWORD_DEFAULT); // Encriptar la nueva clave
                $campos[] = "clave_Empleado = ?";
                $valores[] = $claveEmpleado;
            }
            if ($rol !== null) {
                $campos[] = "rol = ?";
                $valores[] = $rol;
            }
        
            // Si no hay campos para actualizar, finalizar
            if (empty($campos)) {
                echo json_encode(['mensaje' => 'No se proporcionaron datos para actualizar']);
                return;
            }
        
            // Agregar la condición de cédula al final
            $sql .= implode(", ", $campos) . " WHERE cedula_Empleado = ?";
            $valores[] = $cedulaEmpleado;
        
            // Preparar y ejecutar la consulta
            $datos = $con->prepare($sql);
            if ($datos->execute($valores)) {
                echo json_encode(['mensaje' => 'Empleado actualizado correctamente']);
            } else {
                echo json_encode(['mensaje' => 'Empleado no actualizado: error al ejecutar la consulta']);
            }
        
        } catch (PDOException $e) {
            // Manejo de errores
            echo json_encode(['mensaje' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    public static function obtenerEmpleado($cedulaEmpleado) {
        try {
            // Establecer la conexión a la base de datos
            $objetoConexion = new Conexion();
            $con = $objetoConexion->conectar();
    
            // Consulta SQL para obtener los datos del empleado
            $sql = "SELECT cedula_Empleado, nombre_Empleado, apellido_Empleado, correo_Empleado, clave_Empleado, rol 
                    FROM empleados WHERE cedula_Empleado = ?";
            
            $datos = $con->prepare($sql);
            $datos->execute([$cedulaEmpleado]);
    
            // Verificar si se encontró el empleado
            $empleado = $datos->fetch(PDO::FETCH_ASSOC);
            
            if ($empleado) {
                echo json_encode($empleado);  // Devolver los datos del empleado
            } else {
                echo json_encode(['mensaje' => 'Empleado no encontrado']);
            }
        } catch (PDOException $e) {
            echo json_encode(['mensaje' => 'Error: ' . $e->getMessage()]);
        }
    }
    public static function verificarEmpleado() {
        try {
            // Verificar que se reciba la cédula y la contraseña desde el formulario
            if (!isset($_POST['cedula_Empleado']) || !isset($_POST['clave_Empleado'])) {
                echo json_encode(['mensaje' => 'Debe proporcionar la cédula y la contraseña']);
                return;
            }
    
            $cedulaEmpleado = $_POST['cedula_Empleado'];
            $claveEmpleado = $_POST['clave_Empleado'];
    
            // Establecer la conexión a la base de datos
            $objetoConexion = new Conexion();
            $con = $objetoConexion->conectar();
    
            // Consulta SQL para obtener el empleado por cédula
            $sql = "SELECT cedula_Empleado, nombre_Empleado, apellido_Empleado, correo_Empleado, clave_Empleado, rol 
                    FROM empleados WHERE cedula_Empleado = ?";
    
            $datos = $con->prepare($sql);
            $datos->execute([$cedulaEmpleado]);
    
            // Verificar si se encontró el empleado
            $empleado = $datos->fetch(PDO::FETCH_ASSOC);
    
            if ($empleado) {
                // Verificar si la contraseña proporcionada coincide con el hash almacenado
                if (password_verify($claveEmpleado, $empleado['clave_Empleado'])) {
                    // Contraseña correcta
                    echo json_encode(['mensaje' => 'Empleado verificado correctamente', 'empleado' => $empleado]);
                } else {
                    // Contraseña incorrecta
                    echo json_encode(['mensaje' => 'Contraseña incorrecta']);
                }
            } else {
                echo json_encode(['mensaje' => 'Empleado no encontrado']);
            }
        } catch (PDOException $e) {
            echo json_encode(['mensaje' => 'Error: ' . $e->getMessage()]);
        }
    }
        public static function obtenerTransmisiones() {
            $objetoConexion = new Conexion();
            $con = $objetoConexion->conectar();
            $sql = "SELECT DISTINCT transmision_vehiculo FROM vehiculos";
            $stmt = $con->prepare($sql);
            $stmt->execute();
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
    
    public static function obtenerCombustibles() {
        $objetoConexion = new Conexion();
        $con = $objetoConexion->conectar();
        $sql = "SELECT DISTINCT combustible_vehiculo FROM vehiculos";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    public static function obtenerTiposVehiculos() {
        $objetoConexion = new Conexion();
        $con = $objetoConexion->conectar();
        $sql = "SELECT DISTINCT tipo_vehiculo FROM tipo_vehiculos";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}

?>

