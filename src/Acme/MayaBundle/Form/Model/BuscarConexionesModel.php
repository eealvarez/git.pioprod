<?php
namespace Acme\MayaBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class BuscarConexionesModel {
    
   /**
    * @Assert\Date(message = "Fecha de salida no valida")
    */
    protected $fechaSalida;
    
    /**
    * @Assert\Date(message = "Fecha de regreso no valida")
    */
    protected $fechaRegreso;
    
    /**
    * @Assert\NotBlank(message = "La estación 'Origen' no debe estar en blanco.")     
    */
    protected $estacionOrigen;
    
    /**
    * @Assert\NotBlank(message = "La estación 'Destino' no debe estar en blanco.")     
    */
    protected $estacionDestino;
    
    protected $cantidadPasajeros;
    
    protected $conexionesDirectas;
    
//    /**
//    *
//    @Assert\Choice({"Solo ida", "Ida y regreso"})
//    */
    protected $idaRegreso;
    
    
    public function __construct() { 
        $this->conexionesDirectas = false;
        $this->fechaRegreso = new \DateTime();
        $this->fechaSalida = new \DateTime();
        
    }
    public function getFechaSalida() {
        return $this->fechaSalida;
    }

    public function getEstacionOrigen() {
        return $this->estacionOrigen;
    }

    public function getEstacionDestino() {
        return $this->estacionDestino;
    }


    public function getConexionesDirectas() {
        return $this->conexionesDirectas;
    }

    public function setFechaSalida($fechaSalida) {
        $this->fechaSalida = $fechaSalida;
    }

    public function setEstacionOrigen($estacionOrigen) {
        $this->estacionOrigen = $estacionOrigen;
    }

    public function setEstacionDestino($estacionDestino) {
        $this->estacionDestino = $estacionDestino;
    }


    public function setConexionesDirectas($conexionesDirectas) {
        $this->conexionesDirectas = $conexionesDirectas;
    }

    public function getFechaRegreso() {
        return $this->fechaRegreso;
    }

    public function getIdaRegreso() {
        return $this->idaRegreso;
    }

    public function setFechaRegreso($fechaRegreso) {
        $this->fechaRegreso = $fechaRegreso;
    }

    public function setIdaRegreso($idaRegreso) {
        $this->idaRegreso = $idaRegreso;
    }

    public function getCantidadPasajeros() {
        return $this->cantidadPasajeros;
    }

    public function setCantidadPasajeros($cantidadPasajeros) {
        $this->cantidadPasajeros = $cantidadPasajeros;
    }

    


   
}