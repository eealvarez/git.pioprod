<?php

namespace Acme\MayaBundle\Repository;

use Doctrine\ORM\EntityRepository;

class TarifaBoletoRepository extends EntityRepository
{
    public function getTarifaBoleto($idEstacionOrigen, $idEstacionDestino, $idClaseBus, $idClaseAsiento, $fechaSalida)
    {
           $fechaDia = new \DateTime();
           $query =  "SELECT ca.id as idA, ca.nombre as nombreA, e.tarifaValor as precioUnitario, e.id as idTarifa "
                   . " FROM Acme\MayaBundle\Entity\TarifaBoleto e "
                . " LEFT JOIN e.estacionOrigen eo "
                . " LEFT JOIN e.estacionDestino ed "
                . " LEFT JOIN e.claseBus cb "
                . " LEFT JOIN e.claseAsiento ca "
                . " WHERE "
                . " (eo.id = :idEstacionOrigen) and "
                . " (ed.id = :idEstacionDestino) and "
                . " (cb.id = :idClaseBus) and "
                . " (ca.id = :idClaseAsiento) and "
                . " (e.fechaEfectividad <= :fechaEfectividad) ";
            $query .= " ORDER BY e.fechaEfectividad DESC, e.id DESC ";
           
            
            $items = $this->getEntityManager()->createQuery($query)
                ->setParameter('idEstacionOrigen', $idEstacionOrigen)
                ->setParameter('idEstacionDestino', $idEstacionDestino)
                ->setParameter('idClaseBus', $idClaseBus)    
                ->setParameter('idClaseAsiento', $idClaseAsiento)
                ->setParameter('fechaEfectividad', $fechaDia->format('d-m-Y H:i:s'))
                ->getArrayResult();
            
            if(count($items) === 0)
                return null;
            $tarifaValor = number_format((float)$items[0]['precioUnitario'], 2);
            $tarifa = \Acme\BackendBundle\Services\UtilService::calcularTarifa($tarifaValor);
            $items[0]['precioUnitario'] = $tarifa;
            return $items[0];

    }
}

?>
