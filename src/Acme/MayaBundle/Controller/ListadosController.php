<?php

namespace Acme\MayaBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Acme\BackendBundle\Services\UtilService;
use Acme\MayaBundle\Entity\ConexionSimple;
use Acme\MayaBundle\Entity\ConexionCompuesta;
use Acme\MayaBundle\Form\ClienteAnonimoType;
use Acme\BackendBundle\Entity\UserOauth;
use Acme\MayaBundle\Entity\Cliente;
use Acme\MayaBundle\Entity\Pasajero;
use Acme\MayaBundle\Entity\Paquete;
use Acme\MayaBundle\Entity\Compra;
use Acme\MayaBundle\Entity\EstadoCompra;
use ReCaptcha\ReCaptcha;
use Acme\MayaBundle\Entity\EstadoConexion;
use Acme\MayaBundle\Entity\Boleto;
use Acme\MayaBundle\Entity\EstadoBoleto;

/**
* @Route(path="/listados")
*/
class ListadosController extends Controller{
    
    /**
     *   LISTA DE CONEXIONES
     *   
     * @Route(path="/listarConexiones.html", name="listarConexiones")
    */
    public function listarConexionesAction(Request $request) {
        $error = "";
        $result = "";
        $totalIda = 0;
        $totalRegreso = 0;
        $hayCompuestasIda = "0";
        $hayCompuestasRegreso = "0";
        
        try {
            $origen = $request->query->get('idOrigen');
            if (is_null($origen)) {
                $origen = $request->request->get('idOrigen');
            }
            if($origen === null){
                  $error = "Origen requerido";
            }
            $destino = $request->query->get('idDestino');
            if (is_null($destino)) {
                $destino = $request->request->get('idDestino');
            }
            if($destino === null){
                    $error = "Destino requerido";
                }
           
            $fechaIda = $request->get('fechaSalida');
            if (is_null($fechaIda)) {
                $fechaIda = $request->request->get('fechaSalida');
            }
                if($fechaIda === null){
                    $error = "Fecha de salida requerida";
                }
            $ida_regreso = $request->get('ida_regreso');
             if (is_null($ida_regreso)) {
                $ida_regreso = $request->request->get('ida_regreso');
            }
            $cantBoletos = $request->get('cantBoletos');
             if (is_null($cantBoletos)) {
                $cantBoletos = $request->request->get('cantBoletos');
            }
            $fechaRegreso = null;
            if($ida_regreso == true){
                $fechaRegreso = $request->get('fechaRegreso');
                if (is_null($fechaRegreso)) {
                    $fechaRegreso = $request->request->get('fechaRegreso');
                }
                    if($fechaRegreso === null){
                        $error = "Fecha de regreso requerida";
                    }
            }
            $viaje_directo = $request->query->get('viaje_directo');
            if (is_null($viaje_directo)) {
                $viaje_directo = $request->request->get('viaje_directo');
            }
           $mapConexionPrecioIda = array();
           $mapConexionPrecioRegreso = array();
           $mapConexionPrecioIntermediosIda = array();
           $mapConexionPrecioIntermediosRegreso = array();
           $mapTiempoIda = array();
           $mapTiempoRegreso = array();
           $mapIdConexionIda = array();
           $mapIdConexionRegreso = array();
           
           $con_compuestasIda = array();
           $con_compuestasRegreso = array();
           $clienteDetalladoIda = array();
           $clienteDetalladoRegreso = array();
           $mapTiempoIntermedioIda = array();
           $mapTiempoIntermedioRegreso = array();
           $mapIdConexionIntermediasIda = array();
           $mapIdConexionIntermediasRegreso = array();
           
           $repositorySimple = $this->getDoctrine()->getRepository('MayaBundle:ConexionSimple');
           $con_simplesIda = $repositorySimple->listarConexiones($origen, $destino, $fechaIda, $cantBoletos);
           
           
           if($viaje_directo == false || $viaje_directo == 'false'){
               $repositoryCompuesta = $this->getDoctrine()->getRepository('MayaBundle:ConexionCompuesta');
               $con_compuestasIda = $repositoryCompuesta->listarConexiones($origen, $destino, $fechaIda);
               
           }
            if($ida_regreso == true || $ida_regreso == 'true'){
                $con_simplesRegreso = $repositorySimple->listarConexiones($destino, $origen, $fechaRegreso, $cantBoletos);
                if($viaje_directo == false || $viaje_directo == 'false'){
                    $con_compuestasRegreso = $repositoryCompuesta->listarConexiones($destino, $origen, $fechaRegreso);
                }
            }
            $session = $this->getRequest()->getSession();
//            $session = new Session();
//            $session->start();
            
            if(count($con_simplesIda) > 0 || count($con_compuestasIda) > 0){
                $result .= "<div style='display: block;' class='col-lg-12 col-sm-12 col-md-12 col-xs-12 sinPadding'>";
                $result .= "<div style='display: block;' class='col-lg-9 col-sm-12 col-md-12 col-xs-12 sinPadding'>";
                $result .= "<div class='col-lg-12 col-xs-12 col-md-12 col-sm-12 sinPadding btn-group resultadosIda' data-toggle='buttons'>";
                    $result .= "<div class='table-responsive'>";
                        $result .= "<table class='table table-bordered'>";
                            $result .= "<tr class='primerTrResultados'>";
                                $result .= "<td class='col-sm-8 col-lg-8 textAsunto'><span class='titlePanelResultadosIda'></span></td>";
                                $result .= "<td class='col-sm-2 col-lg-2 textAsunto'>Econ&oacute;mica</td>";
                                $result .= "<td class='col-sm-3 col-lg-2 textAsunto'>Ejecutiva</td>";
                            $result .= "</tr>";
                            
                foreach($con_simplesIda as $data)
                {
                   $resultado = $this->save_Tiempo_Precio_Simple($data, $origen, $destino, $mapConexionPrecioIda, $mapIdConexionIda, $mapTiempoIda, $fechaIda, $cantBoletos);
                   if($resultado !== null){
                        $mapIdConexionIda = $resultado[0];
                        $mapConexionPrecioIda = $resultado[1];
                        $mapTiempoIda = $resultado[2];
                        $clienteDetalladoIda[$data->getId()] = $resultado[3];
                   }
                } 
                foreach($con_compuestasIda as $data2)
                {
                    if($this->conexionCompuestaAsientosDisponibles($data2, $cantBoletos) === true){
                        $hayCompuestasIda = "1";
                        $resultado = $this->save_Tiempo_Precio_Compuesta($data2, $mapTiempoIntermedioIda, $mapTiempoIda, $mapConexionPrecioIda, $mapConexionPrecioIntermediosIda, $mapIdConexionIda, $mapIdConexionIntermediasIda, $fechaIda, $cantBoletos);
                        if($resultado !== null){
                            $mapIdConexionIda = $resultado[0];
                            $mapTiempoIntermedioIda = $resultado[1];
                            $mapTiempoIda = $resultado[2];
                            $mapConexionPrecioIda = $resultado[3];
                            $mapConexionPrecioIntermediosIda = $resultado[4];
                            $clienteDetalladoIda[$data2->getId()] = $resultado[5];
                            $mapIdConexionIntermediasIda = $resultado[6];
                        }
                    }
                }
                $session->set("idSession","3");
                $session->remove("preciosSimplesIda");
                $session->set("preciosSimplesIda", $mapConexionPrecioIda);
                if(!empty($mapConexionPrecioIntermediosIda)){
                    $session->remove("preciosCompuestasIda");
                    $session->set("preciosCompuestasIda", $mapConexionPrecioIntermediosIda);
                }
                $listaConexionesIda = $this->ordenarConexionesPorHorarios($mapTiempoIda);
                foreach ($listaConexionesIda as $key => $tiempos)
                {
                    $idIntermedios = null;
                    $tiemposIntermedios = null;
                    $preciosIntermedios = null;
                    if($mapIdConexionIda[$key] instanceof ConexionCompuesta){
                        $idIntermedios = $mapIdConexionIntermediasIda[$key];
                        $tiemposIntermedios = $mapTiempoIntermedioIda[$key];
                        $preciosIntermedios = $mapConexionPrecioIntermediosIda[$key];
                    }
                    
                    $result = $this->crearRowConexion($mapIdConexionIda[$key], $idIntermedios, $mapConexionPrecioIda[$key], $preciosIntermedios, $tiempos, $tiemposIntermedios, $clienteDetalladoIda[$key], $origen, $destino, $totalIda, $result, $fechaIda, false);
                    $totalIda ++;
                }
                $result .= "</table></div></div>";
            }
            
            if(count($con_simplesRegreso) > 0 || count($con_compuestasRegreso) > 0 ){
                $result .= "<div class='col-lg-12 col-xs-12 col-md-12 col-sm-12 sinPadding btn-group resultadosRegreso' data-toggle='buttons'>";
                $result .= "<div class='table-responsive'>";
                $result .= "<table class='table table-bordered'>";
                $result .= "<tr class='primerTrResultados'>";
                $result .= "<td class='col-sm-8 col-lg-8 textAsunto'><span class='titlePanelResultadosRegreso'></span></td>";
                $result .= "<td class='col-sm-2 col-lg-2 textAsunto'>Econ&oacute;mica</td>";
                $result .= "<td class='col-sm-3 col-lg-2 textAsunto'>Ejecutiva</td></tr>";
                foreach($con_simplesRegreso as $data)
                {
                   $resultado = $this->save_Tiempo_Precio_Simple($data, $destino, $origen, $mapConexionPrecioRegreso, $mapIdConexionRegreso, $mapTiempoRegreso, $fechaRegreso, $cantBoletos);
                   if($resultado !== null){
                        $mapIdConexionRegreso = $resultado[0];
                        $mapConexionPrecioRegreso = $resultado[1];
                        $mapTiempoRegreso = $resultado[2];
                        $clienteDetalladoRegreso[$data->getId()] = $resultado[3];
                   }
                }
                foreach($con_compuestasRegreso as $data2)
                {
                    if($this->conexionCompuestaAsientosDisponibles($data2, $cantBoletos) === true){
                        $hayCompuestasRegreso = "1";
                        $resultado = $this->save_Tiempo_Precio_Compuesta($data2, $mapTiempoIntermedioRegreso, $mapTiempoRegreso, $mapConexionPrecioRegreso, $mapConexionPrecioIntermediosRegreso, $mapIdConexionRegreso, $mapIdConexionIntermediasRegreso, $fechaRegreso, $cantBoletos);
                        if($resultado !== null){
                            $mapIdConexionRegreso = $resultado[0];
                            $mapTiempoIntermedioRegreso = $resultado[1];
                            $mapTiempoRegreso = $resultado[2];
                            $mapConexionPrecioRegreso = $resultado[3];
                            $mapConexionPrecioIntermediosRegreso = $resultado[4];
                            $clienteDetalladoRegreso[$data2->getId()] = $resultado[5];
                            $mapIdConexionIntermediasRegreso = $resultado[6];
                        }
                    }
                }
                $listaConexionesRegreso = $this->ordenarConexionesPorHorarios($mapTiempoRegreso);
                $session->remove("preciosSimplesRegreso");
                $session->set("preciosSimplesRegreso", $mapConexionPrecioRegreso);
                if(!empty($mapConexionPrecioIntermediosRegreso)){
                    $session->remove("preciosCompuestasRegreso");
                    $session->set("preciosCompuestasRegreso", $mapConexionPrecioIntermediosRegreso);
                }       
                foreach ($listaConexionesRegreso as $key => $tiempos)
                {
                    $idIntermedios = null;
                    $tiemposIntermedios = null;
                    $preciosIntermedios = null;
                    if($mapIdConexionRegreso[$key] instanceof ConexionCompuesta){
                        $idIntermedios = $mapIdConexionIntermediasRegreso[$key];
                        $tiemposIntermedios = $mapTiempoIntermedioRegreso[$key];
                        $preciosIntermedios = $mapConexionPrecioIntermediosRegreso[$key];
                    }
                    $result = $this->crearRowConexion($mapIdConexionRegreso[$key],$idIntermedios, $mapConexionPrecioRegreso[$key], $preciosIntermedios, $tiempos, $tiemposIntermedios, $clienteDetalladoRegreso[$key], $destino, $origen, $totalRegreso, $result, $fechaRegreso, true);
                    $totalRegreso ++;
                    
                }
                $result .= "</table></div></div>";
                
            }
               $token = $this->getToken();
               $result .= "</div><div class='col-lg-3 col-lg-offset-0 col-xs-12 col-md-6 col-sm-offset-6 col-sm-6 col-md-offset-6' id='tr_1' style='padding-right:0;padding-bottom: 30px;'>";
                $result .= "<div class='timerSesion'><strong>Precio del viaje</strong></div>";
                $result .= "<div>";
                    $result .="<div class='panel panel-default' id='resumenHorario'>";
                        $result .="<div class='panel-body'>";
                            $result .="<div class='detallesViaje'></div>";
                            $result .="<div class='row precioViaje'></div>";
                    $result .="</div></div></div>";
                $result .="</div>";
               
               $result .= "<input type='hidden' class='token' value='".$token."'></input>";
               $result .= "<input type='hidden' class='idApp' value='".$this->container->getParameter("id_empresa_app")."'></input>";
              
        }catch (\RuntimeException $exc) {
            $this->get('logger')->error($exc->getMessage());
        }catch (\ErrorException $exc) {
            $this->get('logger')->error($exc->getMessage());
        }catch (\Exception $exc) {
            $this->get('logger')->error($exc->getMessage());
        }
        
        $result .= "<input type='hidden' name='totalConexionesRegreso' id='totalConexionesRegreso' value='".$totalRegreso."'></input>";
        $result .= "<input type='hidden' name='totalConexionesIda' id='totalConexionesIda' value='".$totalIda."'></input>";
        $result .= "<input type='hidden' name='hayCompuestasIda' value='".$hayCompuestasIda."'></input>";
        $result .= "<input type='hidden' name='hayCompuestasRegreso' value='".$hayCompuestasRegreso."'></input>";
        
        return new Response($result);
    }
    
