<?php
include_once "conexion.php"; // Asegúrate de que el archivo de conexión esté correctamente incluido
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json'); 

class Modelo {
    public static function registrarUsuario() {
        try {
            // Recoger los datos del formulario
            $nombreRegistro = $_POST['nombre_Registro'];
            $apellidoRegistro = $_POST['apellido_Registro'];
            $correoRegistro = $_POST['correo_Registro'];
            $contraseñaRegistro = $_POST['clave_Registro'];
    
            // Generar un token de verificación
            $verificationToken = bin2hex(random_bytes(50));
            $verificationTokenExpiration = date('Y-m-d H:i:s', strtotime('+1 hour')); // Expira en 1 hora
    
            // Conectar a la base de datos
            $objetoConexion = new Conexion();
            $con = $objetoConexion->conectar();
    
            // Preparar la consulta SQL
            $sql = "INSERT INTO usuarios (nombre_Registro, apellido_Registro, correo_Registro, clave_Registro, verification_token, verification_token_expiration) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $datos = $con->prepare($sql);
    
            if ($datos->execute([$nombreRegistro, $apellidoRegistro, $correoRegistro, $contraseñaRegistro, $verificationToken, $verificationTokenExpiration])) {
                // Enviar correo de verificación
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'dalembertbravo2@gmail.com'; // Tu correo
                $mail->Password = 'remjppzatmsxhotj'; // Contraseña de aplicación
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
    
                // Configuración del correo
                $mail->setFrom('dalembertbravo2@gmail.com', 'UTA DRIVE');
                $mail->addAddress($correoRegistro); // Correo del usuario
                $mail->isHTML(true);
                $mail->Subject = 'Confirmación de Registro';
                $mail->Body = "Hola $nombreRegistro,<br><br>Gracias por registrarte en UTA DRIVE. Por favor, confirma tu cuenta haciendo clic en el siguiente enlace:<br>
                <a href='http://localhost/app-Alquiler-Autos/vista/verificarCorreo.php?token=$verificationToken'>Confirmar Cuenta</a><br><br>
                Este enlace expirará en 1 hora.";
    
                $mail->send();
                echo json_encode(['mensaje' => 'Usuario registrado exitosamente. Por favor, revisa tu correo para confirmar tu cuenta.']);
            } else {
                echo json_encode(['mensaje' => 'Error al registrar el usuario.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['mensaje' => 'Error al registrar el usuario: ' . $e->getMessage()]);
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al enviar el correo: ' . $e->getMessage()]);
        }
    }
    

    public static function loginUsuario() {
        try {
            session_start(); // Iniciar la sesión
            $correoRegistro = $_POST['correo_Registro'];
            $contraseñaRegistro = $_POST['clave_Registro'];
    
            // Conectar a la base de datos
            $objetoConexion = new Conexion();
            $con = $objetoConexion->conectar();
    
            // Consulta en la tabla usuarios
            $sqlUsuarios = "SELECT id_Registro AS id, nombre_Registro AS nombre, NULL AS rol, verification_token 
                            FROM usuarios 
                            WHERE correo_Registro = ? AND clave_Registro = ?";
            $stmtUsuarios = $con->prepare($sqlUsuarios);
            $stmtUsuarios->execute([$correoRegistro, $contraseñaRegistro]);
    
            // Consulta en la tabla empleados
            $sqlEmpleados = "SELECT cedula_Empleado AS id, nombre_Empleado AS nombre, rol, NULL AS verification_token 
                             FROM empleados 
                             WHERE correo_Empleado = ? AND clave_Empleado = ?";
            $stmtEmpleados = $con->prepare($sqlEmpleados);
            $stmtEmpleados->execute([$correoRegistro, $contraseñaRegistro]);
    
            // Verificar si el usuario está en alguna tabla
            if ($stmtUsuarios->rowCount() > 0) {
                $usuario = $stmtUsuarios->fetch(PDO::FETCH_ASSOC);
    
                // Verificar si la cuenta está confirmada
                if ($usuario['verification_token'] === null) {
                    // Guardar datos del usuario en la sesión
                    $_SESSION['id_usuario'] = $usuario['id'];
                    $_SESSION['nombre_usuario'] = $usuario['nombre'];
    
                    // Usuario autenticado correctamente
                    echo json_encode([
                        'mensaje' => 'Usuario autenticado',
                        'nombre' => $usuario['nombre'],
                        'id' => $usuario['id'],
                        'rol' => 'Usuario' // Rol predeterminado para usuarios
                    ]);
                } else {
                    // La cuenta no está confirmada
                    echo json_encode(['mensaje' => 'Por favor, confirma tu cuenta antes de iniciar sesión.']);
                }
            } elseif ($stmtEmpleados->rowCount() > 0) {
                $empleado = $stmtEmpleados->fetch(PDO::FETCH_ASSOC);
    
                // Guardar datos del empleado en la sesión
                $_SESSION['id_usuario'] = $empleado['id'];
                $_SESSION['nombre_usuario'] = $empleado['nombre'];
                $_SESSION['rol_usuario'] = $empleado['rol'];
    
                // Empleado autenticado correctamente
                echo json_encode([
                    'mensaje' => 'Usuario autenticado',
                    'nombre' => $empleado['nombre'],
                    'id' => $empleado['id'],
                    'rol' => $empleado['rol']
                ]);
            } else {
                // No se encontraron credenciales
                echo json_encode(['mensaje' => 'Credenciales incorrectas.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['mensaje' => 'Error al iniciar sesión: ' . $e->getMessage()]);
        }
    }
    
    
    
    

    public static function recuperarContrasena($email) {
        try {
            // Conectar a la base de datos
            $objetoConexion = new Conexion();
            $con = $objetoConexion->conectar();

            // Verificar si el correo existe
            $sql = "SELECT * FROM usuarios WHERE correo_Registro = ?";
            $stmt = $con->prepare($sql);
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                // Generar un token único para el restablecimiento
                $token = bin2hex(random_bytes(50)); // Generar un token seguro

                // Aquí puedes almacenar el token en la base de datos y asignarle una fecha de expiración
                $sqlToken = "UPDATE usuarios SET reset_token = ?, reset_token_expiration = NOW() + INTERVAL 1 HOUR WHERE correo_Registro = ?";
                $stmtToken = $con->prepare($sqlToken);
                $stmtToken->execute([$token, $email]);

                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Servidor SMTP de Gmail
                $mail->SMTPAuth = true;
                $mail->Username = 'dalembertbravo2@gmail.com'; // Tu correo de Gmail
                $mail->Password = 'remjppzatmsxhotj'; // Usa la contraseña de aplicación
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                
                // Configuración del remitente y destinatario
                $mail->setFrom('dalembertbravo2@gmail.com', 'UTA DRIVE');
                $mail->addAddress($email); // El correo de destino
                // Contenido del correo
                $mail->isHTML(true);
                $mail->Subject = 'Recuperación de Contraseña';
                $mail->Body = "Haz clic en este enlace para restablecer tu contraseña: 
                <a href='http://localhost/app-Alquiler-Autos/vista/resetContra.php?token=$token'>Restablecer Contraseña</a>";
                
                // Enviar el correo
                $mail->send();
                echo json_encode(['mensaje' => 'Se ha enviado un enlace para restablecer la contraseña a tu correo']);
            } else {
                echo json_encode(['mensaje' => 'El correo no está registrado']);
            }
        } catch (PDOException $e) {
            echo json_encode(['mensaje' => 'Error al recuperar contraseña: ' . $e->getMessage()]);
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al enviar el correo: ' . $mail->ErrorInfo]);
        }
    }

    public static function resetearContrasena($token, $newPassword) {
        try {
            // Conectar a la base de datos
            $objetoConexion = new Conexion();
            $con = $objetoConexion->conectar();
    
            // Verificar el token
            $sql = "SELECT * FROM usuarios WHERE reset_token = ? AND reset_token_expiration > NOW()";
            $stmt = $con->prepare($sql);
            $stmt->execute([$token]);
    
            if ($stmt->rowCount() > 0) {
                // Actualizar la contraseña
                $sqlUpdate = "UPDATE usuarios SET clave_Registro = ?, reset_token = NULL, reset_token_expiration = NULL WHERE reset_token = ?";
                $stmtUpdate = $con->prepare($sqlUpdate);
                $stmtUpdate->execute([$newPassword, $token]);
    
                echo json_encode(['mensaje' => 'Contraseña restablecida con éxito']);
            } else {
                echo json_encode(['mensaje' => 'Token no válido o expirado']);
            }
        } catch (PDOException $e) {
            echo json_encode(['mensaje' => 'Error al restablecer la contraseña: ' . $e->getMessage()]);
        }
    }
}
?>
