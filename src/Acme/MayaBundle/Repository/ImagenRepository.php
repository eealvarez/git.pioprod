<?php

namespace Acme\MayaBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ImagenRepository extends EntityRepository
{
    public function listarImagenesGaleria($id)
    {   
       
        $query =      " SELECT i FROM Acme\MayaBundle\Entity\Imagen i "
                    . " INNER JOIN i.galeria g "
                    . " WHERE "
                    . " g.id = :idGaleria and g.activo = 1 ";
        
        $items = $this->_em->createQuery($query)
                ->setParameter("idGaleria", intval($id))
                ->getResult();
        return $items;
    }
    
    public function listarImagenesGaleriaByReferencia($id, $referencia)
    {   
       
        $query =      " SELECT i FROM Acme\MayaBundle\Entity\Imagen i "
                    . " INNER JOIN i.galeria g "
                    . " WHERE "
                    . " g.id = :idGaleria and i.referencia = :referencia and g.activo = 1 ";
        
        $items = $this->_em->createQuery($query)
                ->setParameter("idGaleria", intval($id))
                ->setParameter("referencia", $referencia)
                ->getResult();
        return $items;
    }
}

?>
