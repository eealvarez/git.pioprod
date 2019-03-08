<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* @ORM\Entity
* @ORM\Table(name="conexion")
* @ORM\InheritanceType("JOINED")
* @ORM\DiscriminatorColumn(name="tipo_conexion", type="integer")
* @ORM\DiscriminatorMap({1 = "ConexionSimple", 2 = "ConexionCompuesta"})
* @ORM\HasLifecycleCallbacks
*/
class Conexion{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
   /**
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(type="datetime")
    */
    protected $fechaViaje; 
    
    function __construct() {
        $this->fechaViaje = new \DateTime();
    }
    
    public function __toString() {
        return strval($this->id);
    }

    public function getNombreTipoConexion() {
        if($this instanceof ConexionSimple){
            return "Conexión Simple";
        }else if($this instanceof ConexionCompuesta){
            return "Conexión Compuesta";
        }else{
            return "N/D";
        }
    }

    public function getId() {
        return $this->id;
    }

    public function getFechaViaje() {
        return $this->fechaViaje;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setFechaViaje($fechaViaje) {
        $this->fechaViaje = $fechaViaje;
    }
}

?>