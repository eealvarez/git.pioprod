<?php

namespace Acme\BackendBundle\Services;

use Acme\BackendBundle\Scheduled\ScheduledServiceInterface;
use Acme\BackendBundle\Entity\Job;
use Acme\MayaBundle\Entity\DiaSemana;
use Acme\MayaBundle\Entity\ItinerarioCompuesto;
use Acme\MayaBundle\Entity\ConexionCompuesta;
use Acme\MayaBundle\Entity\ConexionItem;
use Acme\BackendBundle\Services\UtilService;

class ConexionService implements ScheduledServiceInterface{
      
   protected $container;
   protected $doctrine;
   protected $logger;
   protected $options;
   protected $cantidadMeses;
   protected $job;
      
   public function __construct($container) { 
        $this->container = $container;
        $this->doctrine = $this->container->get('doctrine');
        $this->logger = $this->container->get('logger');
        $this->options = array(
            'flush' => true
        );
        $this->cantidadMeses = 4;
        $this->job = null;
   }
   
   private function getCurrentFecha(){
        if($this->job === null){
            return new \DateTime();
        }else{
            return clone $this->job->getNextExecutionDate();
        }
    }
    
    //SE EJECUTA DESDE EL MODULO DE ITINERARIO COMPUESTO, SOLO SE PUEDE MODIFICAR EL ATRIBUTO ACTIVO.
    public function procesarConexionPorItinerarioCompuesto(ItinerarioCompuesto $itinerario, $options = null){
        $this->logger->warn("procesarConexionPorItinerarioCompuesto ----- INIT -------");
        $detalleLog = "ITINERARIO:".$itinerario->getId().". ";
        $this->logger->warn($detalleLog);
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }
        
        $em = $this->doctrine->getManager();
        $fechaLimiteSistema = new \DateTime();
        $fechaLimiteSistema->modify('+'. $this->cantidadMeses.' month');
        $fechaLimiteSistema->setTime(23, 59, 0);
        $fechaActualSistema = new \DateTime();
        $fechaActual = clone $fechaActualSistema;
        $fechaActual->modify('-1 day');
        $nuevo = $itinerario->getId() === null || trim($itinerario->getId()) === "" || trim($itinerario->getId()) === "0";  
        $mapConexiones = array();
        if($nuevo === false){
            $items = $this->doctrine->getRepository('MayaBundle:ConexionCompuesta')
                    ->getConexionesCompuestasByIntinerarioCompuesto($itinerario->getId(), $fechaActual, $fechaLimiteSistema);           
            $mapConexiones = $this->getMapConexiones($items);
        }
        
