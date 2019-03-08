<?php
namespace Acme\MayaBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class CompraSinCuentaModel {
    
   /**
    * @Assert\Email(message = "Fecha de salida no valida")
    */
    protected $correo;
    
    /**
    * @Assert\NotBlank(message = "El nombre no debe estar en blanco.")     
    */
    protected $nombre;
    
    /**
    */
    protected $telefono;
    
   
   public function __construct() { 
        
        
    }
    public function getCorreo() {
        return $this->correo;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getTelefono() {
        return $this->telefono;
    }

    public function setCorreo($correo) {
        $this->correo = $correo;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setTelefono($telefono) {
        $this->telefono = $telefono;
    }




   
}