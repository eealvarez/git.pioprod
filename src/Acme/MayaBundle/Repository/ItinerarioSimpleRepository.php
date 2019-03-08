<?php

namespace Acme\MayaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Acme\MayaBundle\Entity\DiaSemana;
use Acme\MayaBundle\Entity\Estacion;

class ItinerarioSimpleRepository extends EntityRepository
{
     
    public function listarItinerariosSimples($diaSemana = null, $estacion = null)
    {
        if($diaSemana instanceof DiaSemana){
            $diaSemana = $diaSemana->getId();
        }
        
        if($estacion instanceof Estacion){
            $estacion = $estacion->getId();
        }
        
        $hql =    " SELECT it "
                . " FROM Acme\MayaBundle\Entity\ItinerarioSimple it "
                . " LEFT JOIN it.diaSemana ds "
                . " LEFT JOIN it.horarioCiclico hc "
                . " LEFT JOIN it.ruta ru "
                . " LEFT JOIN ru.estacionOrigen eo "
                . " LEFT JOIN ru.listaEstacionesIntermediaOrdenadas eio "
                . " LEFT JOIN eio.estacion ei "
                . " WHERE "
                . " it.activo=1 "
                ;
        
        if(!is_null($diaSemana) && trim($diaSemana) !== ""){
            $hql .= " and (ds.id=:diaMinimo or ds.id=:diaSemana or ds.id=:diaMaximo) ";
        }
        if(!is_null($estacion) && trim($estacion) !== ""){
            $hql .= " and (eo.id=:estacion or ei.id=:estacion) ";
        }
        $hql .=   " ORDER BY "
                . " ds.id, hc.hora ";
        
        $query = $this->_em->createQuery($hql);
        if(!is_null($diaSemana) && trim($diaSemana) !== ""){
            $diaMinimo = intval($diaSemana) - 1;
            if($diaMinimo <= 0){
                $diaMinimo = 7;
            }
            $diaMaximo = intval($diaSemana) + 1;
            if($diaMaximo > 7){
                $diaMaximo = 1;
            }
//            var_dump($diaMinimo);
//            var_dump($diaMaximo);
            $query->setParameter("diaSemana", $diaSemana);
            $query->setParameter("diaMinimo", $diaMinimo);
            $query->setParameter("diaMaximo", $diaMaximo);
        }
        if(!is_null($estacion) && trim($estacion) !== ""){
            $query->setParameter("estacion", $estacion);
        }
        return $query->getResult();
    }
}

?>
