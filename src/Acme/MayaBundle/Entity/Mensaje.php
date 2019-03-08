<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* @ORM\Entity(repositoryClass="Acme\MayaBundle\Repository\MensajeRepository")
* @ORM\Table(name="mensaje")
* @ORM\HasLifecycleCallbacks
*/
class Mensaje {
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotBlank(message = "El correo no debe estar en blanco")
    * @Assert\Email(
    *     message = "El correo '{{ value }}' no es válido.",
    *     checkMX = true,
    *     checkHost = true
    * )
    * @ORM\Column(name="correo", type="string", length=100, nullable=true)
    */
    protected $correo;
    
    /**
    * @Assert\NotBlank(message = "El nombre no debe estar en blanco")
    * @Assert\Length(
    *      max = "100",
    *      maxMessage = "El nombre no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(name="nombre", type="string", length=200, nullable=true)
    */
    protected $nombre;
    
    /**
    * @Assert\NotBlank(message = "El mensaje no debe estar en blanco")
    * @Assert\Length(      
    *      max = "2024",
    *      maxMessage = "El mensaje no puede tener más de {{ limit }} caracteres de largo"
    * )
    * @ORM\Column(name="mensaje", type="string", length=2024, nullable=true)
    */
    protected $mensaje;
    
    /**
    * @ORM\Column(name="enviado", type="boolean")
    */
    protected $enviado;
    
    /**
    * @ORM\Column(name="fechaCreacion", type="datetime")
    */
    protected $fechaCreacion;
    
    function __construct() {
        $this->enviado = false;
        $this->fechaCreacion = new \DateTime();
    }
    
    public function getId() {
        return $this->id;
    }

    public function getCorreo() {
        return $this->correo;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getMensaje() {
        return $this->mensaje;
    }

    public function getEnviado() {
        return $this->enviado;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setCorreo($correo) {
        $this->correo = $correo;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setMensaje($mensaje) {
        $this->mensaje = $mensaje;
    }

    public function setEnviado($enviado) {
        $this->enviado = $enviado;
    }
    
    public function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;
    }
}
