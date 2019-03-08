<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity()
* @ORM\Table(name="estacion_tipo")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields ="nombre", message="El nombre ya existe")
*/
class TipoEstacion{
    
    const ESTACION = 1;
    const ESTACION_INTERMEDIA = 2;
    const RECORRIDO = 3;
    const AGENCIA = 4;
    
     /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotBlank(message = "El tipo de estación no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "50",
    *      minMessage = "El tipo de estación por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El tipo de estación no puede tener más de {{ limit }} caracteres."
    * )    
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