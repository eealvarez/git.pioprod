<?php
namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
* @ORM\Entity
* @ORM\Table(name="departamento")
* @ORM\HasLifecycleCallbacks
*/
class Departamento{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="smallint")
    * @ORM\GeneratedValue(strategy="NONE")
    */
    protected $id;
    
    /**  
    * @ORM\Column(type="string", length=50, unique=true)
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