     /**
     * 
     *  LISTA DE CONEXIONES 
     * 
     * @Route(path="/listarEstaciones.json", name="ajaxListarEstaciones") 
    */
    public function listarEstacionesAction() {
        $rows = array();
        try {
            $repository = $this->get('doctrine')->getManager()->getRepository('MayaBundle:Estacion');
            $result = $repository->getEstacionesActivasExlusion($estacionExcluida);
           
               
            foreach($result['items'] as $estacion)
                {
                   $item = array(
                        'id' => $estacion->getId(),
                        'nombre' => $estacion->getNombre(),
                    );
                    $rows[] = $item;
                }
        } catch (\Exception $exc) {
            $this->get("logger")->warn("ERROR:" . $exc->getTraceAsString());
        }

        $response = new JsonResponse();
        $response->setData(array(
            'rows' => $rows
        ));
        return $response;
    }
    
    public function crearRowConexion($con, $mapIdConexionIntermediasIda, $mapConexionPrecios, $mapPreciosIntermedios, $mapTiemposGeneral, $mapTiemposIntermedios, $clienteDetallado, $origen, $destino, $totalIda, $result, $fechaIda, $regreso){
       
        $cantidadDiasViaje = "";
        $horaSalida = "----";
        $horaLlegada = "----";
        
        $diff = $this->diasDiferenciaFechas($mapTiemposGeneral['horaSalida'], $mapTiemposGeneral['horaLlegada']);
        if($diff !== 0)
            $cantidadDiasViaje = "Aprox. ".$diff." horas de viaje. ";
        
        if($mapTiemposGeneral['horaSalida'] !== "----"){
            $horaSalida = date("g:i a",strtotime($mapTiemposGeneral['horaSalida']));
            $mapTiemposGeneral['horaSalida'] = $horaSalida;
         }
        if($mapTiemposGeneral['horaLlegada'] !== "----"){
             $horaLlegada = date("g:i a",strtotime($mapTiemposGeneral['horaLlegada']));
             $mapTiemposGeneral['horaLlegada'] = $horaLlegada;
        }
        
        $estacionOrigen = $this->get('doctrine')->getManager()->getRepository('MayaBundle:Estacion')->find($origen);
        $estacionDestino = $this->get('doctrine')->getManager()->getRepository('MayaBundle:Estacion')->find($destino);
        $claseBus = ""; // cuando es compuesta no se muestra el tipo de bus porque puede variar por cada itinerario, se muestra en los detalles
        $tipoConexion = 1; //0 - conexion simple y 1 - conexion compuesta
        $idConexion = $con->getId(); // si es simple coincidara con el id de las salidas del sistema de boletos (externo) si no sera el id interno de la conexion compuesta
        $idPadre = $con->getId();
        $cantidad_asientos = 0;
        $detalles = null;
        $tiemposIntermedias = null;
        if($mapTiemposIntermedios !== null){
            $detalles = json_encode($mapTiemposIntermedios);
        }
        $preciosBoletos = null;
        if($mapPreciosIntermedios !== null){
            $preciosBoletos = json_encode($mapPreciosIntermedios);
        }

        if(!is_null($con)){
            if($con instanceof ConexionSimple){
              $claseBus = $con->getTipoBus()->getClase();
              $tipoConexion = 0;
              $idConexion = $con->getIdExterno();
              $idPadre = $con->getId();
              $cantidad_asientos = $con->getTipoBus()->getTotalAsientos();
              $detalles = json_encode($mapTiemposGeneral);
        }
        $idsIntermedias = null;
        if($mapIdConexionIntermediasIda !== null){
            $idsIntermedias = array();
            foreach ($mapIdConexionIntermediasIda as $key => $value) {
                $idsIntermedias [] = $key;
            }
           $idsIntermedias = json_encode($idsIntermedias, true);
        }

        $cantidad_paradas = $this->getCantidadParadas($con);
        $cant_precios = count($mapConexionPrecios);
        $detallesCliente = 0;
        if($clienteDetallado === true)
            $detallesCliente = 1;
        $nameRadio = "claseIda"; 
             if($regreso == true){
                 $nameRadio = "claseRegreso";
             }
         if($cant_precios > 0 || $cant_precios > 1){
         $result .= "<tr>";

         $result .= "<td>";
             $result .= "<div class='table-responsive info' style='border:0;'>";
                 $result .= "<table class='table'>";
                 $result .= "<tr><td style='width: 52%;'>";
                     $result .= $horaSalida."   ".$estacionOrigen->getNombre()."   ";
                     $result .= "<a class='siglaEstacion linkApp' style='text-decoration:none;' data-loading-text='Cargando...' href='".$this->generateUrl('ajaxGetInfoEstacion', array( 'id' => $estacionOrigen->getId()))."'>(".$estacionOrigen->getAlias().")</a>";
                 $result .= "</td>";
                 if($claseBus != ""){
                     $result .= "<td>";
                     $result .= $claseBus->getNombre();
                     $result .= "</td>";
                 }

             $result .= "</tr>";

             $result .= "<tr>";
                 $result .= "<td style='width: 52%;'>";
                     $result .= $horaLlegada."   ".$estacionDestino->getNombre()."   ";
                      $result .= "<a class='siglaEstacion linkApp' style='text-decoration:none;' data-loading-text='Cargando...' href='".$this->generateUrl('ajaxGetInfoEstacion', array( 'id' => $estacionDestino->getId()))."'>(".$estacionDestino->getAlias().")</a>";
                 $result .= "</td>";
                 $result .= "<td>";
                     $result .= $cantidad_paradas." parada(s)";
                 $result .= "</td>";
                 if($con instanceof ConexionCompuesta){
                     $result .= "<td>";
                     $result .= "<a href='#' data-loading-text='Cargando...' class='detallesConexion' linkApp data-idsintermedios='".$idsIntermedias."' data-tiemposintermedios='".$detalles."' data-id='".$con->getId()."'><span>Ver detalles</span></a>";
                     $result .= "</td>";
                 }
             $result .= "</tr>";
             
        $result .= "</table></div>";
        $result .= "<div style='font-size:12px;'>";
            if($con instanceof ConexionSimple){
                    $result .= " RUTA:  ".$con->getItinerario()->getRuta()->getEstacionOrigen()->getNombre()." - ".$con->getItinerario()->getRuta()->getEstacionDestino()->getNombre();
            }
        $result .= "</div>";
        $result .= "<div style='font-size:12px;'>";
            if($diff !== 0){
                $result .= $cantidadDiasViaje;
            }
            if($con instanceof ConexionSimple){
//                    if($estacionOrigen->getId() !== $con->getItinerario()->getRuta()->getEstacionOrigen()->getId()){
                    $result .= "El bus sale de:  ".$con->getItinerario()->getRuta()->getEstacionOrigen()->getNombre()." - ".$con->getFechaViaje()->format("d/m/Y")." - ".date("g:i a",strtotime($con->getFechaViaje()->format("H:i")));
//                      }
            }
        $result .= "</div>";
        $result  .= "</td>";



         $result .= "<td class='precios'>";
        
            if($con instanceof ConexionSimple){
                $precioUnitario = $mapConexionPrecios['A'];
                $precioTotal = $mapConexionPrecios['totalA'];
                $idTarifa = $mapConexionPrecios['idTarifaA'];
            }
            else{
                $precioUnitario = $mapConexionPrecios[0]['precioUnitario'];
                $precioTotal = $mapConexionPrecios[0]['tarifaValor'];
                $idTarifa = $mapConexionPrecios[0]['idTarifa'];
            }
            if($precioUnitario !== null){
                $result .=  "<label class='btn btn-default preciosHorarios'>";
                $result .= "<input type='radio' class='col-lg-2 col-lg-offset-5 col-sm-2 col-sm-offset-5 col-md-2 col-md-offset-5 col-xs-2 col-xs-offset-5' name='".$nameRadio."' value='".$precioTotal."' data-idpadre='".$idPadre."' data-preciounitario='".$precioUnitario."' data-tarifa='".$idTarifa."' data-preciosintermedios='".$preciosBoletos."' data-tiemposintermedios='".$detalles."' data-clientedetallado='".$detallesCliente."' data-claseasiento='A' data-detalles='".UtilService::getDateString(strtotime($fechaIda))." - ". $horaSalida."' data-idconexion='".$idConexion."' data-tipoconexion='".$tipoConexion."' data-asientos='".$cantidad_asientos."'/><span style='margin-right:5px;' class='glyphicon glyphicon-ok' aria-hidden='true'></span>";
                $result .= "Q ". $precioTotal;
                $result .= "</label>";
            }
            else{
                $result .=  "<span class='glyphicon glyphicon-ban-circle precioBloqueado' title='La salida no está disponible en estos momentos.'></span>";
            }
        $result .= "</td>";
        $existeB = true;
        $result .= "<td class='precios'>";
        if($cant_precios > 1){
                if($con instanceof ConexionSimple){
                    $precioUnitario = $mapConexionPrecios['B'];
                    if($precioUnitario === null)
                        $existeB = false;
                    $precioTotal = $mapConexionPrecios['totalB'];
                    $idTarifa = $mapConexionPrecios['idTarifaB'];
                }
                else{
                    $precioUnitario = $mapConexionPrecios[1]['precioUnitario'];
                    $precioTotal = $mapConexionPrecios[1]['tarifaValor'];
                    $idTarifa = $mapConexionPrecios[1]['idTarifa'];
                    if($idTarifa === null){
                        $existeB = false;
                    }
                    else {
                        $existeB = true;
                    }
                }
                if($existeB){
                    if($precioUnitario != null){
                        $result .=  "<label class='btn btn-default preciosHorarios'>";
                        $result .= "<input type='radio' class='col-lg-2 col-lg-offset-5 col-sm-2 col-sm-offset-5 col-md-2 col-md-offset-5 col-xs-2 col-xs-offset-5' name='".$nameRadio."' value='".$precioTotal."' data-idpadre='".$idPadre."' data-preciounitario='".$precioUnitario."' data-tarifa='".$idTarifa."' data-preciosintermedios='".$preciosBoletos."' data-tiemposintermedios='".$detalles."' data-clientedetallado='".$detallesCliente."' data-claseasiento='B' data-detalles='".UtilService::getDateString(strtotime($fechaIda))." - ". $horaSalida."' data-idconexion='".$idConexion."' data-tipoconexion='".$tipoConexion."' data-asientos='".$cantidad_asientos."'/><span style='margin-right:5px;' class='glyphicon glyphicon-ok' aria-hidden='true'></span>";
                        $result .= "Q ". $precioTotal;
                        $result .= "</label>";
                    }
                    else{
                        $result .=  "<span class='glyphicon glyphicon-ban-circle precioBloqueado' title='La salida no está disponible en estos momentos.'></span>";
                    }
                }
                
            }
         $result .= "</td>";

         $result .= "</tr>";

        }
        $totalIda ++;
        }
        return $result;
    }
    public function order($a, $b) {
        if($a['horaSalida'] == $b['horaSalida']) {
            return 0;
        }
        return ($a['horaSalida'] < $b['horaSalida']) ? -1 : 1;
    }
    
