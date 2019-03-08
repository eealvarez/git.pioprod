<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\ExecutionContext;
use Acme\MayaBundle\Validator as CustomAssert;
use Acme\MayaBundle\Entity\Estacion;

/**
* @ORM\Entity
* @ORM\Table(name="ruta")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields ="codigo", message="El código ya existe")
* @DoctrineAssert\UniqueEntity(fields ="nombre", message="El nombre ya existe")
* @Assert\Callback(methods={"validacionesGenerales"})
*/
class Ruta{
    
    
    /**
    * @Assert\NotBlank(message = "El código no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "6",
    *      minMessage = "El código por lo menos debe tener {{ limit }} carácter.",
    *      maxMessage = "El código no puede tener más de {{ limit }} caracteres."
    * )
    * @Assert\Regex(
    *     pattern="/\d/",
    *     match=true,
    *     message="El código solo puede contener números"
    * )
    * @ORM\Id
    * @ORM\Column(type="string", length=6, unique=true)
    * @ORM\GeneratedValue(strategy="NONE")
    */
    protected $codigo;
    
    
    
    /**
    * @Assert\NotBlank(message = "El nombre no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "255",
    *      minMessage = "El nombre por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El nombre no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=255)
    */
    protected $nombre;
    
    /**
    * @Assert\NotNull(message = "La estación de origen no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_origen_id", referencedColumnName="id")        
    */
    protected $estacionOrigen;
    
    /**
    * @Assert\NotNull(message = "La estación destino no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_destino_id", referencedColumnName="id")        
    */
    protected $estacionDestino;
    
    /**
    * @ORM\OneToMany(targetEntity="RutaEstacionItem", mappedBy="ruta", cascade={"persist", "remove"}, orphanRemoval=true)
    */
    protected $listaEstacionesIntermediaOrdenadas;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $activo;

     /**
    * @ORM\Column(type="boolean", nullable=true)
    */
    protected $obligatorioClienteDetalle;
    /*
     * VALIDACION DE QUE LA ESTACION DE ORIGEN Y DESTINO NO ESTE EN LA LISTA DE ESTACIONES INTERMEDIAS.
     */
    public function validacionesGenerales(ExecutionContext $context)
    {
          if($this->estacionOrigen === $this->estacionDestino){
               $context->addViolation("La estación de origen no puede ser igual a la estación destino.");   
          }
          
          $estacionesIntermediaArray = $this->listaEstacionesIntermediaOrdenadas->toArray();
          if(in_array($this->estacionOrigen, $estacionesIntermediaArray)){
               $context->addViolation("La estación de origen no se puede seleccionar como una estación intermedia.");   
          }
          
          if(in_array($this->estacionDestino, $estacionesIntermediaArray)){
               $context->addViolation("La estación de destino no se puede seleccionar como una estación intermedia.");   
          }
    }
    
    public function __toString() {
        return $this->getCodigoName();
    }
    
    public function getCodigoName() {
        return strval($this->codigo) . " - " . $this->nombre;
    }
    
    function __construct() {
        $this->activo = true;
        $this->listaEstacionesIntermediaOrdenadas = new \Doctrine\Common\Collections\ArrayCollection();
        $this->obligatorioClienteDetalle = false;
    }
           
    public function getCodigo() {
        return $this->codigo;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getEstacionOrigen() {
        return $this->estacionOrigen;
    }

    public function getEstacionDestino() {
        return $this->estacionDestino;
    }

    public function getListaEstacionesIntermediaOrdenadas() {
        return $this->listaEstacionesIntermediaOrdenadas;
    }

    public function setListaEstacionesIntermediaOrdenadas($listaEstacionesIntermediaOrdenadas) {
        $this->listaEstacionesIntermediaOrdenadas = $listaEstacionesIntermediaOrdenadas;
    }

        public function getActivo() {
        return $this->activo;
    }

    public function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }
    
    public function setEstacionOrigen($estacionOrigen) {
        $this->estacionOrigen = $estacionOrigen;
    }

    public function setEstacionDestino($estacionDestino) {
        $this->estacionDestino = $estacionDestino;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }
   
    public function addListaEstacionesIntermediaOrdenadas(RutaEstacionItem $item) {  
       $item->setRuta($this);
       $this->getListaEstacionesIntermediaOrdenadas()->add($item);
       return $this;
    }
    
    public function removeListaEstacionesIntermediaOrdenadas(RutaEstacionItem $item) {       
        $this->getListaEstacionesIntermediaOrdenadas()->removeElement($item); 
        $item->setRuta(null);
    }
    public function getObligatorioClienteDetalle() {
        return $this->obligatorioClienteDetalle;
    }

    public function setObligatorioClienteDetalle($obligatorioClienteDetalle) {
        if($obligatorioClienteDetalle == null)
            $this->obligatorioClienteDetalle = false;
        else
            $this->obligatorioClienteDetalle = $obligatorioClienteDetalle;
    }


}

?>