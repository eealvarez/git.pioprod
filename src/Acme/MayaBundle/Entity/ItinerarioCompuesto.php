<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
* @ORM\Entity(repositoryClass="Acme\MayaBundle\Repository\ItinerarioCompuestoRepository")
* @ORM\Table(name="itinerario_compuesto")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields = {"estacionOrigen" , "estacionDestino", "diasHorariosStr" }, 
* message="Ya existe un itinerario compuesto para la combinación especficada de estación de origen, de destino, dias de la semana y horarios.")
*/
class ItinerarioCompuesto {
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
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
    
    /**
    * @Assert\NotBlank(message = "La estación origen no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_origen_id", referencedColumnName="id")   
    */
    protected $estacionOrigen;
    
    /**
    * @Assert\NotBlank(message = "La estación detino no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_destino_id", referencedColumnName="id")   
    */
    protected $estacionDestino;
    
    /**
    * @ORM\OneToMany(targetEntity="ItinerarioItem", mappedBy="itinerarioCompuesto", 
    * cascade={"persist", "remove"}, orphanRemoval=true)
    */
    protected $listaItinerarioItem;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $activo;
    
    /**
    * @ORM\Column(name="dias_horarios_str", type="string", length=100, nullable=true )
    */
    protected $diasHorariosStr;
    
    function __construct() {
        $this->listaItinerarioItem = new \Doctrine\Common\Collections\ArrayCollection();
        $this->activo = true;
    }
    
    public function __toString() {
        $str  = "ID: " . strval($this->id);
        $str .= "|TIPO:COMP";
        if($this->diaSemana !== null && $this->horarioCiclico !== null)
            $str .= "|HORARIO: " . $this->diaSemana->getNombre() . "[" . $this->horarioCiclico->getHora()->format("h:i A") . "]";
        if($this->estacionOrigen !== null)
            $str .= "|ORIGEN: " . strval($this->estacionOrigen->getAliasNombre());
        if($this->estacionDestino !== null)
            $str .= "|DESTINO: " . strval($this->estacionDestino->getAliasNombre());
        return $str;
    }
    
    public function addListaItinerarioItem(ItinerarioItem $item) {  
       $item->setItinerarioCompuesto($this);
       $this->getListaItinerarioItem()->add($item);
       return $this;
    }
    
    public function calculateDiasHorariosStr() {
        $result = "";
        foreach ($this->listaItinerarioItem as $item) {
            $result .= $item->getDiaHorario();
        }
        $this->diasHorariosStr = $result;
        return $result;
    }
    
    public function getListaItinerarioItemOrder(){
        $items = $this->getListaItinerarioItem()->toArray();
//        var_dump($items);
        usort($items, function($a, $b){
            return intval($a->getOrden()) === intval($b->getOrden()) ? 0 : ( intval($a->getOrden()) > intval($b->getOrden()) ) ? 1 : -1;
        });
        return $items;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getEstacionOrigen() {
        return $this->estacionOrigen;
    }

    public function getEstacionDestino() {
        return $this->estacionDestino;
    }

    public function getListaItinerarioItem() {
        return $this->listaItinerarioItem;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setEstacionOrigen($estacionOrigen) {
        $this->estacionOrigen = $estacionOrigen;
    }

    public function setEstacionDestino($estacionDestino) {
        $this->estacionDestino = $estacionDestino;
    }

    public function setListaItinerarioItem($listaItinerarioItem) {
        $this->listaItinerarioItem = $listaItinerarioItem;
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
    
    public function getActivo() {
        return $this->activo;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }
    
    public function getDiasHorariosStr() {
        return $this->diasHorariosStr;
    }

    public function setDiasHorariosStr($diasHorariosStr) {
        $this->diasHorariosStr = $diasHorariosStr;
    }


    
}

?>