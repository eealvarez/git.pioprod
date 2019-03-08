<?php

namespace Acme\BackendBundle\Services;

use Acme\BackendBundle\Scheduled\ScheduledServiceInterface;
use Acme\BackendBundle\Entity\JobType;
use Acme\MayaBundle\Entity\Departamento;
use Acme\MayaBundle\Entity\Estacion;
use Acme\MayaBundle\Entity\TipoBus;
use Acme\MayaBundle\Entity\HorarioCiclico;
use Acme\MayaBundle\Entity\Ruta;
use Acme\MayaBundle\Entity\ItinerarioSimple;
use Acme\MayaBundle\Entity\ItinerarioEspecial;
use Acme\MayaBundle\Entity\ConexionSimple;
use Acme\MayaBundle\Entity\TarifaBoleto;
use Acme\MayaBundle\Entity\AsientoBus;
use Acme\MayaBundle\Entity\SenalBus;
use Acme\MayaBundle\Entity\Tiempo;
use Acme\MayaBundle\Entity\RutaEstacionItem;

class SincronizacionService implements ScheduledServiceInterface{
    
    protected $container;
    protected $doctrine;
    protected $options;
    protected $logger;
    
    public function __construct($container) { 
        $this->container = $container;
        $this->doctrine = $this->container->get('doctrine');
        $this->logger = $this->container->get('logger');
    }
    
    
    public function setScheduledJob(\Acme\BackendBundle\Entity\Job $job = null) {
        $this->logger->warn("SincronizacionService: SincronizacionService-init");
        $this->job = $job;
        try {
            $this->getActualizacionesAsientosOcupadosBySalidas();
        } catch (\Exception $ex) {
            $this->logger->warn("Ocurrio una exception en el proceso de getActualizacionesAsientosOcupadosBySalidas");
            throw $ex;
        }
        
        try {
            $this->getActualizacionesJob();
        } catch (\Exception $ex) {
            $this->logger->warn("Ocurrio una exception en el proceso de getActualizacionesJob");
            throw $ex;
        }
        $this->logger->warn("SincronizacionService: SincronizacionService-end");
    }
    
    public function getActualizacionesAsientosOcupadosBySalidas(){
        $this->logger->warn("SincronizacionService: getActualizacionesAsientosOcupadosBySalidas - init"); 
        $url = $this->container->getParameter("internal_sys_url") .
               $this->container->getParameter("internal_sys_pref") .
               "ao.json";
        $result =  $this->doRequestIntegration($url);
        $response = json_decode($result, true);
        $items = $response['data'];
        
        $em = $this->doctrine->getManager();
        $em->getConnection()->beginTransaction();
                
        try {
            
            foreach ($items as $idConexionSimple => $cantidad) {
                var_dump("SincronizacionService: Salida: ".$idConexionSimple. " => " .$cantidad);
                $conexionSimple = $this->doctrine->getRepository('MayaBundle:ConexionSimple')->findOneByIdExterno($idConexionSimple);
                if($conexionSimple !== null){
                    $conexionSimple->setCantVendidos($cantidad);
                    $em->persist($conexionSimple);
                }
            }
            $em->flush();
            $em->getConnection()->commit();
           
        } catch (\RuntimeException $exc) {
            var_dump("ERROR:" . $exc->getMessage());
            $this->logger->error("ERROR:" . $exc->getMessage());
            $em->getConnection()->rollback();
        } catch (\Exception $exc) {
            var_dump("ERROR:" . $exc->getMessage());
            $this->logger->error("ERROR:" . $exc->getMessage());
            $em->getConnection()->rollback();
        }
        
        $this->logger->warn("SincronizacionService: getActualizacionesAsientosOcupadosBySalidas - end");
    }
    
