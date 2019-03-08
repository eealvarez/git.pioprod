<?php

namespace Acme\MayaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Acme\MayaBundle\Entity\Nacionalidad;
use Acme\MayaBundle\Entity\Documento;

class ClienteRepository extends EntityRepository
{
    
    public function checkExisteCliente($nacionalidad, $tipoDocumento, $numeroDocumento, $nombreApellidos, $nit, $id = null)
    {
        if($nacionalidad instanceof Nacionalidad){
            $nacionalidad = $nacionalidad->getId();
        }
        
        if($tipoDocumento instanceof Documento){
            $tipoDocumento = $tipoDocumento->getId();
        }
        
        try {
//            var_dump($id);
            $query =  " SELECT c FROM Acme\MayaBundle\Entity\Cliente c "
                    . " INNER JOIN c.tipoDocumento t "
                    . " INNER JOIN c.nacionalidad n "
                    . " WHERE ";
            
            $subquery1 = " c.nombreApellidos=:nombreApellidos ";
            $subquery2 = "";  //se valida NIT Y nombre
            $subquery3 = "";
            
            if($numeroDocumento !== null && trim($numeroDocumento) !== ""){
                $subquery2 .= " or (n.id=:nacionalidad and t.id=:tipoDocumento and c.numeroDocumento=:numeroDocumento) ";
                $subquery1 .= " and (n.id=:nacionalidad and t.id=:tipoDocumento and c.numeroDocumento=:numeroDocumento) ";
            }else{
                $subquery1 .= " and ( c.numeroDocumento is null and n.id=:nacionalidad ) ";
            }
            
            if($nit !== null && trim($nit) !== "" && trim($nit) !== "C/F"){
                $subquery2 .= " or (c.nit=:nit and c.nombreApellidos=:nombreApellidos) ";
                $subquery1 .= " and (c.nit=:nit and c.nombreApellidos=:nombreApellidos) ";
            }else{
                $subquery1 .= " and c.nit='C/F' ";
            }
            
            if($id !== null && trim($id) !== ""){
                $subquery3 .= " and c.id<>:id ";
            }
            
            $query .= " (( " .$subquery1 . " ) " . $subquery2 . " ) ". $subquery3;
            
            $query = $this->_em->createQuery($query)
                    ->setMaxResults(1)
                    ->setParameter('nombreApellidos', $nombreApellidos)
                    ->setParameter('nacionalidad', $nacionalidad);
            
            if($numeroDocumento !== null && trim($numeroDocumento) !== ""){
                $query->setParameter('numeroDocumento', $numeroDocumento);
                $query->setParameter('tipoDocumento', $tipoDocumento);
            }
            
            if($nit !== null && trim($nit) !== "" && trim($nit) !== "C/F"){
                $query->setParameter('nit', $nit);
            }
            
            if($id !== null && trim($id) !== ""){
                $query->setParameter('id', $id);
            }
            
            $primerCliente = $query->getSingleResult();
            
            if($primerCliente !== null){
                if($primerCliente->getNit() === $nit && trim($primerCliente->getNit()) !== "C/F"){
                    return array(
                        "existe" => true,
                        "cliente" => $primerCliente
                    );
                }else if($primerCliente->getNumeroDocumento() === $numeroDocumento && trim($primerCliente->getNumeroDocumento()) !== ""){
                    
                    return array(
                        "existe" => true,
                        "cliente" => $primerCliente
                    );
                }else if(strtoupper($primerCliente->getNombreApellidos()) === strtoupper($nombreApellidos)){
                    return array(
                        "existe" => true,
                        "cliente" => $primerCliente,
                    );
                }else{
                    return array(
                        "existe" => true,
                        "cliente" => $primerCliente,
                     );
                }
            } else{
                return array(
                    "existe" => false
                );
            }
         } catch (\Doctrine\ORM\NoResultException $exc) {
             return array(
                "existe" => false
             );
         }
    }
    
    
}

?>
