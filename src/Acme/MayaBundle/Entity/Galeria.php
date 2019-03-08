<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Acme\MayaBundle\Entity\Imagen;

/**
* @ORM\Entity(repositoryClass="Acme\MayaBundle\Repository\GaleriaRepository")
* @ORM\Table(name="galeria")
* @ORM\HasLifecycleCallbacks
*/
class Galeria{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotBlank(message = "El orden no debe estar en blanco")
    * @Assert\Regex(
    *     pattern="/^\d*$/",
    *     match=true,
    *     message="El orden solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "1000",
    *      minMessage = "El orden no debe ser menor de 0.",
    *      maxMessage = "El orden no debe ser mayor que el 1000.",
    *      invalidMessage = "El orden debe ser un número válido."
    * )   
    * @ORM\Column(type="integer")
    */
    protected $orden;
    
    /**
    * @Assert\NotBlank(message = "El nombre no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "100",
    *      minMessage = "El nombre por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El nombre no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=100, nullable=false)
    */
    protected $nombre;
    
    /**
    * @ORM\Column(type="string", length=50, nullable=true)
    */
    protected $referencia;
    
    /**
    * @Assert\Length(      
    *      max = "2048",
    *      maxMessage = "La descripción no puede tener más de {{ limit }} caracteres de largo"
    * )
    * @ORM\Column(type="string", length=2048, nullable=true)
    */
    protected $descripcion;
    
    /**
    * @ORM\OneToMany(targetEntity="Imagen", mappedBy="galeria", cascade={"persist", "remove"}, orphanRemoval=true)
    */
    protected $imagenes;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $activo;
    
    public function __toString() {
        return strval($this->id) . " - " . $this->nombre;
    }
    
    function __construct() {
        $this->activo = true;
        $this->imagenes = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function addItems(Imagen $item) {  
        $item->setGaleria($this);
        $this->imagenes->add($item); 
        return $this;
    }
    
    public function removeItems(Imagen $item) {       
        $this->imagenes->removeElement($item); 
        $item->setGaleria(null);
    }
    
    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getImagenes() {
        return $this->imagenes;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function setImagenes($imagenes) {
        $this->imagenes = $imagenes;
    }
    
    public function getOrden() {
        return $this->orden;
    }

    public function setOrden($orden) {
        $this->orden = $orden;
    }
    
    public function getActivo() {
        return $this->activo;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }
    
    public function getReferencia() {
        return $this->referencia;
    }

    public function setReferencia($referencia) {
        $this->referencia = $referencia;
    }
}

?>