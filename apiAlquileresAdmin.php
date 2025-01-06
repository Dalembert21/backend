<?php
include_once "alquileresAdmin.php";
header('Content-Type: application/json');

$accion = isset($_GET['accion']) ? $_GET['accion'] : '';

switch ($accion) {
    case 'obtener':
        $reservas = modeloAdminGestionar::obtenerReservasAdmin();
        echo json_encode($reservas);
        break;
    
    case 'actualizar':
        $id_reserva = isset($_POST['id_reserva']) ? $_POST['id_reserva'] : '';
        $nuevo_estado_pago = isset($_POST['nuevo_estado_pago']) ? $_POST['nuevo_estado_pago'] : ''; 
        $nuevo_estado_reserva = isset($_POST['nuevo_estado_reserva']) ? $_POST['nuevo_estado_reserva'] : ''; 

        if ($id_reserva && $nuevo_estado_pago && $nuevo_estado_reserva) {
            $resultado = modeloAdminGestionar::actualizarEstadoReserva($id_reserva, $nuevo_estado_pago, $nuevo_estado_reserva);
            echo json_encode(['success' => $resultado]);
        } else {
            echo json_encode(['error' => 'Faltan parámetros']);
        }
        break;

    case 'obtenerId':
        $id_reserva = isset($_POST['id_reserva']) ? $_POST['id_reserva'] : ''; // Asegúrate de obtener el id_reserva desde el POST
        
        if ($id_reserva) {
            $resultado = modeloAdminGestionar::obtenerIdReserva($id_reserva);
            if ($resultado) {
                echo json_encode(['id_reserva' => $resultado]); // Devuelve el id_reserva en formato JSON
            } else {
                echo json_encode(['error' => 'Reserva no encontrada']);
            }
        } else {
            echo json_encode(['error' => 'Faltan parámetros']);
        }
        break;

    case 'eliminar':
        $id_reserva = isset($_POST['id_reserva']) ? $_POST['id_reserva'] : '';
        
        if ($id_reserva) {
            $resultado = modeloAdminGestionar::eliminarReserva($id_reserva);
            echo json_encode(['success' => $resultado]);
        } else {
            echo json_encode(['error' => 'Faltan parámetros']);
        }
        break;
        case 'devolucion':
            // Obtener datos del cuerpo de la solicitud, asignando valores predeterminados si no se proporcionan
            $id_reserva = isset($_POST['id_reserva']) ? $_POST['id_reserva'] : null;
            $fecha_devolucion = isset($_POST['fecha_devolucion']) ? $_POST['fecha_devolucion'] : null;
            $estado_tanque = isset($_POST['estado_tanque']) ? $_POST['estado_tanque'] : null;
            $estado_limpieza = isset($_POST['estado_limpieza']) ? $_POST['estado_limpieza'] : null;
            $estado_danio = isset($_POST['estado_danio']) ? $_POST['estado_danio'] : null;
            $cargos_adicionales_retraso = isset($_POST['cargos_adicionales_retraso']) ? $_POST['cargos_adicionales_retraso'] : 0.00;
            $cargos_adicionales = isset($_POST['cargos_adicionales']) ? $_POST['cargos_adicionales'] : 0.00;
            $total_pagar = isset($_POST['total_pagar']) ? $_POST['total_pagar'] : null;
            $observaciones = isset($_POST['observaciones']) ? $_POST['observaciones'] : null;
            $estado_devolucion = isset($_POST['estado_devolucion']) ? $_POST['estado_devolucion'] : 'Por Cancelar';
        
            // Llamar al método para insertar la devolución
            $resultado = modeloAdminGestionar::insertarDevolucion(
                $id_reserva,
                $fecha_devolucion,
                $estado_tanque,
                $estado_limpieza,
                $estado_danio,
                $cargos_adicionales_retraso,
                $cargos_adicionales,
                $total_pagar,
                $observaciones,
                $estado_devolucion
            );
        
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Devolución insertada correctamente.']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error al insertar la devolución.']);
            }
            break;
            case 'mantenimiento':
                modeloAdminGestionar::obtenerVehiculosEnMantenimiento();
                
                break;
            case 'obtenerTalleres':
                modeloAdminGestionar::obtenerTalleres();
                break;

                case 'registrarMantenimiento':
                    // Obtener los parámetros de la solicitud POST
                    $matriculaVehiculo = isset($_POST['matricula_vehiculo']) ? $_POST['matricula_vehiculo'] : null;
                    $kilometrajeActual = isset($_POST['kilometraje_actual']) ? $_POST['kilometraje_actual'] : null;
                    $fechaIngreso = isset($_POST['fecha_ingreso']) ? $_POST['fecha_ingreso'] : null;
                    $costoEstimado = isset($_POST['costo_estimado']) ? $_POST['costo_estimado'] : null;
                    $fechaFinalizacion = isset($_POST['fecha_finalizacion']) ? $_POST['fecha_finalizacion'] : null;
                    $descripcionMantenimiento = isset($_POST['descripcion_mantenimiento']) ? $_POST['descripcion_mantenimiento'] : null;
                    $idTaller = isset($_POST['id_taller']) ? $_POST['id_taller'] : null;
                    $mantenimientoBasico = isset($_POST['mantenimiento_basico']) ? $_POST['mantenimiento_basico'] : null;
                    $revisionAdicional = isset($_POST['revision_adicional']) ? $_POST['revision_adicional'] : null;
                
                    // Llamar al método registrarMantenimiento
                    $resultado = modeloAdminGestionar::registrarMantenimiento(
                        $matriculaVehiculo,
                        $kilometrajeActual,
                        $fechaIngreso,
                        $costoEstimado,
                        $fechaFinalizacion,
                        $descripcionMantenimiento,
                        $idTaller,
                        $mantenimientoBasico,
                        $revisionAdicional
                    );
                
                    // Verificar el resultado y devolver la respuesta correspondiente
                    if ($resultado === "Mantenimiento registrado exitosamente.") {
                        echo json_encode(['success' => true, 'message' => $resultado]);
                    } else {
                        echo json_encode(['success' => false, 'error' => $resultado]);
                    }
                    break;
                
                
                

            default;
        
}
?>
