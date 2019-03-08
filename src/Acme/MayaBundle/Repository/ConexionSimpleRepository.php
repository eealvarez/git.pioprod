<?php

namespace Acme\MayaBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ConexionSimpleRepository extends EntityRepository
{
    
    public function listarConexiones($estacionOrigen, $estacionDestino, $fechaSalida, $cantBoletos)
    {
        if($fechaSalida === null || $fechaSalida === false || (is_string($fechaSalida) && trim($fechaSalida) === "") || $estacionOrigen === null || trim($estacionOrigen) === "" || 
                $estacionDestino === null || trim($estacionDestino) === ""){
            return array();
        }
        
        $idEstacionOrigen = $estacionOrigen;
        if($estacionOrigen instanceof Estacion){
            $idEstacionOrigen = $estacionOrigen->getId();
        }
        $idEstacionDestino = $estacionDestino;
        if($estacionDestino instanceof Estacion){
            $idEstacionDestino = $estacionDestino->getId();
        }
        
        if(is_string($fechaSalida)){
            $fechaSalida = \DateTime::createFromFormat('Y-m-d', $fechaSalida);
        }
        $fechaInit = clone $fechaSalida;
        $fechaInit->modify("-1 day");
        $fechaInit->setTime(0, 0, 0);
        $fechaInit = $fechaInit->format('Y-m-d H:i:s');
        $fechaEnd = clone $fechaSalida;
        $fechaEnd->setTime(23, 59, 59); //Hora, minuto, y segundos
        $fechaEnd = $fechaEnd->format('Y-m-d H:i:s');
//        $q = new Doctrine_RawSql();
//        $q->select('{t.minutos}, {tb.tarifaValor}, {cs.*}')->from();
        $query =  " SELECT "
                . " DISTINCT cs "
                . " FROM "
                . " Acme\MayaBundle\Entity\ConexionSimple cs "
                . " INNER JOIN cs.tipoBus cstb "
                . " INNER JOIN cs.itinerario i "
                . " INNER JOIN i.ruta r "
                . " INNER JOIN cs.estado e "
                . " INNER JOIN r.estacionOrigen eo "
                . " INNER JOIN r.estacionDestino ed "
                . " INNER JOIN r.listaEstacionesIntermediaOrdenadas l1 "
                . " INNER JOIN l1.estacion ei1 "
                . " INNER JOIN r.listaEstacionesIntermediaOrdenadas l2 "
                . " INNER JOIN l2.estacion ei2 "
                . " WHERE "
                . " (e.id <> 3 and e.id <> 4 and e.id <> 5)"
                . " and (cs.fechaViaje BETWEEN :fechaInit AND :fechaEnd) "
                . " and ((cs.cantVendidos + :cantBoletos) <= cstb.totalAsientos) "
                . " and ((eo.id = :estacionOrigen and ed.id = :estacionDestino)"
                . " or (eo.id = :estacionOrigen and ei1.id = :estacionDestino)"
                . " or (ei1.id = :estacionOrigen and ed.id = :estacionDestino)"
                . " or (ei1.id = :estacionOrigen and ei2.id = :estacionDestino and l1.posicion < l2.posicion))"
                . " ORDER BY "
                . " cs.id ASC ";
        
        
        $items = $this->getEntityManager()->createQuery($query)
            ->setParameter('estacionOrigen', $idEstacionOrigen)
            ->setParameter('estacionDestino', $idEstacionDestino)
            ->setParameter('fechaInit', $fechaInit)
            ->setParameter('fechaEnd', $fechaEnd)
            ->setParameter('cantBoletos', intval($cantBoletos))
            ->getResult();
//        if($items == null)
        
        return $items;
    }
    
    public function getConexionesSimplesByItinerarioSimple($idItinerariosInternos = array(), $fechaInit, $fechaEnd)
    {
        $query =  " SELECT cs FROM Acme\MayaBundle\Entity\ConexionSimple cs "
                . " INNER JOIN cs.itinerario it "
                . " WHERE "
                . " it.activo=1 "
                . " and it.id IN (:idItinerariosInternos) "
                . " and (cs.fechaViaje BETWEEN :fechaInit AND :fechaEnd) "
                . " ORDER BY "
                . " cs.fechaViaje DESC ";
        
        $items = $this->_em->createQuery($query)
                ->setMaxResults(1)
                ->setParameter('idItinerariosInternos', $idItinerariosInternos)
                ->setParameter('fechaInit', $fechaInit->format('d-m-Y H:i:s'))
                ->setParameter('fechaEnd', $fechaEnd->format('d-m-Y H:i:s'))
                ->getResult();
        return $items;
    }
    
    
}

?>
