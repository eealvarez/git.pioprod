<?php

namespace Acme\MayaBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Acme\MayaBundle\Entity\EstadoCompra;
use Acme\BackendBundle\Services\UtilService;

/**
* @Route(path="/pasarela")
*/
class PasarelaController extends Controller
{
    /**
     * @Route(path="/init.html", name="pasarela-pago-init")
    */
    public function pasarelaInitAction(Request $request) {
        
        $session = $request->getSession();
        $lastIdCompraStep1 = $session->get("last_id_compra_step1");
        if($lastIdCompraStep1 !== null && trim($lastIdCompraStep1) !== ""){
            $session->remove("last_id_compra_step1");
            $session->set("last_id_compra_step2", $lastIdCompraStep1);
            
            $init = $request->query->get('i');
            if (is_null($init)) {
                $init = $request->request->get('i');
            }
            if($init === true || $init === "true"){
                $url = $this->container->getParameter("pasarela_init_url");
                $url .= "?success=" . $this->container->getParameter("pasarela_success_url");
                $url .= "&failed=" . $this->container->getParameter("pasarela_failed_url");
                return $this->redirect($url); //redirect temp
            }
        }
        
        return $this->redirect($this->generateUrl('_maya_homepage'), 301);
    }
    
    /**
     * @Route(path="/success.html", name="pasarela-pago-success")
    */
    public function pasarelaSuccessAction(Request $request) {
        //Hay que ver como proteger esta pagina con un parametro que retorne la pasarela.
        
        $session = $request->getSession();
        $idLastCompra = $session->get("last_id_compra_step2");
        $session->remove("last_id_compra_step2");
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $compra = null;
        try {
            
            if($idLastCompra !== null && trim($idLastCompra) !== ""){
                $compra = $this->getDoctrine()->getRepository('MayaBundle:Compra')->find($idLastCompra);
                $compra->setEstado($this->getDoctrine()->getRepository('MayaBundle:EstadoCompra')->find(EstadoCompra::PAGADA));
                $em->persist($compra);
                $em->flush();
                $em->getConnection()->commit();
            }
            
        } catch (\RuntimeException $exc) {
            $compra = null;
            $em->getConnection()->rollback();
            $message = $exc->getMessage();
            var_dump($message);
            $this->get("logger")->error("ERROR:" . $message);    
        } catch (\ErrorException $exc) {
            $compra = null;
            $em->getConnection()->rollback();
            $message = $exc->getMessage();
            var_dump($message);
            $this->get("logger")->error("ERROR:" . $message);
        } catch (\Exception $exc) {
            $compra = null;
            $em->getConnection()->rollback();
            $message = $exc->getMessage();
            var_dump($message);
            $this->get("logger")->error("ERROR:" . $message);
        }
        
        $em->getConnection()->beginTransaction();
        try {
            
            if($compra !== null){                
//                throw new \RuntimeException("a");
                $pasajeros = $compra->getListaPasajeros();
                $boletosArray = array();
                foreach ($pasajeros as $pasajero) {
                    $paquetes = $pasajero->getListaPaquetes();
                    foreach ($paquetes as $paquete) {
                        $boletos = $paquete->getListaBoletos();
                        foreach ($boletos as $boleto) {
                            $fullname = $pasajero->getNombreApellidos();
                            $nombres = explode(" ", $fullname);
                            $primerNombre = $nombres[0];
                            $segundoNombre = "";
                            $primerApellido = "";
                            $segundoApellido = "";
                            if(count($nombres) === 2){
                                $primerApellido = $nombres[1];
                            }
                            else if(count($nombres) === 3){
                                $primerApellido = $nombres[1];
                                $segundoApellido = $nombres[2];
                            }
                            else if(count($nombres) > 3){
                                $segundoNombre = $nombres[1];
                                $primerApellido = $nombres[2];
                                $segundoApellido = implode(array_slice($nombres, 3));
                            }
                            $boletosArray [] = array(
                                'idReservacion' => $boleto->getIdExternoReservacion(),
                                'idSubeEn' => $boleto->getSubeEn()->getId(),
                                'idBajaEn' => $boleto->getBajaEn()->getId(),
                                'idTarifa' => $boleto->getTarifa()->getId(),
                                'precioBase' => $boleto->getPrecio(),
                                'nacionalidad' => $pasajero->getNacionalidad()->getId(),
                                'tipoDocumento' => $pasajero->getTipoDocumento()->getId(),
                                'numeroDocumento' => $pasajero->getValorDocumento(),
                                'primerNombre' => $primerNombre,
                                'segundoNombre' => $segundoNombre,
                                'primerApellido' => $primerApellido,
                                'segundoApellido' => $segundoApellido,
                                'sexo' => $pasajero->getSexo() === null ? "" : $pasajero->getSexo()->getId(),
                                'fechaNacimiento' => $pasajero->getFechaNacimiento() === null ? "" : $pasajero->getFechaNacimiento()->getId(),
                                'fechaVencimientoDocumento' => $pasajero->getFechaVencimientoDocumento() === null ? "" : $pasajero->getFechaVencimientoDocumento()->getId(),
                                'detallado' => $pasajero->getDetallado()
                                
                            );
                        }
                    }
                }
                $idApp = $this->container->getParameter("id_empresa_app");
                $now = new \DateTime();
                $dataWeb = $now->format('Y-m-d'). "_system_web_".$idApp;
                $claveInterna = $this->container->getParameter("internal_sys_clave_interna");
                $tokenAutLocal = UtilService::encrypt($claveInterna, $dataWeb);

                $dataBoletos = array( "idWeb" => $idApp, "tokenAut" => $tokenAutLocal, "data" => json_encode($boletosArray));
                $postdata = http_build_query($dataBoletos);
                $options = array(
                      'http' => array(
                      'method'  => 'POST',
                      'content' => $postdata,
                      'header'  => "Content-type: application/x-www-form-urlencoded\r\n"));
                $context  = stream_context_create( $options );
                $url =  $this->container->getParameter("internal_sys_url") .
                        $this->container->getParameter("internal_sys_pref") .
                        "cb.json";
                $result = file_get_contents($url, false, $context );
                if($result !== null){
                    $result = json_decode($result);
                   if($result->data !== null){
                        foreach ($result->data as $boletoReservacion) {
                            $boletoParaActualizar = $this->getDoctrine()->getRepository('MayaBundle:Boleto')->findOneByIdExternoReservacion($boletoReservacion->idReservacion);
                            $boletoParaActualizar->setIdExternoBoleto($boletoReservacion->idBoleto);
                            $em->persist($boletoParaActualizar);
                        }
                    }else{
                        throw new \RuntimeException("m1No se generar el boleto en el sistema interno.");
                    }
                } else{
                    throw new \RuntimeException("m1No se generar el boleto en el sistema interno.");
                }
                
                $em->flush();
                $em->getConnection()->commit();
                return $this->render("MayaBundle:Pasarela:success.html.twig");
            }
           
            
        } catch (\RuntimeException $exc) {
            $em->getConnection()->rollback();
            $message = $exc->getMessage();
            var_dump($message);
            $this->get("logger")->error("ERROR:" . $message);    
        } catch (\ErrorException $exc) {
            $em->getConnection()->rollback();
            $message = $exc->getMessage();
            var_dump($message);
            $this->get("logger")->error("ERROR:" . $message);
        } catch (\Exception $exc) {
            $em->getConnection()->rollback();
            $message = $exc->getMessage();
            var_dump($message);
            $this->get("logger")->error("ERROR:" . $message);
        }
        
        return $this->pasarelaSuccessFailed($idLastCompra, $compra);
    }
    
