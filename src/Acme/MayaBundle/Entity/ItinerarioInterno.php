<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
* @ORM\Table(name="itinerario_interno")
* @ORM\InheritanceType("JOINED")
* @ORM\DiscriminatorColumn(name="tipoItinerario", type="integer")
* @ORM\DiscriminatorMap({1 = "ItinerarioSimple", 2 = "ItinerarioEspecial"})
* @ORM\HasLifecycleCallbacks
*/
class ItinerarioInterno{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @ORM\Column(name="id_externo", type="bigint", unique=true, nullable = false)
    */
    protected $idExterno;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $activo;
    
    /**
    * @Assert\NotNull(message = "La ruta del itinerario no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Ruta")
    * @ORM\JoinColumn(name="ruta_codigo", referencedColumnName="codigo")
    */
    protected $ruta;
    
    /**
    * @Assert\NotNull(message = "El tipo de bus del itinerario no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="TipoBus")
    * @ORM\JoinColumn(name="tipo_bus_id", referencedColumnName="id")        
    */
    protected $tipoBus;
    
    public function __toString() {
        $str  = "ID: " . strval($this->id);
        $str .= "|RUTA: " . strval($this->ruta->getCodigoName());
        $str .= "|TIPOBUS: " . strval($this->tipoBus->getId());
        $str .= "|EXTERNO: " . strval($this->idExterno);
        return $str;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }
    public function getRuta() {
        return $this->ruta;
    }

    public function getTipoBus() {
        return $this->tipoBus;
    }

    public function setRuta($ruta) {
        $this->ruta = $ruta;
    }

    public function setTipoBus($tipoBus) {
        $this->tipoBus = $tipoBus;
    }

    public function getIdExterno() {
        return $this->idExterno;
    }

    public function setIdExterno($idExterno) {
        $this->idExterno = $idExterno;
    }


}

?>