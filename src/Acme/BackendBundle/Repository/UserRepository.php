<?php

namespace Acme\BackendBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;


class UserRepository extends EntityRepository
{
    public function findExpiredCredentialsUser($fechaActual)
    {
        if($fechaActual === null || $fechaActual === false || (is_string($fechaActual) && trim($fechaActual) === "")){
            return array();
        }
        
        if(is_string($fechaActual)){
            $fechaActual = \DateTime::createFromFormat('d-m-Y', $fechaActual);
        }
        
        $query =  " SELECT u FROM Acme\BackendBundle\Entity\User u "
                . " WHERE "
                . " u.credentialsExpireAt < :fechaActual and u.credentialsExpired = false ";
        
        $items = $this->_em->createQuery($query)
            ->setParameter('fechaActual', $fechaActual)
            ->getResult();
        return $items;
    }
    
    public function findExpiredUser($fechaLastLogin)
    {
        if($fechaLastLogin === null || $fechaLastLogin === false || (is_string($fechaLastLogin) && trim($fechaLastLogin) === "")){
            return array();
        }
        
        if(is_string($fechaLastLogin)){
            $fechaLastLogin = \DateTime::createFromFormat('d-m-Y', $fechaLastLogin);
        }
    
        $query =  " SELECT u FROM Acme\BackendBundle\Entity\User u "
                . " WHERE "
                . " ((u.lastLogin < :fechaLastLogin) or (u.lastLogin is null and u.dateCreate < :fechaLastLogin)) and u.expired = false";
        
        $items = $this->_em->createQuery($query)
                            ->setParameter('fechaLastLogin', $fechaLastLogin->format('d-m-Y H:i:s'))
                            ->getResult();
        return $items;
    }
    
    
    public function findUserAllAdministrativos()
    {        
        $query =  " SELECT u FROM Acme\BackendBundle\Entity\User u "
                . " WHERE "
                . " u.enabled=1 AND u.locked=0 AND u.expired=0 AND u.credentialsExpired=0 "
                . " AND u.roles LIKE '%ROLE_ADMINISTRATIVOS%' "
                ;
        
        $items = $this->_em->createQuery($query)->getResult();
        return $items;
    }
    
    public function findSuperAdmin()
    {
        $query =  " SELECT u FROM Acme\BackendBundle\Entity\User u "
                . " WHERE "
                . " u.enabled=1 AND u.locked=0 AND u.expired=0 AND u.credentialsExpired=0 "
                . " AND u.roles LIKE '%ROLE_SUPER_ADMIN%' "
                ;
        
        $items = $this->_em->createQuery($query)->getResult();
        return $items;
    }
    
    public function findEmailSuperAdmin()
    {
        $query =  " SELECT DISTINCT u.email FROM Acme\BackendBundle\Entity\User u "
                . " WHERE "
                . " u.enabled=1 AND u.locked=0 AND u.expired=0 AND u.credentialsExpired=0 "
                . " AND u.roles LIKE '%ROLE_SUPER_ADMIN%' "
                ;
        
        $result = array(); 
        $items = $this->_em->createQuery($query)->getArrayResult();
        foreach($items as $item){
            $result[] = $item["email"];
        }
            
        return $result;
    }
}

?>