    public function ordenarConexionesPorHorarios($mapConexionTiempo){
        uasort($mapConexionTiempo, array($this,'order'));
        return  $mapConexionTiempo;
    }
    
    
    public function getTiemposConexion($conexion, $mapTiempo, $intermedios){
        $arrayTiempos = $mapTiempo;
        if($conexion instanceof ConexionCompuesta && $intermedios == true){
            $arrayTiempos = $mapTiempo[0];
        }
            foreach ($arrayTiempos as $idCone => $tiempos) {
                if($idCone == $conexion->getId())
                    return $tiempos;
            }
        return null;
    }
    
    public function getCantidadParadas($conexion){
       if($conexion instanceof ConexionSimple)
           return 0;
       return count($conexion->getListaConexionItem())-1;
    }
    
     /**
     *   
     * @Route(path="/conSimples.json", name="conSimples")
    */
    public function getInfoConexionSimple(Request $request){
        $rows = array();
        $error = null;
        $asientosOcupados = array();
        try {
            $data = $request->query->get('data');
            if (is_null($data)) {
                $data = $request->request->get('data');
            }
            
             $origen = $request->query->get('origen');
            if (is_null($origen)) {
                $origen = $request->request->get('origen');
            }
            
            $destino = $request->query->get('destino');
            if (is_null($destino)) {
                $destino = $request->request->get('destino');
            }
            $jsonData = json_decode($data, true);
            $conSimples = array();
            $conCompuestas = array();
            $idSalidas = array();
            foreach ($jsonData as $value) {
                $id = $value['idSalida'];
                $tipo = $value['tipo'];
                $regreso = $value['regreso'];
                if($tipo === 'simple'){
                    $simple = $this->get('doctrine')->getManager()->getRepository('MayaBundle:ConexionSimple')->findOneByIdExterno($id);
                    $asientos = array();
                    $senales = array();
                    $conSimple = array();
                    if($simple !== null){
                        $listaAsiento = $simple->getTipoBus()->getListaAsiento();
                        $clasePrimerAsiento = $listaAsiento[0]->getClase()->getNombre();
                        $clases = array();
                        $fullClases = false;
                        $clases[] = $clasePrimerAsiento;
                        foreach ($listaAsiento as $asiento) {
                            if($fullClases === false && $asiento->getClase()->getNombre() !== $clasePrimerAsiento){
                                $clases[] = $asiento->getClase()->getNombre();
                                $fullClases = true;
                            }
                            $item1 = array(
                                'id' => $asiento->getId(),
                                'numero' => $asiento->getNumero(),
                                'clase' => $asiento->getClase()->getNombre(),
                                'coordenadaX' => $asiento->getCoordenadaX(),
                                'coordenadaY' => $asiento->getCoordenadaY(),
                                'nivel2' => $asiento->getNivel2()
                            );
                            $asientos[] = $item1;
                        }
                        foreach ($simple->getTipoBus()->getListaSenal() as $senal) {
                            $item2 = array(
                                'id' => $senal->getId(),
                                'tipo' => $senal->getTipo()->getNombre(),
                                'coordenadaX' => $senal->getCoordenadaX(),
                                'coordenadaY' => $senal->getCoordenadaY(),
                                'nivel2' => $senal->getNivel2()
                            );
                            $senales[] = $item2;
                        }
                        $item = array(
                            'idExterno' => $simple->getIdExterno(),
                            'id' => $simple->getId(),
                            'asientos' => $asientos,
                            'senales' => $senales,
                            'clasesAsientos' => $clases,
                            'nivel2' => $simple->getTipoBus()->getNivel2(),
                            'regreso' => $regreso
                        );
                        $conSimples[] = $item;
                        $idSalida = array(
                            "idSalida" => $simple->getIdExterno(),
                        );
                        $idSalidas [] = $idSalida;
                   }
                }
                    else{
                        $compuesta = $this->get('doctrine')->getManager()->getRepository('MayaBundle:ConexionCompuesta')->find($id);
                        if($compuesta == null)
                            return null;
                        $listaConexionesItem = $compuesta->getListaConexionItem();
                        $conItems = array();
                        $conSimpleComp = array();
                        $detallesConexion = array();
                        foreach($listaConexionesItem as $conexionItem)
                         {
                            $conexionSimple = $conexionItem->getConexionSimple();
                            $asientos = array();
                            $senales = array();
                            $listaAsiento = $conexionSimple->getTipoBus()->getListaAsiento();
                            $clasePrimerAsiento = $listaAsiento[0]->getClase()->getNombre();
                            $clases = array();
                            $clases[] = $clasePrimerAsiento;
                            $fullClases = false;
                            
                            foreach ($listaAsiento as $asiento) {
                                if($fullClases === false && $asiento->getClase()->getNombre() !== $clasePrimerAsiento){
                                    $clases[] = $asiento->getClase()->getNombre();
                                    $fullClases = true;
                                }
                                $asientos[] = array(
                                    "id" => $asiento->getId(),
                                    "nivel2" => $asiento->getNivel2(),
                                    "numero" => $asiento->getNumero(),
                                    "clase" => $asiento->getClase()->getNombre(),
                                    "coordenadaX" => $asiento->getCoordenadaX(),
                                    "coordenadaY" => $asiento->getCoordenadaY(),
                                );
                                }
                                foreach ($conexionSimple->getTipoBus()->getListaSenal() as $senal) {
                                    $senales[] = array(
                                        "id" => $senal->getId(),
                                        "nivel2" => $senal->getNivel2(),
                                        "tipo" => $senal->getTipo()->getNombre(),
                                        "coordenadaX" => $senal->getCoordenadaX(),
                                        "coordenadaY" => $senal->getCoordenadaY(),
                                );
                                }
                            $item = array(
                                'idExterno' => $conexionSimple->getIdExterno(),
                                'id' => $conexionSimple->getId(),
                                'asientos' => $asientos,
                                'senales' => $senales,
                                'clasesAsientos' => $clases,
                                'nivel2' => $conexionSimple->getTipoBus()->getNivel2(),
                            );
                            $idSalida = array(
                                "idSalida" => $conexionSimple->getIdExterno(),
                            );
                           $conItems[] = $item; 
                           $idSalidas [] = $idSalida;
                    }
                    $conSimpleComp['items'] = $conItems;
                    $conSimpleComp['regreso'] = $regreso;
                    $conCompuestas[$id] = $conSimpleComp;
                 }
                }  
            
                $rows['simp'] = $conSimples;
                $rows['comp'] = $conCompuestas; 

                $idApp = $this->container->getParameter("id_empresa_app");
                $now = new \DateTime();
                $dataWeb = $now->format('Y-m-d'). "_system_web_".$idApp;
                $claveInterna = $this->container->getParameter("internal_sys_clave_interna");
                $tokenAutLocal = UtilService::encrypt($claveInterna, $dataWeb);

                $dataAsientos = array( "idWeb" => $idApp, "tokenAut" => $tokenAutLocal, "data" => json_encode($idSalidas));

                $postdata = http_build_query($dataAsientos);
                $options = array(
                      'http' => array(
                      'method'  => 'POST',
                      'content' => $postdata,
                      'header'  => "Content-type: application/x-www-form-urlencoded\r\n"));
                $context  = stream_context_create( $options );
                $url =  $this->container->getParameter("internal_sys_url") .
                        $this->container->getParameter("internal_sys_pref") .
                        "is.json";
                $result = file_get_contents($url, false, $context );
                
                if($result !== null){
                    $result = json_decode($result);
                    $asientosOcupados = $result->data;
                }
            }
             catch (\ContextErrorException $exc) {
                $this->get("logger")->warn("ERROR:" . $exc->getTraceAsString());
                $error = "Error en el servidor";
            }
            catch (\ErrorException $exc) {
                $this->get("logger")->warn("ERROR:" . $exc->getTraceAsString());
                $error = "Error en el servidor";
            }
            catch (\Exception $exc) {
                $this->get("logger")->warn("ERROR:" . $exc->getTraceAsString());
                $error = "Error en el servidor";
            }
           
            $response = new JsonResponse();
            $response->setData(array(
                'data' => $rows,
                'asientosOcupados' => $asientosOcupados,
                'error' => $error
            ));
            
            return $response;
            
    }
    
