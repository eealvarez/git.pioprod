<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* @ORM\Entity()
* @ORM\Table(name="bus_senal")
* @ORM\HasLifecycleCallbacks
*/
class SenalBus{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="NONE")
    */
    protected $id;
    
    /**
    * @ORM\ManyToOne(targetEntity="TipoBus", inversedBy="listaSenal")
    * @ORM\JoinColumn(name="tipoBus_id", referencedColumnName="id")
    */
    protected $tipoBus;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $nivel2;
    
    /**
    * @Assert\NotBlank(message = "El Tipo no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="TipoSenal")
    * @ORM\JoinColumn(name="tipo_id", referencedColumnName="id")        
    */
    protected $tipo;
    
    /**
    * @Assert\Regex(
    *     pattern="/^\d*$/",
    *     message="El número solo puede contener números"
    * ) 
    * @ORM\Column(type="integer")
    */
    protected $coordenadaX;
    
    /**
    * @Assert\Regex(
    *     pattern="/^\d*$/",
    *     message="El número solo puede contener números"
    * ) 
    * @ORM\Column(type="integer")
    */
    protected $coordenadaY;
    
    public function __toString() {
        return strval($this->id) . ($this->tipo !== null ? (" - " . $this->tipo->getNombre()) : "");
    }
    
    function __construct() {
        
    }
    
    public function getId() {
        return $this->id;
    }

    public function getTipoBus() {
        return $this->tipoBus;
    }

    public function getNivel2() {
        return $this->nivel2;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function getCoordenadaX() {
        return $this->coordenadaX;
    }

    public function getCoordenadaY() {
        return $this->coordenadaY;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setTipoBus($tipoBus) {
        $this->tipoBus = $tipoBus;
    }

    public function setNivel2($nivel2) {
        $this->nivel2 = $nivel2;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    public function setCoordenadaX($coordenadaX) {
        $this->coordenadaX = $coordenadaX;
    }

    public function setCoordenadaY($coordenadaY) {
        $this->coordenadaY = $coordenadaY;
    }

}

?>