<?php
require_once __DIR__ . "/../../models/ajustes/EnvioModel.php";
require_once __DIR__ . '/../../lib/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../../lib/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../../lib/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json; charset=utf-8');

$response = ["success" => false, "message" => "Acción no válida"];

try {
    $pdo = Database::getConnection();
    $envioModel = new EnvioModel($pdo);

    if (isset($_GET['action'])) {
        switch ($_GET['action']) {

            case 'listar':
                $data = $envioModel->obtenerCampanias();
                $response = $data;
                break;

            case 'ver':
                if (!isset($_GET['idcampania'])) throw new Exception("ID de campaña requerido");
                $data = $envioModel->obtenerCampania($_GET['idcampania']);
                $response = $data;
                break;

            case 'crear':
                $data = $_POST;

                if (!empty($data['programaciones']) && is_string($data['programaciones'])) {
                    $data['programaciones'] = json_decode($data['programaciones'], true);
                }

                if (empty($data['idusuario'])) {
                    if (!empty($_SESSION['idusuario'])) {
                        $data['idusuario'] = $_SESSION['idusuario'];
                    } else {
                        throw new Exception("ID de usuario requerido");
                    }
                }

                $id = $envioModel->crearCampania($data);
                $response = [
                    "success" => true,
                    "message" => "Campaña creada",
                    "id" => $id
                ];
                break;

            case 'actualizar':
                if (!isset($_POST['idcampania'])) throw new Exception("ID de campaña requerido");
                $data = $_POST;

                if (!empty($data['programaciones']) && is_string($data['programaciones'])) {
                    $data['programaciones'] = json_decode($data['programaciones'], true);
                }

                $envioModel->actualizarCampania($_POST['idcampania'], $data);
                $response = [
                    "success" => true,
                    "message" => "Campaña actualizada"
                ];
                break;

            case 'eliminar':
                if (!isset($_POST['idcampania'])) throw new Exception("ID de campaña requerido");
                $envioModel->eliminarCampania($_POST['idcampania']);
                $response = [
                    "success" => true,
                    "message" => "Campaña eliminada"
                ];
                break;

            case 'obtenerProgramacionesPendientes':
                $data = $envioModel->obtenerProgramacionesPendientes();
                $response = $data;
                break;

            case 'iniciarCampania':
                $idcampania = $_POST['idcampania'] ?? null;
                if (!$idcampania) throw new Exception("ID de campaña requerido");
                $programaciones = isset($_POST['programaciones']) ? json_decode($_POST['programaciones'], true) : [];

                $envioModel->iniciarCampania($idcampania, null, $programaciones);
                $response = [
                    "success" => true,
                    "message" => "Campaña iniciada"
                ];
                break;

            case 'actualizarEstadoEnvio':
                $idenvio = $_POST['idenvio'] ?? null;
                $nuevoEstado = $_POST['nuevoEstado'] ?? null;
                if (!$idenvio || !$nuevoEstado) throw new Exception("Faltan datos necesarios para la solicitud");
                $envioModel->actualizarEstadoEnvio($idenvio, $nuevoEstado);
                $response = [
                    "success" => true,
                    "message" => "Estado de envío actualizado"
                ];
                break;

            case 'finalizarCampania':
                $idcampania = $_POST['idcampania'] ?? null;
                if (!$idcampania) throw new Exception("ID de campaña requerido");
                $envioModel->finalizarCampania($idcampania);
                $response = [
                    "success" => true,
                    "message" => "Campaña finalizada"
                ];
                break;

            case 'enviarProgramacion':
                try {
                    $idenvio = $_POST['idenvio'] ?? null;
                    if (!$idenvio) throw new Exception("ID de envío requerido");

                    $prog = $envioModel->obtenerProgramacion($idenvio);
                    $summary = [];
                    $usuarioEjecutor = $_SESSION['idusuario'] ?? null;

                    $idenvio = $prog['idenvio'] ?? null;
                    $itemResult = [
                        'idenvio' => $idenvio,
                        'idplantilla' => $prog['idplantilla'] ?? null,
                        'enviados' => [],
                        'fallos' => []
                    ];
                    $emisor = $envioModel->obtenerEmisor($prog['idemisor']);

                    // Si no hay datos de emisor suficientes, saltar (o usar método de envío por defecto)
                    if (!$emisor || empty($emisor['correo'])) {
                        $itemResult['fallos'][] = "Emisor no disponible o sin email para idenvio {$idenvio}";
                        $summary[] = $itemResult;
                    } else {
                        // PHPMailer por programación: crear una instancia por emisor / programación
                        $mail = new PHPMailer(true);
                        try {
                            // Configuración SMTP si existe
                            if (!empty($emisor['smtp_host']) && !empty($emisor['smtp_pass'])) {
                                $mail->isSMTP();
                                $mail->Host = $emisor['smtp_host'];
                                $mail->SMTPAuth = true;
                                $mail->Username = $emisor['smtp_user'];
                                $mail->Password = $emisor['smtp_pass'];

                                // puerto y secure
                                $mail->Port = 465;
                                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                            } else {
                                throw new Exception("Emisor no tiene usuario o contraseña configurados.");
                            }

                            // From
                            $fromEmail = $emisor['correo'] ?? null;
                            $fromName = $emisor['nombre'] ?? '';
                            if (!$fromEmail) throw new Exception("Emisor no tiene correo configurado.");

                            $mail->setFrom($fromEmail, $fromName);

                            // Contenido de la plantilla
                            $plantilla = $prog['plantilla'];
                            $asunto = $plantilla['asunto'] ?? 'Sin asunto';
                            $contenidoHtml = $plantilla['contenido_html'] ?? null;
                            $contenidoTexto = $plantilla['contenido_texto'] ?? null;

                            $isHtml = !empty($contenidoHtml);
                            if ($isHtml) {
                                $mail->isHTML(true);
                                $mail->Body = $contenidoHtml;
                                $mail->AltBody = $contenidoTexto ?? strip_tags($contenidoHtml);
                            } else {
                                $mail->isHTML(false);
                                $mail->Body = $contenidoTexto ?? '';
                            }

                            $mail->Subject = $asunto;
                            $receptores = $prog['receptores'] ?? null;
                            if (!is_array($receptores)) $receptores = [];

                            foreach ($receptores as $r) {
                                $emailTo = $r['correo'] ?? $r['email'] ?? null;
                                $nombreTo = $r['nombres'] ?? ($r['nombre'] ?? '') . (isset($r['apellidos']) ? ' ' . $r['apellidos'] : '');
                                if (!$emailTo) {
                                    $itemResult['fallos'][] = [
                                        'receptor' => $r,
                                        'error' => 'Receptor sin email'
                                    ];
                                    continue;
                                }

                                try {
                                    // Clonar o limpiar destinatarios previos
                                    $mail->clearAddresses();
                                    $mail->addAddress($emailTo, $nombreTo);

                                    // Opcionales: Reply-To
                                    if (!empty($prog['reply_to'])) {
                                        $mail->clearReplyTos();
                                        $mail->addReplyTo($prog['reply_to']);
                                    }

                                    // Enviar
                                    $mail->send();

                                    // Registrar éxito por receptor
                                    $itemResult['enviados'][] = [
                                        'receptor' => $emailTo,
                                        'nombre' => $nombreTo
                                    ];

                                    $envioModel->actualizarEnvioPorReceptor($idenvio, $r['idcliente'], "enviado");
                                } catch (Exception $eReceptor) {
                                    $itemResult['fallos'][] = [
                                        'receptor' => $emailTo,
                                        'error' => $mail->ErrorInfo . " Emisor: " . json_encode($emisor) ?: $eReceptor->getMessage()
                                    ];

                                    $envioModel->actualizarEnvioPorReceptor($idenvio, $r['idcliente'], "error");
                                }
                            }

                            $totalEnviados = count($itemResult['enviados']);
                            $totalFallos = count($itemResult['fallos']);
                            if ($totalEnviados > 0) {
                                $envioModel->marcarProgramacionEnviada($idenvio, $usuarioEjecutor);
                            } else {
                                $envioModel->marcarProgramacionError($idenvio, $itemResult['fallos'], $usuarioEjecutor);
                            }
                        } catch (Exception $eMail) {
                            // Error global preparando envío de esta programación
                            $itemResult['fallos'][] = "Error PHPMailer preparación: " . $eMail->getMessage();
                        }
                        $summary[] = $itemResult;
                    }

                    $response = [
                        "success" => true,
                        "message" => "Proceso de envío finalizado",
                        "data" => $summary
                    ];
                } catch (Exception $e) {
                    $response = [
                        "success" => false,
                        "message" => "Error al procesar envío: " . $e->getMessage()
                    ];
                }
                break;
        }
    }
} catch (Exception $e) {
    $response = ["success" => false, "message" => $e->getMessage()];
}

echo json_encode($response);
