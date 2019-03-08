<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* @ORM\Entity(repositoryClass="Acme\MayaBundle\Repository\ItinerarioItemRepository")
* @ORM\Table(name="itinerario_compuesto_item")
* @ORM\HasLifecycleCallbacks
*/
class ItinerarioItem{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotBlank(message = "La estación no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="baja_en_id", referencedColumnName="id")   
    */
    protected $bajaEn;
    
     /**
    * @ORM\Column(type="integer")
    */
    protected $orden;
    
    /**
    * @ORM\ManyToOne(targetEntity="ItinerarioCompuesto", inversedBy="listaItinerarioItem")
    * @ORM\JoinColumn(name="itinerario_compuesto_id", referencedColumnName="id")
    */
    protected $itinerarioCompuesto;
    
    /**
    * @ORM\ManyToMany(targetEntity="ItinerarioSimple")
    * @ORM\JoinTable(name="itinerario_compuesto_item_itinerario_simple",
    *   joinColumns={@ORM\JoinColumn(name="item_id", referencedColumnName="id")},
    *   inverseJoinColumns={@ORM\JoinColumn(name="itinerario_simple_id", referencedColumnName="id")}
    * )
    */
    protected $listaItinerarioSimple;    
    
    function __construct() {
        $this->listaItinerarioSimple = new \Doctrine\Common\Collections\ArrayCollection();
    }
   
    public function getListaIdItinerariosSimple() {
        $ids = array();
        foreach ($this->listaItinerarioSimple as $item) {
            $ids[] = $item->getId();
        }
        return $ids;
    }
    
    public function getDiaHorario() {
        $result = "";
        foreach ($this->listaItinerarioSimple as $item) {
            $result = "|ds" . $item->getDiaSemana()->getId() . "," . $item->getHorarioCiclico()->getId() . "|";
            break;
        }
        return $result;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getBajaEn() {
        return $this->bajaEn;
    }

    public function getOrden() {
        return $this->orden;
    }

    public function getItinerarioCompuesto() {
        return $this->itinerarioCompuesto;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setBajaEn($bajaEn) {
        $this->bajaEn = $bajaEn;
    }

    public function setOrden($orden) {
        $this->orden = $orden;
    }

    public function setItinerarioCompuesto($itinerarioCompuesto) {
        $this->itinerarioCompuesto = $itinerarioCompuesto;
    }
    
    public function getListaItinerarioSimple() {
        return $this->listaItinerarioSimple;
    }

    public function setListaItinerarioSimple($listaItinerarioSimple) {
        $this->listaItinerarioSimple = $listaItinerarioSimple;
    }
}

?>