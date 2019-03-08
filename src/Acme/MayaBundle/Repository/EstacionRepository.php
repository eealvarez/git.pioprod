<?php

namespace Acme\MayaBundle\Repository;

use Doctrine\ORM\EntityRepository;

class EstacionRepository extends EntityRepository
{
    public function getEstacionesActivasByDepartamento(){
        $consulta  = " SELECT e "
                   . " FROM Acme\MayaBundle\Entity\Estacion e "
                   . " INNER JOIN e.tipo t "
                   . " INNER JOIN e.departamento d "
                   . " WHERE " 
                   . " e.activo=1"
                   . " ORDER BY "
                   . " d.id, t.id "
                ;
        $query = $this->_em->createQuery($consulta);
        return $query->getResult();
    }
    
    public function getOficinasActivasByDepartamento(){
        $consulta  = " SELECT e "
                   . " FROM Acme\MayaBundle\Entity\Estacion e "
                   . " INNER JOIN e.tipo t "
                   . " INNER JOIN e.departamento d "
                   . " WHERE " 
                   . " e.activo=1 and t.id IN (1,2,4) "
                   . " ORDER BY "
                   . " d.id, t.id "
                ;
        $query = $this->_em->createQuery($consulta);
        return $query->getResult();
    }
    
    public function getAllEstacionesPrincipales(){
        $consulta  = " SELECT e "
                   . " FROM Acme\MayaBundle\Entity\Estacion e "
                   . " INNER JOIN e.tipo t "
                   . " WHERE " 
                   . " e.activo=1 and t.id IN (1,2) "
                   . " ORDER BY "
                   . " t.id "
                ;
        $query = $this->_em->createQuery($consulta);
        return $query->getResult();
    }
    public function getEstacionesActivas(){
        $consulta  = " SELECT e "
                   . " FROM Acme\MayaBundle\Entity\Estacion e "
                   . " INNER JOIN e.tipo t "
                   . " WHERE " 
                   . " e.activo=1"
                   . " ORDER BY "
                   . " t.id "
                ;
        $query = $this->_em->createQuery($consulta);
        return $query->getResult();
    }
    public function getEstacionesActivasExlusion($estacionExcluida){
        $consulta  = " SELECT e "
                   . " FROM Acme\MayaBundle\Entity\Estacion e "
                   . " INNER JOIN e.tipo t "
                   . " WHERE " 
                   . " e.activo=1 and e.id != ".$estacionExcluida
                   . " ORDER BY "
                   . " t.id "
                ;
        $query = $this->_em->createQuery($consulta);
        return $query->getResult();
    }
    
    public function getEstacionesFacturacion(){
        $consulta  = " SELECT e "
                   . " FROM Acme\MayaBundle\Entity\Estacion e "
                   . " WHERE " 
                   . " e.activo=1 and e.facturacion=1"
                   . " ORDER BY "
                   . " e.id "
                ;
        $query = $this->_em->createQuery($consulta);
        return $query->getResult();
    }
    
   
    
}
?>
