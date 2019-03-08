<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
* @ORM\Table(name="itinerario_especial")
* @ORM\HasLifecycleCallbacks
*/
class ItinerarioEspecial extends ItinerarioInterno{
    
    /**
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(type="datetime")
    */
    protected $fecha;
    
    /**
    * @Assert\NotBlank(message = "La estación no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_id", referencedColumnName="id")   
    */
    protected $estacion;
    
    public function __toString() {
        $str  = "ID: " . strval($this->id);
        $str .= "|TIPO:ESP";
        $str .= "|ESTACION: " . $this->estacion->getAliasNombre();
        $str .= "|RUTA: " . strval($this->ruta->getCodigo());
        $str .= "|TIPOBUS: " . strval($this->tipoBus->getId());
        $str .= "|EXTERNO: " . strval($this->idExterno);
        return $str;
    }
    
    function __construct() {
        $this->activo = true;
        $this->fecha = new \DateTime();
    }
    
    public function getFecha() {
        return $this->fecha;
    }

    public function getEstacion() {
        return $this->estacion;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function setEstacion($estacion) {
        $this->estacion = $estacion;
    }




}

?>