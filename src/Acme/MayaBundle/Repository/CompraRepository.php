<?php

namespace Acme\MayaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Acme\BackendBundle\Services\UtilService;

class CompraRepository extends EntityRepository
{
    
    public function getComprasPaginados($page, $rows, $sort, $order, $searchPhrase = "", $usuario)
    {
        if(!is_int($page)){
            $page = intval($page);
        }
        if($page <= 0){
            $page = 0;
        }else{
            $page = $page - 1;
        }
        
        if(!is_int($rows)){
            $rows = intval($rows);
        }
        if($rows < 0){
            $rows = 10;
        }else if($rows > 100){
            $rows = 100;
        }
        
        $viewFull = false;
        $idEstacionesUsuarioFilter = array();
        if($usuario instanceof \Acme\BackendBundle\Entity\User){
            $estacionesUsuarioFilter = $usuario->getEstaciones();
            foreach ($estacionesUsuarioFilter as $estacion) {
                $idEstacionesUsuarioFilter[] = $estacion->getId();
            }
            
            if($usuario->hasRole("ROLE_GESTION_COMPRAS")){
                $viewFull = true;
            }
        }

        $queryStr =   " FROM Acme\MayaBundle\Entity\Compra co "
                    . " LEFT JOIN co.estacionFactura ef "
                    . " LEFT JOIN co.estado es "
                    . " LEFT JOIN co.cliente cl "
                    . " LEFT JOIN co.factura fa "
                    . " LEFT JOIN fa.estado efa "
                    . " WHERE "
                    . " ef.id IN (:idEstacionesUsuarioFilter) ";
        
        if($viewFull === false){
            $queryStr .= " and efa.id IN (2,3,4) ";
        }
        
        if($searchPhrase !== null && trim($searchPhrase) !== ""){
            $queryStr .= " and ( es.nombre like :searchPhraseLike or ef.nombre like :searchPhraseLike "
                    . " or cl.nombreApellidos like :searchPhraseLike or fa.correlativo like :searchPhraseLike "
                    . " or efa.nombre like :searchPhraseLike ";
            if(is_numeric($searchPhrase)){
                $queryStr .= " or co.id=:searchPhraseEqual ";
            }
            $queryStr .= " ) ";
        }
        
        $queryOrder = " co.id DESC ";
        
        $query = $this->_em->createQuery(" SELECT co " . $queryStr . " ORDER BY " . $queryOrder)->setMaxResults($rows)->setFirstResult($page * $rows);
        UtilService::setParameterToQuery($query, "idEstacionesUsuarioFilter", $idEstacionesUsuarioFilter, false);
        
        if($searchPhrase !== null && trim($searchPhrase) !== ""){
            if(is_numeric($searchPhrase)){
                UtilService::setParameterToQuery($query, "searchPhraseEqual", $searchPhrase, false);
            }
            UtilService::setParameterToQuery($query, "searchPhraseLike", "%".$searchPhrase."%", false);
        }
        
        $items = $query->getResult();
        
        $query = $this->_em->createQuery(" SELECT count(co) " .$queryStr);
        UtilService::setParameterToQuery($query, "idEstacionesUsuarioFilter", $idEstacionesUsuarioFilter, false);
        if($searchPhrase !== null && trim($searchPhrase) !== ""){
            if(is_numeric($searchPhrase)){
                UtilService::setParameterToQuery($query, "searchPhraseEqual", $searchPhrase, false);
            }
            UtilService::setParameterToQuery($query, "searchPhraseLike", "%".$searchPhrase."%", false);
        }
        $total =  $query->getSingleScalarResult();
        
        return array(
            'items' => $items,
            'total' => $total,
            'current' => $page,
            'rowCount' => $rows
        );
    }
    
    public function getComprasPendientesNotificar()
    {
        $query =    " SELECT c "
                      . " FROM Acme\MayaBundle\Entity\Compra c "
                      . " INNER JOIN c.estado e "
                      . " WHERE "
                      . " c.notificada=0 and e.id IN (2,3) ";
            
        return $this->getEntityManager()->createQuery($query)->getResult();
    }
    
    public function getComprasByFacturasRecibidas()
    {
        $query =    " SELECT c "
                      . " FROM Acme\MayaBundle\Entity\Compra c "
                      . " INNER JOIN c.factura f "
                      . " INNER JOIN f.estado e "
                      . " WHERE "
                      . " f.notificada=0 and e.id=3 ";
            
        return $this->getEntityManager()->createQuery($query)->getResult();
    }
    
    public function findCompraPagada($idCompra){
        $query =  " SELECT c FROM Acme\MayaBundle\Entity\Compra c "
                . " WHERE "
                . " c.estado=2 "
                . " and c.id = :idCompra) "
                . " ORDER BY "
                . " c.id ASC ";

        $items = $this->_em->createQuery($query)
                ->setMaxResults(1)
                ->setParameter('idCompra', $idCompra)
                ->getResult();
        return $items;
    }
}

?>
