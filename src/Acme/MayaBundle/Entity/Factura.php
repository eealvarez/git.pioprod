<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
* @ORM\Entity(repositoryClass="Acme\MayaBundle\Repository\FacturaRepository")
* @ORM\Table(name="factura", uniqueConstraints={@ORM\UniqueConstraint(name="CUSTOM_IDX_FACTURA_CONSECUTIVO", columns={"serie", "consecutivo"})})
* @DoctrineAssert\UniqueEntity(fields = {"serie" , "correlativo"}, message="Ya existe ese valor de serie y correlativo de factura en el sistema.")
* @ORM\HasLifecycleCallbacks
*/
class Factura{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotBlank(message = "La serie de la factura no debe estar en blanco")
    * @Assert\Regex(
    *     pattern="/^[A-Z0-9]{1,6}$/",
    *     match=true,
    *     message="La serie de la factura solo puede contener letras mayúsculas y números."
    * )
    * @Assert\Length(
    *      min = "1",
    *      max = "6",
    *      minMessage = "La serie de la factura debe tener por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "La serie de la factura no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(name="serie", type="string", length=6, nullable=false)
    */
    protected $serie;
    
    /**
    * @Assert\NotBlank(message = "El correlativo de la factura no debe estar en blanco")
    * @ORM\Column(name="consecutivo", type="bigint", nullable=false)
    */
    protected $correlativo;

    /**
    * @ORM\OneToOne(targetEntity="Compra", mappedBy="factura")
    */
    protected $compra;
    
    /**
    * @ORM\ManyToOne(targetEntity="EstadoFactura")
    * @ORM\JoinColumn(name="estado_id", referencedColumnName="id")        
    */
    protected $estado;
    
    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    protected $observaciones;
    
    /**
    * @ORM\Column(name="notificada", type="boolean", nullable=true)
    */
    protected $notificada;
    
    function __construct() {
        $this->notificada = false;
    }
    
    public function __toString() {
        return strval($this->id);
    }
    
    public function getInfo1() {
        return $this->serie . " " . $this->correlativo;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getSerie() {
        return $this->serie;
    }

    public function getCorrelativo() {
        return $this->correlativo;
    }

    public function getCompra() {
        return $this->compra;
    }

    public function getObservaciones() {
        return $this->observaciones;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setSerie($serie) {
        $this->serie = $serie;
    }

    public function setCorrelativo($correlativo) {
        $this->correlativo = $correlativo;
    }

    public function setCompra($compra) {
        $this->compra = $compra;
    }

    public function setObservaciones($observaciones) {
        $this->observaciones = $observaciones;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }    
    
    public function getNotificada() {
        return $this->notificada;
    }

    public function setNotificada($notificada) {
        $this->notificada = $notificada;
    }
}

?>