<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity
* @ORM\Table(name="factura_estado")
* @ORM\HasLifecycleCallbacks
*/
class EstadoFactura{
    
    const CREADA = '1';
    const ENVIADA = '2';
    const RECIBIDA = '3';
    const ENTREGADA = '4';
    
    /**
    * @ORM\Id
    * @ORM\Column(type="smallint")
    */
    protected $id;
    
    /**
    * @ORM\Column(type="string", length=20, unique=true)
    */
    protected $nombre;
    
    public function __toString() {
        return $this->nombre;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }
}

?>