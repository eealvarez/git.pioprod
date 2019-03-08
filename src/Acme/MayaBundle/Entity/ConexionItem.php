<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Acme\MayaBundle\Validator as CustomAssert;

/**
 * @ORM\Entity()
* @ORM\Table(name="conexion_item")
* @ORM\HasLifecycleCallbacks
*/
class ConexionItem{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    
     /**
    * @ORM\Column(type="integer")
    */
    protected $orden;
    
    /**
    * @ORM\ManyToOne(targetEntity="ConexionCompuesta", inversedBy="listaConexionItem")
    * @ORM\JoinColumn(name="conexionCompuesta_id", referencedColumnName="id")
    */
    protected $conexionCompuesta;

    /**
    * @ORM\ManyToOne(targetEntity="ConexionSimple")
    * @ORM\JoinColumn(name="conexionSimple_id", referencedColumnName="id")
    */
    protected $conexionSimple;
    
    /**
    * @ORM\ManyToOne(targetEntity="ItinerarioItem")
    * @ORM\JoinColumn(name="itinerarioItem_id", referencedColumnName="id")
    */
    protected $itinerarioItem;
    
    
    function __construct() {
        
    }
    
    public function __toString() {
        $str  = "ID: " . strval($this->id);
        $str .= "|ORDEN: " . strval($this->orden);
        $str .= "|ID_CONEXION_SIMPLE: " . strval($this->getConexionSimple()->getId());
        return $str;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getOrden() {
        return $this->orden;
    }

    public function getConexionCompuesta() {
        return $this->conexionCompuesta;
    }

    public function getConexionSimple() {
        return $this->conexionSimple;
    }

    public function getItinerarioItem() {
        return $this->itinerarioItem;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setOrden($orden) {
        $this->orden = $orden;
    }

    public function setConexionCompuesta($conexionCompuesta) {
        $this->conexionCompuesta = $conexionCompuesta;
    }

    public function setConexionSimple($conexionSimple) {
        $this->conexionSimple = $conexionSimple;
    }

    public function setItinerarioItem($itinerarioItem) {
        $this->itinerarioItem = $itinerarioItem;
    }




}

?>