    public function getActualizacionesJob(){
        $em = $this->doctrine->getManager();
        $this->logger->warn("SincronizacionService: getActualizacionesJob - init");        
        while ($items = $this->getItemsPendientesServer()) {
            $this->logger->warn("SincronizacionService: Existen " . count($items) . " items de actualizacion.");
            $stateItems = array();
            foreach ($items as $item) {
                $id = $item['id'];
                $em = $this->doctrine->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    var_dump("SincronizacionService: Procesando Item Id: ".$id);
                    $this->logger->warn("SincronizacionService: Procesando Item Id: ".$id);
                    $objects = $this->getObjects($item["data"]);
                    foreach ($objects as $item) {
                        $em->persist($item);
                    }
                    $em->flush();
                    $em->getConnection()->commit();
                    var_dump("Item Id:".$id." actualizado");
                    $stateItems[] = array("id" => $id, "estado" => "3" );
                } catch (\RuntimeException $exc) {
                    $em->getConnection()->rollback();
                    var_dump("Error en id:".$id.". Message:".$exc->getMessage());
                    $this->logger->warn("Error en id:".$id.". Message:".$exc->getMessage());
                    $stateItems[] = array("id" => $id, "estado" => "2" );
                }catch (\Exception $exc) {
                    $em->getConnection()->rollback();
                    var_dump("Error en id:".$id.". Message:".$exc->getMessage());
                    $this->logger->warn("Error en id:".$id.". Message:".$exc->getMessage());
                    $stateItems[] = array("id" => $id, "estado" => "2" );
                }
            }
            
            $url =  $this->container->getParameter("internal_sys_url") .
                    $this->container->getParameter("internal_sys_pref") .
                    "eap.json";
            $result =  $this->doRequestIntegration($url, $stateItems);
            $this->logger->warn($result);
        }
        
