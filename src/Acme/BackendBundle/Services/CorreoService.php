<?php

namespace Acme\BackendBundle\Services;

use Acme\BackendBundle\Scheduled\ScheduledServiceInterface;
use Acme\BackendBundle\Entity\Job;
use Acme\BackendBundle\Services\UtilService;
use Acme\BackendBundle\Entity\WebCode;

class CorreoService implements ScheduledServiceInterface{
    
    protected $container;
    protected $doctrine;
    protected $logger;
    protected $job;
    
    public function __construct($container) { 
        $this->container = $container;
        $this->doctrine = $this->container->get('doctrine');
        $this->logger = $this->container->get('logger');
        $this->job = null;
    }
    
    private function getCurrentFecha(){
        if($this->job === null){
            return new \DateTime();
        }else{
            return clone $this->job->getNextExecutionDate();
        }
    }
    
    public function notificarFacturasRecibidas(){       
        $textLog = "NotificarFacturasRecibidas: Buscando facturas recibidas.";
        var_dump($textLog);
        $this->logger->warn($textLog);
        $compras = $this->doctrine->getRepository('MayaBundle:Compra')->getComprasByFacturasRecibidas();
        if(count($compras) === 0){
            $textLog = "NotificarFacturasRecibidas: No existen facturas recibidas pendiente de notificar.";
            var_dump($textLog);
            $this->logger->warn($textLog);
        }else{
            $textLog = "NotificarFacturasRecibidas: Existen " .count($compras)." facturas recibidas pendientes de notificar.";
            var_dump($textLog);
            $this->logger->warn($textLog);
            $em = $this->doctrine->getManager();
            foreach ($compras as $compra) {
                $factura = $compra->getFactura();
                $textLog = "NotificarFacturasRecibidas: Procesando factura ID: " . $factura->getId() ;
                var_dump($textLog);
                $this->logger->warn($textLog);
                $now = new \DateTime();
                $now = $now->format('Y-m-d H:i:s');
                $em->getConnection()->beginTransaction();
                try {
                    $to = array($compra->getCliente()->getCorreo());
                    $textLog = "NotificarFacturasRecibidas: Enviando correo para notificar que la factura " . $compra->getId() . " esta lista para entrega. Direccion : " . $compra->getCliente()->getCorreo() . "-init";
                    var_dump($textLog);
                    $this->logger->warn($textLog);
                    
                    $subject = "Fuente del Norte. Notificación de Factura lista para entrega.";
                    UtilService::sendEmail($this->container, $subject, $to, $this->container->get("templating")->render('MayaBundle:Email:notificacionFactura.html.twig', array(
                        "compra" => $compra
                    )));
                        
                    $factura->setNotificada(true);
                    $em->persist($factura);
                    $em->flush();
                    $em->getConnection()->commit();

                } catch (\RuntimeException $exc) {
                    $em->getConnection()->rollback();
                    $textLog = "NotificarFacturasRecibidas: Error: " . $exc->getMessage();
                    var_dump($textLog);
                    $this->logger->warn($textLog);
                } catch (\Exception $exc) {
                    $em->getConnection()->rollback();
                    $textLog = "NotificarFacturasRecibidas: Error: " . $exc->getMessage();
                    var_dump($textLog);
                    $this->logger->warn($textLog);
                }
            }
        }
    }
    
