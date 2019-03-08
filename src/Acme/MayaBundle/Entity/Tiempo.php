<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
* @ORM\Entity(repositoryClass="Acme\MayaBundle\Repository\TiempoRepository")
* @ORM\Table(name="tiempo")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields ={"ruta", "estacionDestino", "claseBus"}, message="Ya existe un tiempo para esa ruta, estación y clase de bus.")
*/
class Tiempo{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotBlank(message = "La ruta no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Ruta")
    * @ORM\JoinColumn(name="ruta_codigo", referencedColumnName="codigo")   
    */
    protected $ruta;
    
    /**
    * @Assert\NotNull(message = "La estación destino no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_destino_id", referencedColumnName="id")        
    */
    protected $estacionDestino;
    
    /**
    * @Assert\NotBlank(message = "La clase no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="ClaseBus")
    * @ORM\JoinColumn(name="clasebus_id", referencedColumnName="id")        
    */
    protected $claseBus;
    
    /**
    * @Assert\NotBlank(message = "Los minutos no debe estar en blanco")
    * @Assert\Regex(
    *     pattern="/^\d*$/",
    *     match=true,
    *     message="El minutos solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "1",
    *      max = "99999",
    *      minMessage = "Los minutos no debe ser menor que {{ limit }}.",
    *      maxMessage = "Los minutos  no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "Los minutos debe ser un número válido."
    * )   
    * @ORM\Column(type="integer")
    */
    protected $minutos;
    
    public function __toString() {
        return $this->ruta . " - " . $this->estacionDestino . " - " . $this->claseBus;
    }
    
    function __construct() {
        
    }
    
    public function getId() {
        return $this->id;
    }

    public function getRuta() {
        return $this->ruta;
    }

    public function getClaseBus() {
        return $this->claseBus;
    }

    public function getMinutos() {
        return $this->minutos;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setRuta($ruta) {
        $this->ruta = $ruta;
    }

    public function setClaseBus($claseBus) {
        $this->claseBus = $claseBus;
    }

    public function setMinutos($minutos) {
        $this->minutos = $minutos;
    }
    
    public function getEstacionDestino() {
        return $this->estacionDestino;
    }

    public function setEstacionDestino($estacionDestino) {
        $this->estacionDestino = $estacionDestino;
    }
}

?>