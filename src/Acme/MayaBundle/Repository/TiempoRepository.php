<?php

namespace Acme\MayaBundle\Repository;

use Doctrine\ORM\EntityRepository;

class TiempoRepository extends EntityRepository
{
    
   public function getTiempoConexionCompleta ($origen, $destino, $ruta, $claseBus){
       
        $query =  " SELECT "
                . " t1.minutos as destino, t2.minutos as origen "
                . " FROM "
                . " Acme\MayaBundle\Entity\Tiempo t1 "
                . " INNER JOIN t1.ruta r1 "
                . " INNER JOIN t1.estacionDestino ed1 "
                . " INNER JOIN t1.claseBus c1, "
                . " Acme\MayaBundle\Entity\Tiempo t2 "
                . " INNER JOIN t2.ruta r2 "
                . " INNER JOIN t2.estacionDestino ed2 "
                . " INNER JOIN t2.claseBus c2 "
                . " WHERE "
                . " (ed1.id=:estacionDestino)"
                . " and (r1.codigo=:ruta)"
                . " and (c1.id=:clase) "
                . " and (ed2.id=:estacionOrigen)"
                . " and (r2.codigo=:ruta)"
                . " and (c2.id=:clase) "
                . " GROUP BY "
                . " t1.minutos, t2.minutos";
                
        
    
        $result = $this->getEntityManager()->createQuery($query)
            ->setParameter('estacionDestino', $destino)
            ->setParameter('estacionOrigen', $origen)
            ->setParameter('ruta', $ruta)
            ->setParameter('clase', $claseBus)
            ->getResult();
        return $result;
       
   }
   public function getTiempoConexion($destino, $ruta, $claseBus){
       
       $query =  "  SELECT t "
                . " FROM Acme\MayaBundle\Entity\Tiempo t "
                . " INNER JOIN t.ruta r "
                . " INNER JOIN t.estacionDestino ed "
                . " INNER JOIN t.claseBus c "
                . " WHERE "
                . " (ed.id=:estacionDestino)"
                . " and (r.codigo=:ruta)"
                . " and (c.id=:clase)";
                
        $result = $this->getEntityManager()->createQuery($query)
            ->setParameter('estacionDestino', $destino)
            ->setParameter('ruta', $ruta)
            ->setParameter('clase', $claseBus)
            ->getResult();
        
        if(count($result) === 0)
            return null;

        return $result[0];
   }
}

?>
