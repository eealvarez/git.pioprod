<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* @ORM\Entity(repositoryClass="Acme\MayaBundle\Repository\BoletoRepository")
* @ORM\Table(name="boleto")
* @ORM\HasLifecycleCallbacks
*/
class Boleto {
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @ORM\Column(type="bigint", unique=true, nullable=true)
    */
    protected $idExternoBoleto;
    
    /**
    * @ORM\Column(type="bigint", unique=true, nullable=false)
    */
    protected $idExternoReservacion;
    
    /**
    * @Assert\Regex(
    *     pattern="/((^\d{0,5}$)|(^\d{0,5}[\.|,]\d{1,2}$))/",
    *     match=true,
    *     message="El precio calculado en la moneda base solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "99999.99",
    *      minMessage = "El precio no debe ser menor que {{ limit }}.",
    *      maxMessage = "El precio no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El precio debe ser un número válido."
    * )   
    * @ORM\Column(type="decimal", precision=7, scale=2, nullable=true)
    */
    protected $precio;
    
    /**
    * @ORM\ManyToOne(targetEntity="AsientoBus")
    * @ORM\JoinColumn(name="asiento_bus_id", referencedColumnName="id", nullable=true)        
    */
    protected $asientoBus;
    
    
    /**
    * @ORM\ManyToOne(targetEntity="ConexionSimple")
    * @ORM\JoinColumn(name="conexion_id", referencedColumnName="id", nullable=false)   
    */
    protected $conexion;
    
    /**
    * @ORM\ManyToOne(targetEntity="Paquete", inversedBy="listaBoletos")
    * @ORM\JoinColumn(name="paquete_id", referencedColumnName="id")
    */
    protected $paquete;
    
    /**
    * @ORM\ManyToOne(targetEntity="TarifaBoleto")
    * @ORM\JoinColumn(name="tarifa_id", referencedColumnName="id", nullable=true)   
    */
    protected $tarifa; //Tarifa utilizada para generar el precio calculado //pendiente
    
    /**
    * @Assert\NotNull(message = "El estado no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="EstadoBoleto")
    * @ORM\JoinColumn(name="estado_id", referencedColumnName="id", nullable=true)        
    */
    protected $estado;
    
    /**
    * @Assert\NotNull(message = "La estación donde sube no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="subeEn_id", referencedColumnName="id")        
    */
    protected $subeEn;
    
    /**
    * @Assert\NotNull(message = "La estación donde baja no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="bajaEn_id", referencedColumnName="id")        
    */
    protected $bajaEn;
    
    function __construct() {
        
    }
    
    public function preparedPersist($em){
        if($this->asientoBus !== null){
            $this->asientoBus = $em->merge($this->asientoBus);
        }
        if($this->conexion !== null){
            $this->conexion = $em->merge($this->conexion);
        }
        if($this->tarifa !== null){
            $this->tarifa = $em->merge($this->tarifa);
        }
        if($this->estado !== null){
            $this->estado = $em->merge($this->estado);
        }
        if($this->subeEn !== null){
            $this->subeEn = $em->merge($this->subeEn);
        }
        if($this->bajaEn !== null){
            $this->bajaEn = $em->merge($this->bajaEn);
        }
    }

    public function getId() {
        return $this->id;
    }

    public function getIdExternoBoleto() {
        return $this->idExternoBoleto;
    }

    public function getIdExternoReservacion() {
        return $this->idExternoReservacion;
    }

    public function getAsientoBus() {
        return $this->asientoBus;
    }

    public function getConexion() {
        return $this->conexion;
    }

    public function getPaquete() {
        return $this->paquete;
    }

    public function getTarifa() {
        return $this->tarifa;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setIdExternoBoleto($idExternoBoleto) {
        $this->idExternoBoleto = $idExternoBoleto;
    }

    public function setIdExternoReservacion($idExternoReservacion) {
        $this->idExternoReservacion = $idExternoReservacion;
    }
    
    public function setAsientoBus($asientoBus) {
        $this->asientoBus = $asientoBus;
    }

    public function setConexion($conexion) {
        $this->conexion = $conexion;
    }

    public function setPaquete($paquete) {
        $this->paquete = $paquete;
    }

    public function setTarifa($tarifa) {
        $this->tarifa = $tarifa;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function getPrecio() {
        return $this->precio;
    }

    public function setPrecio($precio) {
        $this->precio = $precio;
    }

    public function getSubeEn() {
        return $this->subeEn;
    }

    public function getBajaEn() {
        return $this->bajaEn;
    }

    public function setSubeEn($subeEn) {
        $this->subeEn = $subeEn;
    }

    public function setBajaEn($bajaEn) {
        $this->bajaEn = $bajaEn;
    }
}