        $this->logger->warn("SincronizacionService: getActualizacionesJob - end");
    }
    
    function getItemsPendientesServer(){
        $items = array();
         try {
             $this->logger->warn("Buscando job pendientes en el servidor...");
              var_dump("Buscando job pendientes en el servidor...");
              $url =    $this->container->getParameter("internal_sys_url") .
                        $this->container->getParameter("internal_sys_pref") .
                        "ga.json";
              $result =  $this->doRequestIntegration($url);
              $this->logger->warn($result);
              var_dump($result);
              $response = json_decode($result, true);
              $items = json_decode($response['data'], true);
             
          } catch (\Exception $exc) {
               echo $exc->getMessage();
               $this->logger->warn($exc->getMessage());   
          }
          return $items;
    }
    
    function getObjects($data){
        $objects = array();
        if($data["type"] == JobType::TYPE_SYNC_DEPARTAMENTO){
            $object = $this->doctrine->getRepository('MayaBundle:Departamento')->find($data["id"]);
            if(is_null($object)){
                $object = new Departamento();
                $object->setId($data["id"]);
                var_dump("Creando departamento: ".$object->getId());
            }else{
                var_dump("Actualizado departamento: ".$object->getId());
            }
            $object->setNombre($data["nombre"]);
            $object->setActivo($data["activo"]);
            $objects[] = $object;
        }
        else if($data["type"] == JobType::TYPE_SYNC_ESTACION){
               $object = $this->doctrine->getRepository('MayaBundle:Estacion')->find($data["id"]);
               if(is_null($object)){
                   $object = new Estacion();
                   $object->setId($data["id"]);
                   var_dump("Creando estacion: ".$object->getId());
               }else{
                   var_dump("Actualizado estacion: ".$object->getId());
               }
               
               $object->setNombre($data["nombre"]);
               $object->setAlias($data["alias"]);
               $object->setDireccion($data["direccion"]);
               $object->setLongitude(isset($data["longitude"]) ? $data["longitude"] : null);
               $object->setLatitude(isset($data["latitude"]) ? $data["latitude"] : null);
               $object->setActivo($data["activo"]);
               
               $tipo = $this->doctrine->getRepository('MayaBundle:TipoEstacion')->findOneById($data["tipo"]);
               $object->setTipo($tipo);
               
               if($data["departamento"] !== null && trim($data["departamento"]) !== ""){
                    $departamento = $this->doctrine->getRepository('MayaBundle:Departamento')->findOneById($data["departamento"]);
                    $object->setDepartamento($departamento);
               }
               
               $correos = "";
               foreach ($data["correos"] as $correo) {
                   $correos = $correos." ".$correo; 
               }
               $object->setListaCorreos($correos);
               
               $telefs = "";
               foreach ($data["telefonos"] as $telef) {
                   $telefs = $telefs." ".$telef; 
               }
               $object->setListaTelefonos($telefs);
               $objects[] = $object;
        }
        else if($data["type"] == JobType::TYPE_SYNC_TIPO_BUS){
             $object = $this->doctrine->getRepository('MayaBundle:TipoBus')->find($data["id"]);
             if(is_null($object)){
                $object = new TipoBus();
                $object->setId($data["id"]);
                var_dump("Creando tipo bus: ".$object->getId());
             }else{
                var_dump("Actualizado tipo bus: ".$object->getId());
             }
             
             $object->setAlias($data["alias"]);
             $object->setNivel2($data["nivel2"]);
             $clase = $this->doctrine->getRepository('MayaBundle:ClaseBus')->findOneById($data["clase"]);
             $object->setClase($clase);
             $listaAsientosNewItems = array ();
             $listaAsientosOldItems = array ();
             foreach ($data["asientos"] as $item) {
                 $asiento = $this->doctrine->getRepository('MayaBundle:AsientoBus')->find($item["id"]);
                 if(is_null($asiento)){
                     $asiento = new AsientoBus();
                     $asiento->setId($item["id"]);
                     $listaAsientosNewItems [] = $asiento;
                 }else{
                     $listaAsientosOldItems [] = $asiento;
                 }
                 $asiento->setNumero($item["numero"]);
                 $asiento->setCoordenadaX($item["coordenadaX"]);
                 $asiento->setCoordenadaY($item["coordenadaY"]);
                 $asiento->setNivel2($item["nivel2"]);
                 $claseA = $this->doctrine->getRepository('MayaBundle:ClaseAsiento')->findOneById($item["clase"]);
                 $asiento->setClase($claseA);
             }
             $listaAsientoRemove = array();
             $listaAsientosNoActualiz = $object->getListaAsiento();
             if(count($listaAsientosNoActualiz) > 0){
                 $listaAsientoRemove = array_diff($listaAsientosNoActualiz->toArray(),$listaAsientosOldItems);
             }
             foreach ($listaAsientoRemove as $item2) {
                 $object->removeListaAsiento($item2);
             }
             foreach ($listaAsientosNewItems as $item3){
                 $object->addListaAsiento($item3);
             }
             $object->setTotalAsientos(count($object->getListaAsiento()));
             
             $listaSenalesNewItems = array ();
             $listaSenalesOldItems = array ();
             foreach ($data["senales"] as $item) {
                 $senal = $this->doctrine->getRepository('MayaBundle:SenalBus')->find($item["id"]);
                 if(is_null($senal)){
                     $senal = new SenalBus();
                     $senal->setId($item["id"]);
                     $listaSenalesNewItems [] = $senal;
                 }
                 else{
                     $listaSenalesOldItems [] = $senal;
                 }
                 $senal->setCoordenadaX($item["coordenadaX"]);
                 $senal->setCoordenadaY($item["coordenadaY"]);
                 $senal->setNivel2($item["nivel2"]);
                 $tipo = $this->doctrine->getRepository('MayaBundle:TipoSenal')->findOneById($item["tipo"]);
                 $senal->setTipo($tipo);
             }
             $listaSenalesRemove = array();
             $listaSenalesNoActualiz = $object->getListaSenal();
             if(count($listaSenalesNoActualiz) > 0){
                 $listaSenalesRemove = array_diff($listaSenalesNoActualiz->toArray(),$listaSenalesOldItems);
             }
             foreach ($listaSenalesRemove as $item2) {
                 $object->removeListaSenal($item2);
             }
             foreach ($listaSenalesNewItems as $item3){
                 $object->addListaSenal($item3);
             }
             $objects[] = $object;
        }
        
        else if($data["type"] == JobType::TYPE_SYNC_HORARIO_CICLICO){
             $object = $this->doctrine->getRepository('MayaBundle:HorarioCiclico')->find($data["id"]);
             if(is_null($object)){
                $object = new HorarioCiclico();
                $object->setId($data["id"]);
                var_dump("Creando horario ciclico: ".$object->getId());
             }else{
                var_dump("Actualizado horario ciclico: ".$object->getId());
             }
             
             $object->setActivo($data["activo"]);
             json_decode($data["hora"]["date"]);
             $object->setHora(\DateTime::createFromFormat('Y-m-d H:i:s', $data["hora"]["date"]));
             $objects[] = $object;
        }
        
        else if($data["type"] == JobType::TYPE_SYNC_RUTA){
             $object = $this->doctrine->getRepository('MayaBundle:Ruta')->find($data["codigo"]);
             if(is_null($object)){
                $object = new Ruta();
                $object->setCodigo($data["codigo"]);
                var_dump("Creando ruta: ".$object->getCodigo());
             }else{
                var_dump("Actualizado ruta: ".$object->getCodigo());
             }
             
             $object->setNombre($data["nombre"]);
             $object->setActivo($data["activa"]);
             $object->setObligatorioClienteDetalle($data["obligatorioClienteDetalle"]);
             
             $origen = $this->doctrine->getRepository('MayaBundle:Estacion')->findOneById($data["origen"]);
             $object->setEstacionOrigen($origen);
             
             $destino = $this->doctrine->getRepository('MayaBundle:Estacion')->findOneById($data["destino"]);
             $object->setEstacionDestino($destino);
             $lengthIntermedias = count($object->getListaEstacionesIntermediaOrdenadas());
             foreach ($data["intermedias"] as $item) {
                    $existeEstacion = false;
                    //if($lengthIntermedias > 0){
                        foreach ($object->getListaEstacionesIntermediaOrdenadas() as $item2) {
                            if($item2->getEstacion()->getId() == $item["estacion"]){
                                $item2->setPosicion($item["posicion"]);
                                $existeEstacion = true;
                                break;
                            }
                        }
                   // }
                    if($existeEstacion == false){
                        $intermedia = new RutaEstacionItem();
                        $estacion = $this->doctrine->getRepository('MayaBundle:Estacion')->find($item["estacion"]);
                        $intermedia->setEstacion($estacion);
                        $intermedia->setPosicion($item["posicion"]);
                        $object->addListaEstacionesIntermediaOrdenadas($intermedia);
                    }
             }
             $objects[] = $object;
        }
        
        else if($data["type"] == JobType::TYPE_SYNC_TARIFA_BOLETO){
            $object = $this->doctrine->getRepository('MayaBundle:TarifaBoleto')->find($data["id"]);
             if(is_null($object)){
                $object = new TarifaBoleto();
                $object->setId($data["id"]);
                var_dump("Creando tarifa: ".$object->getId());
             }else{
                var_dump("Actualizado tarifa: ".$object->getId());
             }
             
             $fechaE = $data["fechaEfectividad"];
             $object->setFechaEfectividad(\DateTime::createFromFormat('d-m-Y H:i:s', $fechaE));
             
             $claseA = $this->doctrine->getRepository('MayaBundle:ClaseAsiento')->findOneById($data["claseAsiento"]);
             $object->setClaseAsiento($claseA);
             
             $claseB = $this->doctrine->getRepository('MayaBundle:ClaseBus')->findOneById($data["claseBus"]);
             $object->setClaseBus($claseB);
             
             $origen = $this->doctrine->getRepository('MayaBundle:Estacion')->findOneById($data["estacionOrigen"]);
             $object->setEstacionOrigen($origen);
             
             $destino = $this->doctrine->getRepository('MayaBundle:Estacion')->findOneById($data["estacionDestino"]);
             $object->setEstacionDestino($destino);
             
             $object->setTarifaValor($data["tarifaValor"]);
             $objects[] = $object;
        }
        
        else if($data["type"] == JobType::TYPE_SYNC_ITINERARIO_CICLICO){
            $object = $this->doctrine->getRepository('MayaBundle:ItinerarioSimple')->findOneByIdExterno($data["id"]);
             if(is_null($object)){
                $object = new ItinerarioSimple();
                $object->setIdExterno($data["id"]);
                var_dump("Creando itinerario ciclico: ".$object->getId());
             }else{
                var_dump("Actualizado itinerario ciclico: ".$object->getId());
             }
             
             $object->setActivo($data["activo"]);
             
             $tipoBus = $this->doctrine->getRepository('MayaBundle:TipoBus')->findOneById($data["tipoBus"]);
             $object->setTipoBus($tipoBus);
             
             $ruta = $this->doctrine->getRepository('MayaBundle:Ruta')->findOneByCodigo($data["ruta"]);
             $object->setRuta($ruta);
             
             $dia = $this->doctrine->getRepository('MayaBundle:DiaSemana')->findOneById($data["diaSemana"]);
             $object->setDiaSemana($dia);
             
             $horario = $this->doctrine->getRepository('MayaBundle:HorarioCiclico')->findOneById($data["horarioCiclico"]);
             $object->setHorarioCiclico($horario);
             $objects[] = $object;
        }
        
        else if($data["type"] == JobType::TYPE_SYNC_ITINERARIO_ESPECIAL){
             $object = $this->doctrine->getRepository('MayaBundle:ItinerarioEspecial')->findOneByIdExterno($data["id"]);
             if(is_null($object)){
                $object = new ItinerarioEspecial();
                $object->setIdExterno($data["id"]);
                var_dump("Creando itinerario especial: ".$object->getId());
             }else{
                var_dump("Actualizado itinerario especial: ".$object->getId());
             }
             
             $object->setActivo($data["activo"]);
             $object->setFecha(\DateTime::createFromFormat('d-m-Y H:i:s', $data["fecha"]));
             
             $tipoBus = $this->doctrine->getRepository('MayaBundle:TipoBus')->findOneById($data["tipoBus"]);
             $object->setTipoBus($tipoBus);
             
             $ruta = $this->doctrine->getRepository('MayaBundle:Ruta')->findOneByCodigo($data["ruta"]);
             $object->setRuta($ruta);
             
             $estacion = $this->doctrine->getRepository('MayaBundle:Estacion')->findOneById($data["estacion"]);
             $object->setEstacion($estacion);
             $objects[] = $object;
        }
        
        else if($data["type"] == JobType::TYPE_SYNC_SALIDA){
             $object = $this->doctrine->getRepository('MayaBundle:ConexionSimple')->findOneByIdExterno($data["id"]);
             if(is_null($object)){
                $object = new ConexionSimple();
                $object->setIdExterno($data["id"]);
                var_dump("Creando salida: ".$object->getId());
             }else{
                var_dump("Actualizado salida: ".$object->getId());
             }

             $object->setFechaViaje(\DateTime::createFromFormat('d-m-Y H:i:s', $data["fecha"]));
             
             $tipoBus = $this->doctrine->getRepository('MayaBundle:TipoBus')->find($data["tipoBus"]);
             $object->setTipoBus($tipoBus);
             
             $estado = $this->doctrine->getRepository('MayaBundle:EstadoConexion')->find($data["estado"]);
             $object->setEstado($estado);
             
             $itinerario = $this->doctrine->getRepository('MayaBundle:ItinerarioInterno')->findOneByIdExterno($data["itinerario"]);
             $object->setItinerario($itinerario);
             $objects[] = $object;
             
             if(!$this->isNewId($object->getId()) && $estado->getId() === \Acme\MayaBundle\Entity\EstadoConexion::CANCELADA){
                 $conexionesCompuestas = $this->doctrine->getRepository('MayaBundle:ConexionCompuesta')
                         ->getConexionesCompuestasByConexionSimple($object);
                 foreach ($conexionesCompuestas as $item) {
                     var_dump("Actualizado conexion compuesta: ".$item->getId() . " asociada a salida: ". $object->getId());
                     $item->setActiva(false);
                     $objects[] = $item;
                 }
             }
        }
        
        else if($data["type"] == JobType::TYPE_SYNC_TIEMPO){
            $object = $this->doctrine->getRepository('MayaBundle:Tiempo')->findOneById($data["id"]);
             if(is_null($object)){
                $object = new Tiempo();
                $object->setId($data["id"]);
                var_dump("Creando tiempo: ".$object->getId());
             }else{
                var_dump("Actualizado tiempo: ".$object->getId());
             }

             $claseB = $this->doctrine->getRepository('MayaBundle:ClaseBus')->findOneById($data["claseBus"]);
             $object->setClaseBus($claseB);
             
             $ruta = $this->doctrine->getRepository('MayaBundle:Ruta')->findOneByCodigo($data["ruta"]);
             $object->setRuta($ruta);
             
             $estacion = $this->doctrine->getRepository('MayaBundle:Estacion')->findOneById($data["estacionDestino"]);
             $object->setEstacionDestino($estacion);
             
             $object->setMinutos($data["minutos"]);
             $objects[] = $object;
        }
        
        return $objects;
    }
    
    public function doRequestIntegration($url, $arrayData = null){
        
        $idApp = $this->container->getParameter("id_empresa_app");
        $now = new \DateTime();
        $dataWeb = $now->format('Y-m-d'). "_system_web_".$idApp;
        $claveInterna = $this->container->getParameter("internal_sys_clave_interna");
        $tokenAutLocal = \Acme\BackendBundle\Services\UtilService::encrypt($claveInterna, $dataWeb);
        
        $data = array( "idWeb" => $idApp, "tokenAut" => $tokenAutLocal );
        if($arrayData !== null){
            $data['data'] = json_encode($arrayData);
        }
            
        $postdata = http_build_query($data);
        $options = array(
              'http' => array(
              'method'  => 'POST',
              'content' => $postdata,
              'header'  => "Content-type: application/x-www-form-urlencoded\r\n"));
        $context  = stream_context_create( $options );
        $result = file_get_contents($url, false, $context );
        return $result;
    }
    
    public function isNewId($id)
    {
        return ($id === null || trim($id) === "" || trim($id) === "0");
    }

}