    public function getToken(){
        $now = new \DateTime();
        $dataWeb = $now->format('Y-m-d'). "_system_web_".$this->container->getParameter("id_empresa_app");
        $claveInterna = $this->container->getParameter("internal_sys_clave_interna");
        $tokenAutLocal = \Acme\BackendBundle\Services\UtilService::encrypt($claveInterna, $dataWeb);
        return $tokenAutLocal;
    }
    
    public function save_Tiempo_Precio_Compuesta($conexion, $mapConexionTiempos, $mapConexionTiemposGeneral, $mapConexionPrecios, $mapConexionPreciosIntermedios, $mapConexionIds, $mapConexionesIdIntermedias, $fechaIda, $cantPasajeros){
        $itinerarioCompuesto = $conexion->getItinerarioCompuesto();
        $listaConexionesItem = $conexion->getListaConexionItem();
        
        $tiemposArrayIntermediasCompuesta = array();
        $conexionesSimplesMap = array();
        $clienteDetallado = false;
        $countConex = 0;
        
        $precioA = 0;
        $precioB = 0;
        $claseAsientoUnica = array();
        $bajaEnAnterior = null;
        $preciosIntermediosBoletos = array();
        
        //comprobar si la conexion debe incluirse por las fechas
        
        $fechaIdaInit = \DateTime::createFromFormat('Y-m-d', $fechaIda);
        
        $fechaIdaInit->setTime(0, 0, 0);
        $fechaActual = new \DateTime();
        if(UtilService::compararFechas($fechaIdaInit, $fechaActual) === 0){
            $fechaActual->modify("+20 minutes");
            $horaActual = $fechaActual->format("H");
            $minutoActual = $fechaActual->format("i");
            $fechaIdaInit->setTime($horaActual, $minutoActual, 0);
        } 
        $fechaIdaEnd = clone $fechaIdaInit;
        $fechaIdaEnd->setTime(23, 59, 59);
        if($conexion->getFechaViaje() > $fechaIdaEnd || $conexion->getFechaViaje() < $fechaIdaInit){
            return null;
        }
        
        foreach ($listaConexionesItem as $conexionItem) {
                //salvar precios
                $itinerarioItem = $conexionItem->getItinerarioItem();
                $destinoItem = $itinerarioItem->getBajaEn()->getId();
                
                $itinerario = $conexionItem->getConexionSimple()->getItinerario();
                $claseBus = $itinerario->getTipoBus()->getClase();
                $origenItem = $bajaEnAnterior;
                if($countConex == 0){
                    $origenItem = $itinerarioCompuesto->getEstacionOrigen()->getId(); 
                }
                $preciosTarifaA = $this->getDoctrine()->getRepository('MayaBundle:TarifaBoleto')->getTarifaBoleto($origenItem, $destinoItem, $claseBus->getId(), 1, $fechaIda);
               
                $tipoBusActualizado = $conexionItem->getConexionSimple()->getTipoBus();
                $preciosTarifaB = null;
                if($tipoBusActualizado->existeAsientoB() === true){
                    $preciosTarifaB = $this->getDoctrine()->getRepository('MayaBundle:TarifaBoleto')->getTarifaBoleto($origenItem, $destinoItem, $claseBus->getId(), 2, $fechaIda);
                }
                
                if($preciosTarifaA != null){
                        $claseAsientoUnica[] = array('nombre'=>$preciosTarifaA['nombreA'], 'id'=>$preciosTarifaA['idA'], 'idTarifa'=>$preciosTarifaA['idTarifa']);
                        $precioA += floatval($preciosTarifaA['precioUnitario']);
                }
                else{
                    if($preciosTarifaB != null){
                        $claseAsientoUnica[] = array('nombre'=>$preciosTarifaB['nombreA'], 'id'=>$preciosTarifaB['idA'], 'idTarifa'=>$preciosTarifaB['idTarifa']);
                        $precioA += floatval($preciosTarifaB['precioUnitario']);
                    }
                }
                if($preciosTarifaB != null){
                    $claseAsientoUnica[] = array('nombre'=>$preciosTarifaB['nombreA'], 'id'=>$preciosTarifaB['idA'], 'idTarifa'=>$preciosTarifaB['idTarifa']);
                    $precioB += floatval($preciosTarifaB['precioUnitario']);
                }
                else{
                    if($preciosTarifaA != null){
                        $claseAsientoUnica[] = array('nombre'=>$preciosTarifaA['nombreA'], 'id'=>$preciosTarifaA['idA'], 'idTarifa'=>$preciosTarifaA['idTarifa']);
                        $precioB += floatval($preciosTarifaA['precioUnitario']);
                    }
                }
                $preciosBoletos = array();
                $preciosBoletos["A"] = $preciosTarifaA === null ? $preciosTarifaB['precioUnitario'] : $preciosTarifaA['precioUnitario'];
                $preciosBoletos["B"] = $preciosTarifaB === null ? $preciosTarifaA['precioUnitario'] : $preciosTarifaB['precioUnitario'];
                $preciosBoletos['idTarifaA'] = $preciosTarifaA === null ? $preciosTarifaB['idTarifa'] : $preciosTarifaA['idTarifa'];
                $preciosBoletos['idTarifaB'] = $preciosTarifaB === null ? $preciosTarifaA['idTarifa'] : $preciosTarifaB['idTarifa'];
                
                $firstClase = $claseAsientoUnica[0];
                $fullClaseAsiento = false;
                foreach ($claseAsientoUnica as $clase) {
                    if ($clase != $firstClase) {
                        $fullClaseAsiento = true;
                    }
                }
                $arrayPrecios = array();
                $preciosClaseA = array();
                $preciosClaseB = array();
                if($fullClaseAsiento == true){
                    $preciosClaseA['idA'] = 1;
                    $preciosClaseA['nombreA'] = 'A';
                    $preciosClaseA['precioUnitario'] = $precioA;
                    $preciosClaseA['tarifaValor'] = $precioA * $cantPasajeros;
                    $preciosClaseA['idTarifa'] = $preciosTarifaA['idTarifa'];
                    
                    $preciosClaseB['idA'] = 2;
                    $preciosClaseB['nombreA'] = 'B';
                    $preciosClaseB['precioUnitario'] = $precioB;
                    $preciosClaseB['tarifaValor'] = $precioB * $cantPasajeros;
                    $preciosClaseB['idTarifa'] = $preciosTarifaB['idTarifa'];
                     
                    $arrayPrecios [] = $preciosClaseA;
                    $arrayPrecios [] = $preciosClaseB;
                }
                else{
                    $preciosClaseA['idA'] = $firstClase['id'];
                    $preciosClaseA['nombreA'] = $firstClase['nombre'];
                    $preciosClaseA['precioUnitario'] = $precioA;
                    $preciosClaseA['tarifaValor'] = $precioA * $cantPasajeros;
                    $preciosClaseA['idTarifa'] = $preciosTarifaA['idTarifa'];
                    $arrayPrecios [] = $preciosClaseA;
                }
                $preciosIntermediosBoletos [$conexionItem->getConexionSimple()->getIdExterno()] = $preciosBoletos;
                
                $mapConexionPrecios[$conexion->getId()] = $arrayPrecios;
                $mapConexionPreciosIntermedios[$conexion->getId()] = $preciosIntermediosBoletos;
                
                //salvar tiempos de cada itinerario de la conexion
                $ruta = $itinerario->getRuta();
                
                $estacOrigen = $this->getDoctrine()->getRepository('MayaBundle:Estacion')->find($origenItem);
                $estacDestino = $this->getDoctrine()->getRepository('MayaBundle:Estacion')->find($destinoItem);
                $tiempos = $this->getDoctrine()->getRepository('MayaBundle:Tiempo')->getTiempoConexionCompleta($origenItem, $destinoItem, $ruta->getCodigo(), $claseBus->getId());
                
                $tiempoOrigen = 0; 
                $tiempoDestino = 0;
                $horaSalida = "";
                $horaLlegada = "";
                 
                $fechaViaje = $conexionItem->getConexionSimple()->getFechaViaje();
                $fechaSalida = clone $fechaViaje;
                $fechaLlegada = clone $fechaViaje;
                if(count($tiempos) > 0){
                    $tiempoOrigen = $tiempos[0]["origen"];
                    $tiempoDestino = $tiempos[0]["destino"];
                    $fechaSalida = $fechaSalida->modify("+".$tiempoOrigen." minutes");
                    
                    $fechaLlegada = $fechaLlegada->modify("+".$tiempoDestino." minutes");
                    $horaSalida = $fechaSalida->format('H:i');
                    $horaLlegada = $fechaLlegada->format('H:i');
                }
                else{
                    if($countConex == 0){
                        $fechaSalida = $fechaViaje;
                        $horaSalida = $fechaViaje->format('H:i');
                    }
                    else {
                        $fechaSalida = "----";
                        $horaSalida = "----";
                    }
                    $fechaLlegada = "----";
                    $horaLlegada = "----";
//                    return null;
                }
                $countConex ++;
                $horas = array();
                $horas['horaSalida'] = $horaSalida !== "----" ? date("g:i a",strtotime($horaSalida)) : "----";
                $horas['horaLlegada'] = $horaLlegada !== "----" ? date("g:i a",strtotime($horaLlegada)) : "----";
                $horas['origen'] = $estacOrigen->getNombre();
                $horas['destino'] = $estacDestino->getNombre();
                $horas['claseBus'] = $claseBus->getNombre();
                $horas['fechaSalida'] = $fechaSalida !== "----" ? $fechaSalida->format('d/m/Y'):"----";
                $horas['fechaLlegada'] = $fechaLlegada !== "----" ? $fechaLlegada->format('d/m/Y'):"----";
                
                $conexionesSimplesMap[$conexionItem->getConexionSimple()->getIdExterno()] = $conexionItem->getConexionSimple();
                $tiemposArrayIntermediasCompuesta[$conexionItem->getConexionSimple()->getIdExterno()] = $horas;
                
                $bajaEnAnterior = $destinoItem;
                if($ruta->getObligatorioClienteDetalle() === true){
                    $clienteDetallado = true;
                }
            }
            $mapConexionTiempos[$conexion->getId()] = $tiemposArrayIntermediasCompuesta;
            
            //salvar conexiones simple de la compuesta
            $mapConexionesIdIntermedias[$conexion->getId()] = $conexionesSimplesMap;
            
            //salvar tiempos generales de la conexion
            $primeraConexion = $listaConexionesItem->first()->getConexionSimple()->getIdExterno();
            $horaSalidaPrimera = $tiemposArrayIntermediasCompuesta[$primeraConexion]["horaSalida"];
            
            $ultimaConexion = $listaConexionesItem->last()->getConexionSimple()->getIdExterno();
            $horaLlegadaUltima = $tiemposArrayIntermediasCompuesta[$ultimaConexion]["horaLlegada"];
            
            $horasG = array();
            $horasG['horaSalida'] = $horaSalidaPrimera;
            $horasG['horaLlegada'] = $horaLlegadaUltima;
            $mapConexionTiemposGeneral[$conexion->getId()] = $horasG;
            
            //salvar conexiones simple de la compuesta
            $mapConexionIds[$conexion->getId()] = $conexion;
            
            return array(0=>$mapConexionIds, 1=>$mapConexionTiempos, 2=>$mapConexionTiemposGeneral,  3=>$mapConexionPrecios, 4=>$mapConexionPreciosIntermedios, 5=>$clienteDetallado, 6=>$mapConexionesIdIntermedias);
      }
    public function save_Tiempo_Precio_Simple($conexion, $origen, $destino, $mapConexionPrecios, $mapConexionIds, $mapConexionTiempos, $fechaIda, $cantPasajeros){
        //salvar tiempos
        $fechaIdaInit = \DateTime::createFromFormat('Y-m-d', $fechaIda);
        $fechaIdaInit->setTime(0, 0, 0);
        $fechaActual = new \DateTime();
        if(UtilService::compararFechas($fechaIdaInit, $fechaActual) === 0){
            $fechaActual->modify("+20 minutes");
            $horaActual = $fechaActual->format("H");
            $minutoActual = $fechaActual->format("i");
            $fechaIdaInit->setTime($horaActual, $minutoActual, 0);
        }        
        $fechaIdaEnd = clone $fechaIdaInit;
        $fechaIdaEnd->setTime(23, 59, 59);
        $itinerario = $conexion->getItinerario();
        $ruta = $itinerario->getRuta();
        $codigoRuta = $ruta->getCodigo();
        $claseBus = $itinerario->getTipoBus()->getClase();
        $estacOrigen = $this->getDoctrine()->getRepository('MayaBundle:Estacion')->find($origen);
        $estacDestino = $this->getDoctrine()->getRepository('MayaBundle:Estacion')->find($destino);
        $horaSalida = "";
        $horaLlegada = "";
        $fechaViaje = $conexion->getFechaViaje();
        $tiempoOrigenASube = $this->getDoctrine()->getRepository('MayaBundle:Tiempo')->getTiempoConexion($origen, $codigoRuta, $claseBus->getId());
        $tiempoOrigenABaja = $this->getDoctrine()->getRepository('MayaBundle:Tiempo')->getTiempoConexion($destino, $codigoRuta, $claseBus->getId());
        if($tiempoOrigenASube !== null && $tiempoOrigenABaja !== null){
            $fechaSubeEn = clone $fechaViaje;
            $fechaSubeEn = $fechaSubeEn->modify("+".$tiempoOrigenASube->getMinutos()." minutes");
            if($fechaSubeEn > $fechaIdaEnd || $fechaSubeEn < $fechaIdaInit)
                return null;
            $horaSalida = $fechaSubeEn->format('H:i');
            $fechaBajaEn = clone $fechaViaje;
            $fechaBajaEn = $fechaBajaEn->modify("+".$tiempoOrigenABaja->getMinutos()." minutes");
            $horaLlegada = $fechaBajaEn->format('H:i');
            
        }
        else{
            if($origen === $ruta->getEstacionOrigen()->getId()){
                $horaSalida = $conexion->getFechaViaje()->format('H:i');
            }
            else $horaSalida = "----";
            
            $horaLlegada = "----";
            $fechaBajaEn = "----";
//                    return null;
        }
        $horas['horaSalida'] =  $horaSalida;
        $horas['horaLlegada'] = $horaLlegada;
        $horas['origen'] = $estacOrigen->getNombre();
        $horas['destino'] = $estacDestino->getNombre();
        $horas['claseBus'] = $claseBus->getNombre();
        $horas['fechaSalida'] = $fechaViaje->format("d-m-Y");
        $horas['fechaLlegada'] = $fechaBajaEn !== "----" ? $fechaBajaEn->format("d-m-Y"): "----";
        $horas['fechaViajeRuta'] = $fechaViaje->format("d-m-Y");
        $horas['horaSalidaRuta'] = $fechaViaje->format("H:i");
        
        $mapConexionTiempos[$conexion->getId()] = $horas;
        
        //salvar precios
        $preciosA = $this->getDoctrine()->getRepository('MayaBundle:TarifaBoleto')->getTarifaBoleto($origen, $destino, $claseBus, 1, $fechaIda);
        $tipoBusActualizado = $conexion->getTipoBus();
        $preciosB = null;
        
        if($tipoBusActualizado->existeAsientoB() === true){
            $preciosB = $this->getDoctrine()->getRepository('MayaBundle:TarifaBoleto')->getTarifaBoleto($origen, $destino, $claseBus, 2, $fechaIda);
        }
        
//        $precios = array();
//        $preciosA ['tarifaValor'] = $preciosA ['precioUnitario'] * $cantPasajeros;
//        $preciosB ['tarifaValor'] = $preciosB ['precioUnitario'] * $cantPasajeros;
//        $precios[] = $preciosA;
//        $precios [] = $preciosB;
//        $mapConexionPrecios[$conexion->getId()] = $precios;
        
        $preciosBoletos = array();
        $preciosBoletos["A"] = $preciosA === null ? null : $preciosA['precioUnitario'];
        $preciosBoletos["B"] = $preciosB === null ? null : $preciosB['precioUnitario'];
        $preciosBoletos['idTarifaA'] = $preciosA === null ? null : $preciosA['idTarifa'];
        $preciosBoletos['idTarifaB'] = $preciosB === null ? null : $preciosB['idTarifa'];
        $preciosBoletos['totalA'] = $preciosA === null ? null : $preciosBoletos["A"] * $cantPasajeros;
        $preciosBoletos['totalB'] = $preciosB === null ? null : $preciosBoletos["B"] * $cantPasajeros;
        
        $mapConexionPrecios[$conexion->getId()] = $preciosBoletos;
        //salvar conexion
        $mapConexionIds[$conexion->getId()] = $conexion;
        return array(0=>$mapConexionIds, 1=>$mapConexionPrecios, 2=>$mapConexionTiempos, 3=>$ruta->getObligatorioClienteDetalle());
    
      }
      public function conexionCompuestaAsientosDisponibles($conexion, $cantBoletos){
            $listaItem = $conexion->getListaConexionItem();
            foreach ($listaItem as $item) {
              $simple = $item->getConexionSimple();
              $tipoBus = $simple->getTipoBus();
              $idEstado = strval($simple->getEstado()->getId());
              if(($simple->getCantVendidos()+intval($cantBoletos)) > $tipoBus->getTotalAsientos() 
                      || $idEstado === EstadoConexion::CANCELADA
                      || $idEstado === EstadoConexion::INICIADA
                      || $idEstado === EstadoConexion::FINALIZADA){
                  return false;
              }
            }
            return true;
      }
      
