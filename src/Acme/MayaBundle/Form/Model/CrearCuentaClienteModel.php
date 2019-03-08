<?php
namespace Acme\MayaBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class CrearCuentaClienteModel {
    
   /**
    * @Assert\Email(message = "Fecha de salida no valida")
    */
    protected $cliente;
    
    /**
    
    */
    protected $enviarInfoSMS;
    
     /**
    
    */
    protected $enviarInfoTelefono;
    
    /**
    */
    protected $terminos;
    
    /**
    */
    protected $aceptaTerminos;
    
   
   public function __construct() { 
        
        
    }
   
    public function getCliente() {
        return $this->cliente;
    }

    public function getEnviarInfoSMS() {
        return $this->enviarInfoSMS;
    }

    public function getEnviarInfoTelefono() {
        return $this->enviarInfoTelefono;
    }

    public function getTerminos() {
        return $this->terminos;
    }

    public function getAceptaTerminos() {
        return $this->aceptaTerminos;
    }

    public function setCliente($cliente) {
        $this->cliente = $cliente;
    }

    public function setEnviarInfoSMS($enviarInfoSMS) {
        $this->enviarInfoSMS = $enviarInfoSMS;
    }

    public function setEnviarInfoTelefono($enviarInfoTelefono) {
        $this->enviarInfoTelefono = $enviarInfoTelefono;
    }

    public function setTerminos($terminos) {
        $this->terminos = $terminos;
    }

    public function setAceptaTerminos($aceptaTerminos) {
        $this->aceptaTerminos = $aceptaTerminos;
    }





   
}