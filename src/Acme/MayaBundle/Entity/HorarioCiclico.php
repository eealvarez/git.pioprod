<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
* @ORM\Table(name="horario_ciclico")
* @ORM\HasLifecycleCallbacks
*/
class HorarioCiclico{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="NONE")
    */
    protected $id;

    /**
    * @Assert\Time(message = "Hora no valida")
    * @ORM\Column(type="time", unique=true)
    */
    protected $hora;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $activo;

    public function __toString() {
        if($this->hora !== null){
            return date_format($this->hora, "H:i");
        }
    }
    
    function __construct() {
        $this->activo = true;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getHora() {
        return $this->hora;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setHora($hora) {
        $this->hora = $hora;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }


}

?>