        if($itinerario->getActivo() === true){
            
            $horario = $itinerario->getHorarioCiclico()->getHora();
            $hour = $horario->format('H');
            $minute = $horario->format('i');
            $diaSemana = $itinerario->getDiaSemana()->getPHPValue();
            $fechaActual->modify('next '.$diaSemana);
            $fechaActual->setTime($hour, $minute, 0);
            
            while (UtilService::compararFechas($fechaActual, $fechaLimiteSistema) <= 0) {
                //Buscar la salida que corresponde a la semana de de cada fecha
                $conexion = $this->buscarConexion($fechaActual, $mapConexiones);
                if($conexion === null){
                        $this->logger->warn($detalleLog."Creando conexion compuesta-init");
                        $conexion = new ConexionCompuesta();
                        $conexion->setItinerarioCompuesto($itinerario);
                        $conexion->setFechaViaje(clone $fechaActual);
                        $fechaInit = clone $fechaActual;
                        $fechaInit->modify('-1 day');
                        $fechaInit->setTime(0, 0, 0);
                        $fechaEnd = clone $fechaActual;
                        $fechaEnd->modify('+1 day');
                        $fechaEnd->setTime(23, 59, 59);
                        $listaItinerarioItem = $itinerario->getListaItinerarioItemOrder();
                        foreach ($listaItinerarioItem as $item) {
                            $conexionItem = new ConexionItem();
                            $conexionesSimples = $this->doctrine->getRepository('MayaBundle:ConexionSimple')
                                    ->getConexionesSimplesByItinerarioSimple($item->getListaIdItinerariosSimple(), $fechaInit, $fechaEnd);
                            if(count($conexionesSimples) !== 1){
                                $this->logger->warn($detalleLog."No se pudo generar la conexion compuesta pq no se encontro la conexion simple del orden " . $item->getOrden());
                                break;
                            }
                            $conexionItem->setConexionSimple($conexionesSimples[0]);
                            $conexionItem->setOrden($item->getOrden());
                            $conexionItem->setItinerarioItem($item);
                            $conexion->addListaConexionItem($conexionItem);
                        }
                        
                        $this->logger->warn($detalleLog. "Items: " . count($conexion->getListaConexionItem()));
                        if(count($conexion->getListaConexionItem()) === 0){
                            $this->logger->warn($detalleLog."No se pudo generar la conexion compuesta pq no tiene items.");
                        }else{
                            $em->persist($conexion);
                        }
                        
//                        $this->logger->warn("Generando conexion compuesta para fecha:" . $fechaActual->format('d-m-Y H:i:s'). "-end");
                 }else{
                     //En caso de que las conexiones no esten activas, se dejan asi, y se debe activar cada una de forma manual.
                     //Esto para evitar activarlas todas automaticamente, si se queria una especifica desactivada.
                 }
                 
                 $fechaActual->modify('next '.$diaSemana);
                 $fechaActual->setTime($hour, $minute, 0);
           }
        }else{
            foreach ($mapConexiones as $fecha => $conexion) {
                $conexion->setActiva(false);
                $em->persist($conexion);
            }
        }
        