    public function enviarVoucherBoletos(){      
        $textLog = "EnviarVoucherBoletos: Buscando compras recientes.";
        var_dump($textLog);
        $this->logger->warn($textLog);
        $compras = $this->doctrine->getRepository('MayaBundle:Compra')->getComprasPendientesNotificar();
        if(count($compras) === 0){
            $textLog = "EnviarVoucherBoletos: No existen compras pendientes de notificar.";
            var_dump($textLog);
            $this->logger->warn($textLog);
        }else{
            $textLog = "EnviarVoucherBoletos: Existen " .count($compras)." compras pendientes de notificar.";
            var_dump($textLog);
            $this->logger->warn($textLog);
            $em = $this->doctrine->getManager();
            foreach ($compras as $compra) {
                $textLog = "EnviarVoucherBoletos: Procesando compra ID: " . $compra->getId();
                var_dump($textLog);
                $this->logger->warn($textLog);
                $now = new \DateTime();
                $now = $now->format('Y-m-d H:i:s');
                $em->getConnection()->beginTransaction();
                try {
                    $to = array($compra->getCliente()->getCorreo());
                    $textLog = "EnviarVoucherBoletos: Enviando correo compra satisfactoria ID: " . $compra->getId() . ". a la direccion : " . $compra->getCliente()->getCorreo() . "-init";
                    var_dump($textLog);
                    $this->logger->warn($textLog);
                    
                    $listaIdBoletosExternos = $compra->getListaIdExternoBoletos();
                    $pathPdf = $this->getPathVoucherBoleto($listaIdBoletosExternos);
                    if($pathPdf === null){
                        continue;
                    }
                    
                    $pathPdf = $this->container->getParameter("internal_sys_url") . $pathPdf;
                    var_dump($pathPdf);
                    
                    $subject = "Fuente del Norte. Gracias por su compra.";
                    UtilService::sendEmail($this->container, $subject, $to, $this->container->get("templating")->render('MayaBundle:Email:notificacionCompra.html.twig', array(
                        "compra" => $compra
                    )), array($pathPdf));
                        
                    $compra->setNotificada(true);
                    $em->persist($compra);
                    $em->flush();
                    $em->getConnection()->commit();

                } catch (\RuntimeException $exc) {
                    $em->getConnection()->rollback();
                    $textLog = "EnviarVoucherBoletos: Error: " . $exc->getMessage();
                    var_dump($textLog);
                    $this->logger->warn($textLog);
                } catch (\ErrorException $exc) {
                    $em->getConnection()->rollback();
                    $textLog = "EnviarVoucherBoletos: Error: " . $exc->getMessage();
                    var_dump($textLog);
                    $this->logger->warn($textLog);
                } catch (\Exception $exc) {
                    $em->getConnection()->rollback();
                    $textLog = "EnviarVoucherBoletos: Error: " . $exc->getMessage();
                    var_dump($textLog);
                    $this->logger->warn($textLog);
                }
            }
        }
    }
    
    private function getPathVoucherBoleto($listaIdBoletosExternos){
    
        $idApp = $this->container->getParameter("id_empresa_app");
        $now = new \DateTime();
        $dataWeb = $now->format('Y-m-d'). "_system_web_".$idApp;
        $claveInterna = $this->container->getParameter("internal_sys_clave_interna");
        $tokenAutLocal = \Acme\BackendBundle\Services\UtilService::encrypt($claveInterna, $dataWeb);
        
        $data = array( "idWeb" => $idApp, "tokenAut" => $tokenAutLocal );
        $data['data'] = json_encode($listaIdBoletosExternos);
            
        $postdata = http_build_query($data);
        $options = array(
              'http' => array(
              'method'  => 'POST',
              'content' => $postdata,
              'header'  => "Content-type: application/x-www-form-urlencoded\r\n"));
        
        $url = $this->container->getParameter("internal_sys_url") .
               $this->container->getParameter("internal_sys_pref") .
               "vb.json";
        $context  = stream_context_create( $options );
        $result = file_get_contents($url, false, $context );
        $response = json_decode($result, true);
//        var_dump($response);
        if($response['status'] === WebCode::SERVIDOR_SATISFACTORIO){
            return $response['path'];
        }else{
            var_dump($response['message']);
            $this->logger->warn($response['message']);
            return null;
        }
    }
    