      public function diasDiferenciaFechas($salida, $llegada){
          if($salida !== "----" && $llegada !== "----"){
            $diff = $llegada - $salida;
            if($diff > 0)
                return $diff;
            return (24 - $salida) + $llegada;
          }
          return 0;
      }
      
      /**
     * @Route(path="/validarCliente.json", name="validarCliente")
    */
    public function validarClienteAction(Request $request){
        $result = false;
        $error = "";
        $cliente = null;
        $user = $this->getUser();
        if($user !== null && $user instanceof UserOauth){
            $cliente = $this->getDoctrine()->getRepository('MayaBundle:Cliente')->findOneByUsuario($user->getId());
        }
        if($cliente === null){
            $cliente = new Cliente();
        }
        $form = $this->createForm(new ClienteAnonimoType($this->getDoctrine()), $cliente);
        $em = $this->getDoctrine()->getManager();
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $result = true;
                
                $checkExisteCliente = $this->getDoctrine()->getRepository('MayaBundle:Cliente')->checkExisteCliente($cliente->getNacionalidad(),
                        $cliente->getTipoDocumento(), $cliente->getNumeroDocumento(), $cliente->getNombreApellidos(), $cliente->getNit());
//                var_dump($checkExisteCliente["existe"]);
                if($checkExisteCliente["existe"] === true){
                    $cliente = $checkExisteCliente["cliente"];
               }
                $message = "";
                if($user !== null && $user instanceof UserOauth){
                    
                    if($cliente->getUsuario() === null){
                        $user = $em->merge($user);
                        $cliente->setUsuario($user);
                    }
                    $em->getConnection()->beginTransaction();
                    try {
                        $em->persist($cliente);
                        $em->flush();
                        $em->getConnection()->commit();
                    } catch (\RuntimeException $exc) {
                        $em->getConnection()->rollback();
                        $error = $exc->getMessage();
                        $this->get("logger")->error("ERROR:" . $message);

                    } catch (\Exception $exc) {
                        $em->getConnection()->rollback();
                        $error = $exc->getMessage();
                        $this->get("logger")->error("ERROR:" . $message);
                    }

                    
                }
                $session = $request->getSession();
                $em->detach($cliente);
                $session->set('cliente', $cliente);
                
            }
            else{
               $error = UtilService::getErrorsToForm($form);
            }
         }
        $response = new JsonResponse();
        $response->setData(array(
            'valid' => $result,
            'error' => $error
        ));
        return $response;
    }
    
    /**
     * @Route(path="/saveCompra.json", name="saveCompra")
    */
    public function saveCompraAction(Request $request){
        $status = "2";
        $message = "Faltan parametros por definir";
        $compra = $request->query->get('compra');
        if (is_null($compra)) {
            $compra = $request->request->get('compra');
        }
        if($compra !== null){
            $recaptcha = new ReCaptcha($this->container->getParameter("recaptcha_private_key"));
            $gRecaptchaResponse = $request->request->get('g-recaptcha-response');
            if($gRecaptchaResponse === null || trim($gRecaptchaResponse) === ""){
                $status = "2";
                $message = "Debe definir el recaptcha.";
            }else {
                $resp = $recaptcha->verify($gRecaptchaResponse, null);
                if ($resp->isSuccess()) {
                    $em = $this->getDoctrine()->getManager();
                    $em->getConnection()->beginTransaction();
                    try {
                        
                        $compra = json_decode($compra);
                        $compraItem = new Compra();
                        $compraItem->setFecha(new \DateTime());
                        $compraItem->setEstado($this->getDoctrine()->getRepository('MayaBundle:EstadoCompra')->find(EstadoCompra::PENDIENTE));
                        
                        $idEstacion = $compra->estacion;
                        if( $idEstacion === null && trim($idEstacion) === ""){
                            throw new \RuntimeException("Debe seleccionar la estación donde va a recoger su factura.");
                        }
                        $compraItem->setEstacionFactura($this->getDoctrine()->getRepository('MayaBundle:Estacion')->find($idEstacion));
                        
                        $precio = 0;
                        $session = $request->getSession();
                        $pasajerosSession = $session->get("pasajeros");
                        if($pasajerosSession == null){
                            throw new \RuntimeException("No se pudo cargar los pasajeros de la compra.");
                        }
                        foreach ($pasajerosSession as $item) {
                            $compraItem->addListaPasajeros($item);
                            $precio += $item->precio();
                        }
                        $compraItem->setPrecio($precio);

                        $clienteSession = $session->get("cliente");
                        if($clienteSession == null){
                            throw new \RuntimeException("No se pudo cargar el cliente de la compra.");
                        }
                        $compraItem->setCliente($clienteSession);
                        $compraItem->preparedPersist($em);
//                        var_dump($compraItem);
                        $em->persist($compraItem);
                        $em->flush();
                        $em->getConnection()->commit();

                        $session->set("last_id_compra_step1", strval($compraItem->getId()));
                        $status = "1";
                        $message = $this->generateUrl('pasarela-pago-init');

                    } catch (\RuntimeException $exc) {
                        $em->getConnection()->rollback();
                        $status = "2";
                        $message = $exc->getMessage();
                        $this->get("logger")->error("ERROR:" . $message);
                    } catch (\ErrorException $exc) {
                        $em->getConnection()->rollback();
                        $this->get("logger")->error("ERROR:" . $exc->getTraceAsString());
                        $status = "2";
                        $message = "Ha ocurrido un error procesando los datos de la compra.";
                    } catch (\Exception $exc) {
                        $em->getConnection()->rollback();
                        $this->get("logger")->error("ERROR:" . $exc->getTraceAsString());
                        $status = "2";
                        $message = "Ha ocurrido un error procesando los datos de la compra.";
                    }
                }
                else {
                    $status = "2";
                    $message = implode('', $resp->getErrorCodes());
                } 
            }
        }
        
        $response = new JsonResponse();
         $response->setData(array(
             'status' => $status,
             'message' => $message
         ));
        return $response;
    }
    
    /**
     * @Route(path="/savePasajeros.json", name="savePasajeros")
    */
    public function savePasajerosAction(Request $request){
        $error = "";
        $paquete = $request->query->get('paquete');
        if (is_null($paquete)) {
            $paquete = $request->request->get('paquete');
        }
        
        $pasajeroArray = $request->query->get('pasajeros');
        if (is_null($pasajeroArray)) {
            $pasajeroArray = $request->request->get('pasajeros');
        }
        $reservacionArray = $request->query->get('reservaciones');
        if (is_null($reservacionArray)) {
            $reservacionArray = $request->request->get('reservaciones');
        }
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getManager();
        try {
            if($pasajeroArray !== null && $paquete !== null){
                $pasajeroArray = json_decode($pasajeroArray);
                $paquete = json_decode($paquete);
                $reservacionArray = json_decode($reservacionArray);
                //$reservacionArrayPasajero = $this->reservacionesPorPasajero($reservacionArray);
                $index = 0;
                foreach ($pasajeroArray as $pasajero) {
                    $pasajeroItem = new Pasajero();
                    $pasajeroItem->setNombreApellidos($pasajero->nombreApell);
                    $pasajeroItem->setValorDocumento($pasajero->numDoc);
                    $idTipoDocumento = $pasajero->tipoDoc;
                    //FOR IE
                    if(is_array($idTipoDocumento) && isset($idTipoDocumento[0])){
                        $idTipoDocumento = $idTipoDocumento[0];
                    }
                    if($idTipoDocumento === null || trim($idTipoDocumento) === ""){
                        throw new \RuntimeException("Debe definir el tipo de documento del pasajero.");
                    }
                    $pasajeroItem->setTipoDocumento($this->getDoctrine()->getRepository('MayaBundle:Documento')->find($idTipoDocumento));
                    $idNacionalidad = $pasajero->nacionalidad;
                    //FOR IE
                    if(is_array($idNacionalidad) && isset($idNacionalidad[0])){
                        $idNacionalidad = $idNacionalidad[0];
                    }
                    if($idNacionalidad === null || trim($idNacionalidad) === ""){
                        throw new \RuntimeException("Debe definir la nacionalidad del pasajero.");
                    }
                    $pasajeroItem->setNacionalidad($this->getDoctrine()->getRepository('MayaBundle:Nacionalidad')->find($idNacionalidad));

                    if($pasajero->detallado !== null){
                        $pasajeroItem->setDetallado($pasajero->detallado);
                        if($pasajero->detallado === true){
                            $idSexo = $pasajero->sexo;
                            if($idSexo === null || trim($idSexo) === ""){
                                throw new \RuntimeException("Debe definir el sexo del pasajero.");
                            }
                            $pasajeroItem->setSexo($this->getDoctrine()->getRepository('MayaBundle:Sexo')->find($idSexo));
                            if($pasajero->fechaNac !== null){
                               $fechaNac = \DateTime::createFromFormat('d/m/Y', $pasajero->fechaNac);
                               $pasajeroItem->setFechaNacimiento($fechaNac);
                            }
                            if($pasajero->fechaVenc !== null){
                                $fechaVenc = \DateTime::createFromFormat('d/m/Y', $pasajero->fechaVenc);
                                $pasajeroItem->setFechaVencimientoDocumento($fechaVenc);
                            }
                        }
                    } 

                    $paqueteIda = new Paquete();
                    $conexionIda = null;
                    $mapPrecioConexionIda = null;
                    $subeEn = $paquete->subeEn;
                    if($subeEn === null || trim($subeEn) === ""){
                        throw new \RuntimeException("No se ha podido determinar la estación donde sube el pasajero en el paquete de ida.");
                    }
                    $subeEn = $this->getDoctrine()->getRepository('MayaBundle:Estacion')->find($subeEn);
                    $bajaEn = $paquete->bajaEn;
                    if($bajaEn === null || trim($bajaEn) === ""){
                        throw new \RuntimeException("No se ha podido determinar la estación donde baja el pasajero en el paquete de ida.");
                    }
                    $bajaEn = $this->getDoctrine()->getRepository('MayaBundle:Estacion')->find($bajaEn);
                    
                    if($paquete->conexionIda !== null){
                        if($paquete->tipoConexionIda === 0){
                            $conexionIda = $this->getDoctrine()->getRepository('MayaBundle:ConexionSimple')->find($paquete->conexionIda);
                            $preciosIda = $session->get("preciosSimplesIda");
                            $paqueteIda->setConexion($conexionIda);
                            if($preciosIda != null){
                                $mapPrecioConexionIda = $preciosIda[$paquete->conexionIda];
                            }
                            foreach ($reservacionArray as $idSalida => $value) {
                                if($idSalida === $conexionIda->getIdExterno()){
                                    $this->saveBoletos($paqueteIda, $value[$index], $mapPrecioConexionIda, $subeEn, $bajaEn);
                                    break;
                                }
                            }
                            
                        }
                        else if($paquete->tipoConexionIda === 1){
                            $conexionIda = $this->getDoctrine()->getRepository('MayaBundle:ConexionCompuesta')->find($paquete->conexionIda);
                            $preciosIda = $session->get("preciosCompuestasIda");
                            $paqueteIda->setConexion($conexionIda);
                            if($preciosIda != null){
                                $mapPrecioConexionIda = $preciosIda[$paquete->conexionIda];
                            }
                            $encontrada = false;
                            $asientoReservacion = null;
                            if($conexionIda !== null){
                                $subeEn = $conexionIda->getItinerarioCompuesto()->getEstacionOrigen();
                                $bajaAnterior = null;
                                $indexSimples = 0;
                                foreach ($reservacionArray as $idSalida => $value) {
                                    foreach ($conexionIda->getListaConexionItem() as $item) {
                                        $conexionSimple = $item->getConexionSimple();
                                        $idSimple = $conexionSimple->getIdExterno();
                                        if($idSimple === $idSalida){
                                            if($indexSimples !== 0){
                                                $subeEn = $bajaAnterior;
                                            }
                                            $bajaEn = $item->getItinerarioItem()->getBajaEn();
                                            $asientoReservacion = $value[$index];
                                            $this->saveBoletos($paqueteIda, $asientoReservacion, $mapPrecioConexionIda, $subeEn, $bajaEn, $conexionSimple);
                                            $bajaAnterior = $bajaEn;
                                            $indexSimples ++;
                                        }
                                       
                                    }
                                }
                            }
                        }
                    }
                   $pasajeroItem->addListaPaquetes($paqueteIda);
                    //$paqueteIdaSession = $em->detach($paqueteIda);
                    if($paquete->regreso === "true" || $paquete->regreso === true){
                        $paqueteRegreso = new Paquete();
                        $conexionRegreso = null;
                        $mapPrecioConexionRegreso = null;
                        if($paquete->conexionRegreso !== null){
                            if($paquete->tipoConexionRegreso === 0){
                                $conexionRegreso = $this->getDoctrine()->getRepository('MayaBundle:ConexionSimple')->find($paquete->conexionRegreso);
                                $preciosRegreso = $session->get("preciosSimplesRegreso");
                                $paqueteRegreso->setConexion($conexionRegreso);
                                if($preciosRegreso != null){
                                    $mapPrecioConexionRegreso = $preciosRegreso[$paquete->conexionRegreso];
                                }
                                foreach ($reservacionArray as $idSalida => $value) {
                                    if($idSalida === $conexionRegreso->getIdExterno()){
                                        $this->saveBoletos($paqueteRegreso, $value[$index], $mapPrecioConexionRegreso, $bajaEn, $subeEn);
                                        break;
                                    }
                                }
                            }
                            else if($paquete->tipoConexionRegreso === 1){
                                $conexionRegreso = $this->getDoctrine()->getRepository('MayaBundle:ConexionCompuesta')->find($paquete->conexionRegreso);
                                $preciosRegreso = $session->get("preciosCompuestasRegreso");
                                if($preciosRegreso != null){
                                    $mapPrecioConexionRegreso = $preciosRegreso[$paquete->conexionRegreso];
                                }
                                $encontrada = false;
                                $asientoReservacion = null;
                                if($conexionRegreso !== null){
                                    $paqueteRegreso->setConexion($conexionRegreso);
                                    $subeEn = $conexionRegreso->getItinerarioCompuesto()->getEstacionOrigen();
                                    $bajaAnterior = null;
                                    $indexSimples = 0;
                                    foreach ($reservacionArray as $idSalida => $value) {
                                        foreach ($conexionRegreso->getListaConexionItem() as $item) {
                                            $conexionSimple = $item->getConexionSimple();
                                            $idSimple = $conexionSimple->getIdExterno();
                                            if($idSimple === $idSalida){
                                                if($indexSimples !== 0){
                                                    $subeEn = $bajaAnterior;
                                                }
                                                $bajaEn = $item->getItinerarioItem()->getBajaEn();
                                                $asientoReservacion = $value[$index];
                                                $this->saveBoletos($paqueteRegreso, $asientoReservacion, $mapPrecioConexionRegreso, $subeEn, $bajaEn, $conexionSimple);
                                                $bajaAnterior = $bajaEn;
                                                $indexSimples ++;
                                            }
                                        }
                                    }
                                }
                            }
                       }
                        $pasajeroItem->addListaPaquetes($paqueteRegreso);
                     }
                     $pasajeros[] = $pasajeroItem;
                     $index ++;
                 }
                
                $session->set('pasajeros', $pasajeros);
            }
            $estacionesFacturas = [];
            $estacionesF = $em->getRepository('MayaBundle:Estacion')->getEstacionesFacturacion();
            foreach ($estacionesF as $item) {
                $estacionesFacturas [] = array(
                    'id' => $item->getId(),
                    'nombre' => $item->getAlias()." - ".$item->getNombre()
                );
            }
            
        }catch (\RuntimeException $exc) {
           $error = $exc->getMessage();
           $this->get("logger")->error("ERROR:" . $exc->getTraceAsString());
       } catch (\ErrorException $exc) {
            $this->get("logger")->error("ERROR:" . $exc->getTraceAsString());
            $error = "Ha ocurrido un error procesando los datos.";
       } catch (\Exception $exc) {
            $this->get("logger")->error("ERROR:" . $exc->getTraceAsString());
            $error = "Ha ocurrido un error procesando los datos.";
        }
        
        $response = new JsonResponse();
         $response->setData(array(
             'error' => $error,
             'estacionesFacturacion' => $estacionesFacturas
         ));
        return $response;
    }
        
    private function saveBoletos($paquete, $asientoReservacion, $mapPrecios, $subeEn, $bajaEn, $conexionBoleto = null){
        $em = $this->getDoctrine()->getManager();
        $boleto = new Boleto ();
        $boleto->setIdExternoReservacion($asientoReservacion->idReservacion);
        $boleto->setSubeEn($subeEn);
        $boleto->setBajaEn($bajaEn);
        $asiento = $em->getRepository('MayaBundle:AsientoBus')->find($asientoReservacion->idAsiento);
        $clase = null;
        if($asiento !== null){
            $boleto->setAsientoBus($asiento);
            $clase = $asiento->getClase()->getId();
        }
        $boleto->setEstado($em->getRepository('MayaBundle:EstadoBoleto')->find(EstadoBoleto::PENDIENTE));
        $mapTarifasPrecios = array();
        
        if($paquete->getConexion() instanceof ConexionSimple){
            $boleto->setConexion($paquete->getConexion());
            $mapTarifasPrecios = $this->preciosTarifasSimple($mapPrecios, $clase);
        }
        else{
            $boleto->setConexion($conexionBoleto);
            $mapTarifasPrecios = $this->preciosTarifasCompuesta($conexionBoleto->getIdExterno(), $mapPrecios, $clase);
        }
        
        $boleto->setPrecio($mapTarifasPrecios[0]);
        if($mapTarifasPrecios[1] !== null){
            $boleto->setTarifa($em->getRepository('MayaBundle:TarifaBoleto')->find($mapTarifasPrecios[1]));
        }
        $paquete->addListaBoletos($boleto);
        $precio = $paquete->getPrecio() + $boleto->getPrecio();
        $paquete->setPrecio($precio);
    }
    
    private function preciosTarifasCompuesta($idSalida, $arrayPrecios, $idClaseAsiento){
        $tarifa = null;
        $precio = null;
        foreach ($arrayPrecios as $key => $value) {
            if($key."" === $idSalida){
                if($idClaseAsiento === "1"){
                    $tarifa = $value['idTarifaA'];
                    $precio = floatval($value['A']);
                }
                else{
                    $tarifa = $value['idTarifaB'];
                    $precio = floatval($value['B']);
                }   
             }
         }
         return array(0=>$precio, 1=>$tarifa);
    }
    
    private function preciosTarifasSimple($arrayPrecios, $idClaseAsiento){
        $tarifa = null;
        $precio = null;
        if($idClaseAsiento === "1"){
            $tarifa = $arrayPrecios['idTarifaA'];
            $precio = floatval($arrayPrecios['A']);
        }
        else{
            $tarifa = $arrayPrecios['idTarifaB'];
            $precio = floatval($arrayPrecios['B']);
        }   
        return array(0=>$precio, 1=>$tarifa);
    }
    
    /**
     * @Route(path="/loadCliente.json", name="loadCliente")
    */
    public function loadClienteAction()
    {
        $result = null;
        $cliente = null;
        $user = $this->getUser();
        if($user !== null && $user instanceof UserOauth){
            $clientes = $this->getDoctrine()->getRepository('MayaBundle:Cliente')->findByUsuario($user->getId());
            if(count($clientes) >= 1){
                $cliente = $clientes[0];
                $result = array(
                    'nombre' => $cliente->getNombreApellidos(),
                    'correo' => $cliente->getCorreo(),
                    'nit' => $cliente->getNit(),
                    'tipoDoc' => $cliente->getTipoDocumento() !== null ? $cliente->getTipoDocumento()->getId() : null,
                    'numDoc' => $cliente->getNumeroDocumento(),
                    'nacionalidad' => $cliente->getNacionalidad() !== null ? $cliente->getNacionalidad()->getId() : null,
                    'telefono' => $cliente->getTelefono(),
                    'fechaNac' => $cliente->getFechaNacimiento() !== null ? $cliente->getFechaNacimiento()->format('d-m-Y') : null,
                    'fechaVenc' => $cliente->getFechaVencimientoDocumento() !== null ? $cliente->getFechaVencimientoDocumento()->format('d-m-Y') : null,
                    'sexo' => $cliente->getSexo() !== null ? $cliente->getSexo()->getId() : null
                );
            }
        }
        
        $response = new JsonResponse();
        $response->setData(array(
            'cliente' => $result
        ));
        return $response;
    }
}