        if($options['flush'] === true){
            $em->flush();
        }
        $this->logger->warn("procesarConexionPorItinerarioCompuesto ----- END -------");
     }
     
     //SE EJECUTA DE FORMA PERIODICA, SE SUPONE QUE NO HAY CAMBIOS SOLO GENERAR LAS NUEVAS CONEXIONES COMPUESTAS.
     public function procesarConexionPorItinerarioCompuestoFormaPeriodica($options = null){
        $this->logger->warn("procesarConexionPorItinerarioCompuestoFormaPeriodica ----- INIT -------");
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }
        
        $em = $this->doctrine->getManager();
        $fechaLimiteSistema = $this->getCurrentFecha();
        $fechaLimiteSistema->modify('+'.$this->cantidadMeses.' month');
        $fechaLimiteSistema->setTime(23, 59, 0);
        $itinerarioCompuestos = $this->doctrine->getRepository('MayaBundle:ItinerarioCompuesto')->findByActivo(true);
        $this->logger->warn("Cantidad de itinerarios compuestos detectados: " . count($itinerarioCompuestos) . ".");
        foreach ($itinerarioCompuestos as $itinerario) {
             $detalleLog = "ITINERARIO:".$itinerario->getId().". ";
             if($itinerario->getActivo() === true){
                 $this->logger->warn("idItinerario:" . $itinerario->getId(). ", activo:true");
                 $fechaActualSistema = $this->getCurrentFecha();
                 $fechaActualSistema->modify('-1 day');
                 $items = $this->doctrine->getRepository('MayaBundle:ConexionCompuesta')
                    ->getConexionesCompuestasByIntinerarioCompuesto($itinerario->getId(), $fechaActualSistema, $fechaLimiteSistema);
                 $mapConexiones = $this->getMapConexiones($items);
                 $horario = $itinerario->getHorarioCiclico()->getHora();
                 $hour = $horario->format('H');
                 $minute = $horario->format('i');
                 $diaSemana = $itinerario->getDiaSemana()->getPHPValue();
                 $fechaActualSistema->modify('next '.$diaSemana);
                 $fechaActualSistema->setTime($hour, $minute, 0);
                 
                 while (UtilService::compararFechas($fechaActualSistema, $fechaLimiteSistema) <= 0) {
                     if(!array_key_exists($fechaActualSistema->format('d-m-Y H:i:s'), $mapConexiones)){
                            $this->logger->warn($detalleLog."Generando conexion compuesta para fecha:" . $fechaActualSistema->format('d-m-Y H:i:s') . "-init");
                            $conexion = new ConexionCompuesta();
                            $conexion->setItinerarioCompuesto($itinerario);
                            $conexion->setFechaViaje(clone $fechaActualSistema);
                            $fechaInit = clone $fechaActualSistema;
                            $fechaInit->modify('-1 day');
                            $fechaInit->setTime(0, 0, 0);
                            $fechaEnd = clone $fechaActualSistema;
                            $fechaEnd->modify('+1 day');
                            $fechaEnd->setTime(23, 59, 59);
                            $listaItinerarioItem = $itinerario->getListaItinerarioItemOrder();
                            foreach ($listaItinerarioItem as $item) {
                                $conexionItem = new ConexionItem();
                                $conexionesSimples = $this->doctrine->getRepository('MayaBundle:ConexionSimple')
                                        ->getConexionesSimplesByItinerarioSimple($item->getListaIdItinerariosSimple(), $fechaInit, $fechaEnd);
                                if(count($conexionesSimples) !== 1){
                                    $this->logger->warn($detalleLog."No se pudo generar la conexion compuesta pq no se encontro la conexion simple del orden " . $item->getOrden());
                                    break;
                                }
                                $conexionItem->setConexionSimple($conexionesSimples[0]);
                                $conexionItem->setOrden($item->getOrden());
                                $conexionItem->setItinerarioItem($item);
                                $conexion->addListaConexionItem($conexionItem);
                            }
                        
                            $this->logger->warn($detalleLog. "Items: " . count($conexion->getListaConexionItem()));
                            if(count($conexion->getListaConexionItem()) === 0){
                                $this->logger->warn($detalleLog."No se pudo generar la conexion compuesta pq no tiene items.");
                            }else{
                                $em->persist($conexion);
                            }
                            
                     }else{
                         $this->logger->warn($detalleLog."Ya existe conexion para fecha:" . $fechaActualSistema->format('d-m-Y H:i:s'));
                     }
                     $fechaActualSistema->modify('next '.$diaSemana);
                     $fechaActualSistema->setTime($hour, $minute, 0);
                 }
                         
             }else{
                 $this->logger->warn($detalleLog."El itinerario no esta activo.");
             }
         }

         if($options['flush'] === true){
            $this->logger->warn("flust---init");
            $em->flush();
            $this->logger->warn("flust---end");
         }
         $this->logger->warn("procesarConexionPorItinerarioCompuestoFormaPeriodica ----- END -------");
     }
     
     public function buscarConexion($fechaActualSistema, $mapConexiones){
         $fechaRangoInit = clone $fechaActualSistema;
         if($fechaRangoInit->format("l") !== "Sunday"){
             $fechaRangoInit->modify('last ' . DiaSemana::DOMINGO);
         }
         $fechaRangoInit->setTime(0, 0, 0);
         $fechaRangoFin = clone $fechaActualSistema;
         if($fechaRangoFin->format("l") !== "Saturday"){
             $fechaRangoFin->modify('next ' . DiaSemana::SABADO);
         }
         $fechaRangoFin->setTime(23, 59, 0);
         foreach ($mapConexiones as $fecha => $conexion) {
            if(UtilService::compararFechas($fechaRangoInit, $fecha) <= 0 && UtilService::compararFechas($fecha, $fechaRangoFin) <= 0){
                return $conexion;
            }   
         }
         return null;
     }
     
     public function getMapConexiones($items){
         $result = array();
         foreach ($items as $item) {
             $clave = $item->getFechaViaje()->format('d-m-Y H:i:s');
             $result[$clave] = $item;
         }
         return $result;
     }
     
    public function setScheduledJob(Job $job = null) {
        $this->logger->warn("Conexion Service - init");
        $this->job = $job;
        $this->procesarConexionPorItinerarioCompuestoFormaPeriodica();
        $this->logger->warn("Conexion Service - end");
    }

}

?>
