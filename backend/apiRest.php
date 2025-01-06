<?php

include_once "Consultas.php";
include_once "ConsultasAdmin.php"; // Asegúrate de incluir el archivo donde está la clase ModeloAdministrador
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'registrarUsuario':
                Modelo::registrarUsuario();
                break;
            case 'loginUsuario':
                Modelo::loginUsuario();
                break;
            case 'recuperarContrasena':
                if (isset($_POST['email'])) {
                    Modelo::recuperarContrasena($_POST['email']);
                } else {
                    echo json_encode(['mensaje' => 'Email no proporcionado']);
                }
                break;
            case 'resetearContrasena':
                if (isset($_POST['token']) && isset($_POST['new_password'])) {
                    Modelo::resetearContrasena($_POST['token'], $_POST['new_password']);
                } else {
                    echo json_encode(['mensaje' => 'Token o nueva contraseña no proporcionados']);
                }
                break;
            case 'registrarEmpleado':
                ModeloAdministrador::registroEmpleado();
                break;
            case 'eliminarEmpleado':
                if (isset($_POST['cedula_Empleado'])) {
                    ModeloAdministrador::eliminarEmpleado($_POST['cedula_Empleado']);  // Pasa la cédula como argumento
                } else {
                    echo json_encode(['mensaje' => 'Cédula del empleado no proporcionada']);
                }
                break;
            case 'editarEmpleado':
                ModeloAdministrador::editarEmpleado();
                break;
            // Otras acciones en POST
            default:
                echo json_encode(['mensaje' => 'Acción no válida']);
                break;
        }
    } else {
        echo json_encode(['mensaje' => 'No se ha especificado ninguna acción']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['accion'])) {
        switch ($_GET['accion']) {
            case 'seleccionarEmpleado':
                // Acción para seleccionar todos los empleados
                ModeloAdministrador::seleccionarEmpleados();
                break;
            case 'obtenerEmpleado':
                // Acción para obtener un empleado por cédula
                if (isset($_GET['cedula_Empleado'])) {
                    $cedula = $_GET['cedula_Empleado'];
                    ModeloAdministrador::obtenerEmpleado($cedula);
                } else {
                    echo json_encode(['mensaje' => 'Cédula del empleado no proporcionada']);
                }
                break;
            case 'buscarEmpleadoPorCedula':
                if (isset($_GET['cedula_Empleado'])) {
                    ModeloAdministrador::buscarEmpleadoPorCedula($_GET['cedula_Empleado']);
                } else {
                    echo json_encode(['mensaje' => 'Cédula del empleado no proporcionada']);
                }
                break;
            case 'verificarEmpleado':
                ModeloAdministrador::verificarEmpleado();
                break;
            case 'obtenerRoles':
                // Acción para obtener los roles
                ModeloAdministrador::obtenerRoles();
                break;
            default:
                echo json_encode(['mensaje' => 'Acción no válida o no especificada en GET']);
                break;
        }
    } else {
        echo json_encode(['mensaje' => 'Acción no especificada en GET']);
    }
} else {
    echo json_encode(['mensaje' => 'Método no permitido']);
}
?>
