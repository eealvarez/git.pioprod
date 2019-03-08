<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
* @ORM\Table(name="titulo")
* @ORM\HasLifecycleCallbacks
*/
class Titulo{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**   
    * @ORM\Column(type="string", length=5, unique=true, nullable=true)
    */
    protected $nombre;
    function __construct() {
        
    }
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