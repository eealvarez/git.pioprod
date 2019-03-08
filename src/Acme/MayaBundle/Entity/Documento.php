<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
* @ORM\Table(name="documento")
* @ORM\HasLifecycleCallbacks
*/
class Documento{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**   
    * @ORM\Column(type="string", length=15, unique=true, nullable=true)
    */
    protected $tipo;
    
    function __construct() {
        
    }
    public function __toString() {
        return $this->tipo;
    }

    public function getId() {
        return $this->id;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }
}

?>