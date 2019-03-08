<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* @ORM\Entity(repositoryClass="Acme\MayaBundle\Repository\ConexionCompuestaRepository")
* @ORM\Table(name="conexion_compuesta")
* @ORM\HasLifecycleCallbacks
*/
class ConexionCompuesta extends Conexion{
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $activa;
    
    /**
    * @Assert\NotNull(message = "El itinerario de la salida no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="ItinerarioCompuesto")
    * @ORM\JoinColumn(name="itinerario_id", referencedColumnName="id", nullable=false)
    */
    protected $itinerarioCompuesto; 
    
    /**
    * @ORM\OneToMany(targetEntity="ConexionItem", mappedBy="conexionCompuesta", 
    * cascade={"persist", "remove"}, orphanRemoval=true)
    */
    protected $listaConexionItem;
    
    public function __toString() {
        return strval($this->id);
    }
    
    function __construct() {
        $this->listaConexionItem = new \Doctrine\Common\Collections\ArrayCollection();
        $this->fechaViaje = new \DateTime();
        $this->activa = true;
    }
    
    public function addListaConexionItem(ConexionItem $item) {  
       $item->setConexionCompuesta($this);
       $this->getListaConexionItem()->add($item);
       return $this;
    }
    
    public function getItinerarioCompuesto() {
        return $this->itinerarioCompuesto;
    }

    public function getListaConexionItem() {
        return $this->listaConexionItem;
    }

    public function setItinerarioCompuesto($itinerarioCompuesto) {
        $this->itinerarioCompuesto = $itinerarioCompuesto;
    }

    public function setListaConexionItem($listaConexionItem) {
        $this->listaConexionItem = $listaConexionItem;
    }
    
    public function getActiva() {
        return $this->activa;
    }

    public function setActiva($activa) {
        $this->activa = $activa;
    }
}

?>