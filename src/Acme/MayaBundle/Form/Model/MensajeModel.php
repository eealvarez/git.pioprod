<?php
namespace Acme\MayaBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class MensajeModel {
    
    /**
     * @Assert\NotBlank(message = "El nombre no debe estar en blanco")
     * @var string
     */
    protected $nombre;
    
    /**
     * @Assert\NotBlank(message = "El correo no debe estar en blanco")
     * @Assert\Email
     * @var string
     */
    protected $correo;
    
    /**
     * @Assert\NotBlank(message = "El mensaje no debe estar en blanco")
     * @var string
     */
    protected $mensaje;

    public function __construct() { 
        
        
    }
   
    public function getNombre() {
        return $this->nombre;
    }

    public function getCorreo() {
        return $this->correo;
    }

    public function getMensaje() {
        return $this->mensaje;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setCorreo($correo) {
        $this->correo = $correo;
    }

    public function setMensaje($mensaje) {
        $this->mensaje = $mensaje;
    }
}