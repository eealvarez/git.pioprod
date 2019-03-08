<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
* @ORM\Table(name="conexion_estado")
* @ORM\HasLifecycleCallbacks
*/
class EstadoConexion{
    
    const PROGRAMADA = '1';
    const ABORDANDO = '2';
    const INICIADA = '3';
    const CANCELADA = '4'; //Estado final
    const FINALIZADA = '5'; //Estado final
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    */
    protected $id;
    
    /**
    * @ORM\Column(type="string", length=20, unique=true)
    */
    protected $nombre;
        
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $activo;
    
    public function __toString() {
        return $this->nombre;
    }
    
    function __construct() {
        $this->activo = true;
    }

    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }
}

?>