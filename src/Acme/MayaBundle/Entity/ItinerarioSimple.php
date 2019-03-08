<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Acme\MayaBundle\Repository\ItinerarioSimpleRepository")
* @ORM\Table(name="itinerario_simple")
* @ORM\HasLifecycleCallbacks
*/
class ItinerarioSimple extends ItinerarioInterno{
    
    /**
    * @Assert\NotNull(message = "El día de la semana no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="DiaSemana")
    * @ORM\JoinColumn(name="dia_semana_id", referencedColumnName="id")        
    */
    protected $diaSemana;
    
    /**
    * @Assert\NotNull(message = "El horario ciclico no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="HorarioCiclico")
    * @ORM\JoinColumn(name="horario_ciclico_id", referencedColumnName="id")        
    */
    protected $horarioCiclico;
    
    public function __toString() {
        $str  = "ID: " . strval($this->id);
        $str .= "|TIPO:SIM";
        $str .= "|HORARIO: " . $this->diaSemana->getNombre() . "[" . $this->horarioCiclico->getHora()->format("h:i A") . "]";
        $str .= "|RUTA: " . strval($this->ruta->getCodigo());
        $str .= "|TIPOBUS: " . strval($this->tipoBus->getId());
        $str .= "|EXTERNO: " . strval($this->idExterno);
        return $str;
    }
    
    function __construct() {
        $this->activo = true;
    }
    
    public function getClave1() {
        return $this->getRuta()->getCodigo() . "-" . $this->getDiaSemana()->getId() . "-" . $this->getHorarioCiclico()->getId();
    }
    
    public function getInfo1() {
        $str = "ID:" . strval($this->id);
        if($this->diaSemana !== null && $this->horarioCiclico !== null){
            $str .= "|Horario:" . $this->diaSemana . "[" . $this->horarioCiclico->getHora()->format("h:i A") . "]";
        }
        if($this->getRuta() !== null){
            $str .= "|Ruta:" . $this->getRuta()->getCodigoName();
        }
        if($this->getTipoBus() !== null){
            $str .= "|Clase:" . $this->getTipoBus()->getClase()->getNombre();
        }
        return $str;
    }
    
    public function getDiaSemana() {
        return $this->diaSemana;
    }

    public function getHorarioCiclico() {
        return $this->horarioCiclico;
    }

    public function setDiaSemana($diaSemana) {
        $this->diaSemana = $diaSemana;
    }

    public function setHorarioCiclico($horarioCiclico) {
        $this->horarioCiclico = $horarioCiclico;
    }
}

?>