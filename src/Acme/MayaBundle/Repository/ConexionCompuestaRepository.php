<?php

namespace Acme\MayaBundle\Repository;

use Doctrine\ORM\EntityRepository;
class ConexionCompuestaRepository extends EntityRepository
{
    
    public function listarConexiones($estacionOrigen, $estacionDestino, $fechaSalida)
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
        $fechaInit->setTime(0, 0, 0);
        $fechaInit = $fechaInit->format('Y-m-d H:i:s');
        $fechaEnd = clone $fechaSalida;
        $fechaEnd->setTime(23, 59, 59); //Hora, minuto, y segundos
        $fechaEnd = $fechaEnd->format('Y-m-d H:i:s');
        
        $query =  " SELECT "
                . " DISTINCT cc "
                . " FROM "
                . " Acme\MayaBundle\Entity\ConexionCompuesta cc "
                . " INNER JOIN cc.itinerarioCompuesto ic "
                . " INNER JOIN ic.estacionOrigen eo "
                . " INNER JOIN ic.estacionDestino ed "
                . " WHERE "
                . " (cc.fechaViaje BETWEEN :fechaInit AND :fechaEnd) "
                . " and (cc.activa=1 )"
                . " and (eo.id = :estacionOrigen and ed.id = :estacionDestino)"
                . " ORDER BY "
                . " cc.id ASC ";
        
    
        $items = $this->getEntityManager()->createQuery($query)
            ->setParameter('estacionOrigen', $idEstacionOrigen)
            ->setParameter('estacionDestino', $idEstacionDestino)
            ->setParameter('fechaInit', $fechaInit)
            ->setParameter('fechaEnd', $fechaEnd)
            ->getResult();
        return $items;
    }
	
    public function getConexionesCompuestasByIntinerarioCompuesto($idItinerarioCompuesto, $fechaInit, $fechaEnd)
    {
        $query =  " SELECT cc FROM Acme\MayaBundle\Entity\ConexionCompuesta cc "
            . " INNER JOIN cc.itinerarioCompuesto ic "
            . " WHERE ic.id = :idItinerarioCompuesto "
            . " and (cc.fechaViaje BETWEEN :fechaInit AND :fechaEnd) "
            . " ORDER BY cc.fechaViaje DESC ";
        
        $items = $this->_em->createQuery($query)
            ->setParameter('idItinerarioCompuesto', $idItinerarioCompuesto)
            ->setParameter('fechaInit', $fechaInit->format('d-m-Y H:i:s'))
            ->setParameter('fechaEnd', $fechaEnd->format('d-m-Y H:i:s'))
            ->getResult();
        return $items;
    }
    
    public function getConexionesCompuestasByConexionSimple($conexionSimple)
    {
        if($conexionSimple instanceof \Acme\MayaBundle\Entity\ConexionSimple){
            $conexionSimple = $conexionSimple->getId();
        }
        
        $query =  " SELECT cc FROM Acme\MayaBundle\Entity\ConexionCompuesta cc "
                . " INNER JOIN cc.listaConexionItem ci "
                . " INNER JOIN ci.conexionSimple cs "
                . " WHERE "
                . " cs.id = :idConexionSimple ";
        
        $items = $this->_em->createQuery($query)
            ->setParameter('idConexionSimple', $conexionSimple)
            ->getResult();
        return $items;
    }
}

?>
