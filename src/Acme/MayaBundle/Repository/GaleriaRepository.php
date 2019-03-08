<?php

namespace Acme\MayaBundle\Repository;

use Doctrine\ORM\EntityRepository;

class GaleriaRepository extends EntityRepository
{
    public function listarGaleriaActivas()
    {   
       
        $query =      " SELECT g FROM Acme\MayaBundle\Entity\Galeria g "
                    . " WHERE "
                    . " g.activo = 1 "
                    . " ORDER BY "
                    . " g.orden ASC  ";
        
        $items = $this->_em->createQuery($query)->getResult();
        return $items;
    }
    
    public function listarGaleriaByReferencia($referencia)
    {   
       
        $query =      " SELECT g FROM Acme\MayaBundle\Entity\Galeria g "
                    . " WHERE "
                    . " g.activo = 1 "
                    . " and g.referencia like :referencia "
                    . " ORDER BY "
                    . " g.orden ASC  ";
        
        $query = $this->_em->createQuery($query)
                      ->setParameter("referencia", $referencia . "%");
        return $query->getResult();
    }
}

?>