    public function enviarMensajesPendientes(){
        $textLog = "EnviarMensajesPendientes: Buscando mensajes pendientes de notificar.";
        var_dump($textLog);
        $this->logger->warn($textLog);
        $mensajes= $this->doctrine->getRepository('MayaBundle:Mensaje')->findByEnviado(false);
        if(count($mensajes) === 0){
            $textLog = "EnviarMensajesPendientes: No existen mensajes pendientes de notificar.";
            var_dump($textLog);
            $this->logger->warn($textLog);
        }else{
            $textLog = "EnviarMensajesPendientes: Existen " .count($mensajes)." mensajes pendientes de notificar.";
            var_dump($textLog);
            $this->logger->warn($textLog);
            $mailers = $this->container->getParameter("message_mailers");
            if($mailers !== null && trim($mailers) !== ""){
                $to2 = split(",", $mailers);
                foreach ($mensajes as $mensaje) {
                    $textLog = "EnviarMensajesPendientes: Procesando mensaje ID: " . $mensaje->getId();
                    var_dump($textLog);
                    $this->logger->warn($textLog);
                    $now = new \DateTime();
                    $now = $now->format('Y-m-d H:i:s');
                    $em = $this->doctrine->getManager();
                    $em->getConnection()->beginTransaction();
                    try {
                        
                        $textLog = "EnviarMensajesPendientes: Confirmando mensaje ID: " . $mensaje->getId() . ". a la direccion : " . $mensaje->getCorreo() . "-init";
                        var_dump($textLog);
                        $this->logger->warn($textLog);
                        $subject1 = "Fuente del Norte. Confirmación de mensaje recibido.";
                        $to1 = array($mensaje->getCorreo());
                        UtilService::sendEmail($this->container, $subject1, $to1, $this->container->get("templating")->render('MayaBundle:Email:confirmacionCliente.html.twig', array(
                            "mensaje" => $mensaje
                        )));
                        
                        $textLog = "EnviarMensajesPendientes: Notificando mensaje ID: " . $mensaje->getId() . " a las direcciones : " . $mailers . "-init";
                        var_dump($textLog);
                        $this->logger->warn($textLog);
                        $subject2 = "MSG_" .$now . ". PORTAL INTERNET. " . $this->container->getParameter("nombre_empresa_app");
                        UtilService::sendEmail($this->container, $subject2, $to2, $this->container->get("templating")->render('BackendBundle:Email:notifiacion.html.twig', array(
                            "mensaje" => $mensaje
                        )));
                        
                        $mensaje->setEnviado(true);
                        $em->persist($mensaje);
                        $em->flush();
                        $em->getConnection()->commit();

                    } catch (\RuntimeException $exc) {
                        $em->getConnection()->rollback();
                        $textLog = "EnviarMensajesPendientes: Error: " . $exc->getMessage();
                        var_dump($textLog);
                        $this->logger->warn($textLog);
                    } catch (\ErrorException $exc) {
                        $em->getConnection()->rollback();
                        $textLog = "EnviarMensajesPendientes: Error: " . $exc->getMessage();
                        var_dump($textLog);
                        $this->logger->warn($textLog);
                    } catch (\Exception $exc) {
                        $em->getConnection()->rollback();
                        $textLog = "EnviarMensajesPendientes: Error: " . $exc->getMessage();
                        var_dump($textLog);
                        $this->logger->warn($textLog);
                    }
                }
            }
        }
    }
    
    public function setScheduledJob(Job $job = null) {
        $this->job = $job;
        
        try {
            $this->notificarFacturasRecibidas();
        } catch (\Exception $ex) {
            $this->logger->warn("Ocurrio una exception en el proceso notificarFacturasRecibidas.");
            throw $ex;
        }
        
        try {
            $this->enviarMensajesPendientes();
        } catch (\Exception $ex) {
            $this->logger->warn("Ocurrio una exception en el proceso enviarMensajesPendientes.");
            throw $ex;
        }
        
        try {
            $this->enviarVoucherBoletos();
        } catch (\Exception $ex) {
            $this->logger->warn("Ocurrio una exception en el proceso enviarVoucherBoletos.");
            throw $ex;
        }
    }
}
