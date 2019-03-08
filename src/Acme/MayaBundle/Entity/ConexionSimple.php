<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* @ORM\Entity(repositoryClass="Acme\MayaBundle\Repository\ConexionSimpleRepository")
* @ORM\Table(name="conexion_simple")
* @ORM\HasLifecycleCallbacks
*/
class ConexionSimple extends Conexion{
    
    /**
    * @Assert\NotNull(message = "El estado no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="EstadoConexion")
    * @ORM\JoinColumn(name="estado_id", referencedColumnName="id", nullable=true)        
    */
    protected $estado;
    
    /**
    * @ORM\Column(name="id_externo", type="bigint", unique=true, nullable=false)
    */
    protected $idExterno;
    
    /**
    * @Assert\NotNull(message = "El itinerario de la salida no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="ItinerarioInterno")
    * @ORM\JoinColumn(name="itinerario_id", referencedColumnName="id", nullable=false)
    */
    protected $itinerario; //Itinerario que dio lugar a la salida
    
     /**
    * @ORM\ManyToOne(targetEntity="TipoBus")
    * @ORM\JoinColumn(name="tipoBus_id", referencedColumnName="id")
    */
    protected $tipoBus;
    
    /**
    * @ORM\Column(name="cant_vendidos", type="integer", nullable = true)
    */
    protected $cantVendidos;
    
    public function __toString() {
        return strval($this->id);
    }
    
    function __construct() {
        $this->fechaViaje = new \DateTime();
        $this->cantVendidos = 0;
    }

    public function getItinerario() {
        return $this->itinerario;
    }

    public function setItinerario($itinerario) {
        $this->itinerario = $itinerario;
    }

    public function getTipoBus() {
        return $this->tipoBus;
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

    public function getEstado() {
        return $this->estado;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }
    public function getCantVendidos() {
        return $this->cantVendidos;
    }

    public function setCantVendidos($cantVendidos) {
        $this->cantVendidos = $cantVendidos;
    }


}

?>