    private function pasarelaSuccessFailed($idLastCompra, $compra = null) {
        return $this->render("MayaBundle:Pasarela:successFailed.html.twig", array(
            'idCompra' => $idLastCompra,
            'compra' => $compra
        ));
    }

    
    /**
     * @Route(path="/failed.html", name="pasarela-pago-failed")
    */
    public function pasarelaFailedAction(Request $request) {
        $session = $request->getSession();
        $idLastCompra = $session->get("last_id_compra_step2");
        $session->remove("last_id_compra_step2");
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $compra = null;
        try {
            if($idLastCompra !== null && trim($idLastCompra) !== ""){
                $compra = $this->getDoctrine()->getRepository('MayaBundle:Compra')->find($idLastCompra);
                if($compra !== null){
                    $compra->setEstado($this->getDoctrine()->getRepository('MayaBundle:EstadoCompra')->find(EstadoCompra::CANCELADA));
                    $em->persist($compra);
                    $em->flush();
                    $em->getConnection()->commit();
                }
            }
        
        } catch (\RuntimeException $exc) {
            $em->getConnection()->rollback();
            $message = $exc->getMessage();
            var_dump($message);
            $this->get("logger")->error("ERROR:" . $message);    
        } catch (\Exception $exc) {
            $em->getConnection()->rollback();
            $message = $exc->getMessage();
            var_dump($message);
            $this->get("logger")->error("ERROR:" . $message);
        } 
        
        return $this->render("MayaBundle:Pasarela:failed.html.twig", array(
            'idCompra' => $idLastCompra,
            'compra' => $compra
        ));
    }
    
    
    /**
     * @Route(path="/page.html", name="pasarela-pago-page")
    */
    public function basePageAction(Request $request) {
        $success = $request->query->get('success');
        if (is_null($success)) {
            $success = $request->request->get('success');
        }
        $failed = $request->query->get('failed');
        if (is_null($failed)) {
            $failed = $request->request->get('failed');
        }
        return $this->render("MayaBundle:Pasarela:pasarela.html.twig", array(
            'success' => $success,
            'failed' => $failed
        ));
    }
    
}