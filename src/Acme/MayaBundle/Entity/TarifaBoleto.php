<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\ExecutionContext;


/**
* @ORM\Entity(repositoryClass="Acme\MayaBundle\Repository\TarifaBoletoRepository")
* @ORM\Table(name="tarifas_boleto")
* @ORM\HasLifecycleCallbacks
* @Assert\Callback(methods={"validacionesGenerales"})
* @DoctrineAssert\UniqueEntity(fields = {"estacionOrigen" , "estacionDestino", "claseBus", "claseAsiento" , "fechaEfectividad"}, 
* message="Ya existe una tarifa para la combinación especficada de estación de origen, de destino, la clase de bus, la clase del asiento y la fecha de efectividad.")
*/
class TarifaBoleto{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotNull(message = "La estación de origen no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_origen_id", referencedColumnName="id", nullable=false)   
    */
    protected $estacionOrigen;
    
    /**
    * @Assert\NotNull(message = "La estación de destino no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_destino_id", referencedColumnName="id", nullable=false)   
    */
    protected $estacionDestino;
    
    /**
    * @Assert\NotNull(message = "La clase del bus no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="ClaseBus")
    * @ORM\JoinColumn(name="clase_bus_id", referencedColumnName="id", nullable=false)   
    */
    protected $claseBus;
    
    /**
    * @Assert\NotNull(message = "La clase del asiento no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="ClaseAsiento")
    * @ORM\JoinColumn(name="clase_asiento_id", referencedColumnName="id", nullable=false)   
    */
    protected $claseAsiento;
    
    
    /**
    * @Assert\NotBlank(message = "La fecha de efectividad no debe estar en blanco")
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(type="datetime", nullable=false)
    */
    protected $fechaEfectividad;
    
    /**
    * @Assert\NotBlank(message = "El valor no debe estar en blanco")
    * @Assert\Regex(
    *     pattern="/((^\d{0,5}$)|(^\d{1,5}[\.|,]\d{1,2}$))/",
    *     match=true,
    *     message="El precio solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "99999.99",
    *      minMessage = "El valor no debe ser menor que {{ limit }}.",
    *      maxMessage = "El valor no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El valor debe ser un número válido."
    * )   
    * @ORM\Column(type="decimal", precision=7, scale=2, nullable=false)
    */
    protected $tarifaValor;
    
    
    // ----------------- DATOS INTERNOS -------------------------
    
    function __construct() {
        $this->fechaEfectividad = new \DateTime();
       
    }
    
    public function __toString() {
        $str  = "Tarifa Boleto: " . $this->id;
        $str .= ", Valor: " . $this->tarifaValor;
        return $str;
    }
    
    public function getInfo1() {
        $str  = "ID: " . $this->id;
        $str .= ", Valor: " . $this->tarifaValor;
        return $str;
    }
    
     /*
     * VALIDACION QUE LA ESTACION DE ORIGEN NO SEA IGUAL QUE LA DE DESTINO.
     */
    public function validacionesGenerales(ExecutionContext $context)
    {
       
        if($this->estacionOrigen === $this->estacionDestino){
             $context->addViolation("La estación de origen no puede ser igual a la estación destino.");   
        } 
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


    public function getClaseBus() {
        return $this->claseBus;
    }

    public function getClaseAsiento() {
        return $this->claseAsiento;
    }

    
    public function getFechaEfectividad() {
        return $this->fechaEfectividad;
    }

    public function getTarifaValor() {
        return $this->tarifaValor;
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

    public function setClaseBus($claseBus) {
        $this->claseBus = $claseBus;
    }

    public function setClaseAsiento($claseAsiento) {
        $this->claseAsiento = $claseAsiento;
    }

    public function setFechaEfectividad($fechaEfectividad) {
        $this->fechaEfectividad = $fechaEfectividad;
    }

    public function setTarifaValor($tarifaValor) {
        $this->tarifaValor = $tarifaValor;
    }

    
    
}

?>