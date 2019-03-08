<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="pasajero")
 * @ORM\HasLifecycleCallbacks
 */
class Pasajero {

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Assert\NotBlank(message = "El nombre no debe estar en blanco")
     * @Assert\Length(
     *      min = "1",
     *      max = "100",
     *      minMessage = "El nombre por lo menos debe tener {{ limit }} caracteres.",
     *      maxMessage = "El nombre no puede tener más de {{ limit }} caracteres."
     * )    
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    protected $nombreApellidos;

    /**
     * @ORM\ManyToOne(targetEntity="Nacionalidad")
     * @ORM\JoinColumn(name="nacionalidad_id", referencedColumnName="id")
     */
    protected $nacionalidad;


    /**
     * @ORM\ManyToOne(targetEntity="Documento")
     * @ORM\JoinColumn(name="documento_id", referencedColumnName="id")   
     */
    protected $tipoDocumento;

    /**
     * @Assert\NotBlank(message = "El numero de documento del pasajero no debe estar en blanco")
     * @Assert\Length(
     *      max = "40",
     *      maxMessage = "El número del documento del pasajero no puede tener más de {{ limit }} caracteres."
     * )  
     * @Assert\Regex(
     *     pattern="/(^[a-zA-Z0-9\s]{0,40}$)/",
     *     match=true,
     *     message="El número del documento del pasajero solo puede contener números, letras y espacios. No puede tener guiones."
     * )    
     * @ORM\Column(type="string", length=40, nullable=false)  
     */
    protected $valorDocumento;

    /**
     * @Assert\Date(message = "Fecha no valida")
     * @ORM\Column(name="fecha_vencimiento_documento", type="date", nullable=true)
     */
    protected $fechaVencimientoDocumento;

    /**
     * @Assert\Date(message = "Fecha no valida")
     * @ORM\Column(name="fecha_nacimiento", type="date", nullable=true)
     */
    protected $fechaNacimiento;

    /**
     * @ORM\ManyToOne(targetEntity="Sexo")
     * @ORM\JoinColumn(name="sexo_id", referencedColumnName="id", nullable=true)   
     */
    protected $sexo;
    
     /**
    * @ORM\OneToMany(targetEntity="Paquete", mappedBy="pasajero", cascade={"persist", "remove"})
    */
    protected $listaPaquetes;
    
    /**
     * @ORM\ManyToOne(targetEntity="Compra", inversedBy="listaPasajeros")
     * @ORM\JoinColumn(name="compra_id", referencedColumnName="id")
     */
    protected $compra;
    
    /**
    * @ORM\Column(name="detallado", type="boolean")
    */
    protected $detallado;

    function __construct() {
         $this->listaPaquetes = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function preparedPersist($em){
        $this->nacionalidad = $em->merge($this->nacionalidad);
        $this->tipoDocumento = $em->merge($this->tipoDocumento);
        $this->sexo = $this->sexo === null ? null : $em->merge($this->sexo);
        foreach ($this->listaPaquetes as $paquete) {
            $paquete->preparedPersist($em);
        }
    }
    
    public function __toString() {
        return strval($this->id);
    }
    
    public function getListaIdExternoBoletos(){
        $items = array();
        foreach ($this->listaPaquetes as $paquete) {
            $items = array_merge($items, $paquete->getListaIdExternoBoletos());
        }
        return $items;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getNombreApellidos() {
        return $this->nombreApellidos;
    }

    public function getNacionalidad() {
        return $this->nacionalidad;
    }

    public function getTipoDocumento() {
        return $this->tipoDocumento;
    }

    public function getValorDocumento() {
        return $this->valorDocumento;
    }

    public function getFechaVencimientoDocumento() {
        return $this->fechaVencimientoDocumento;
    }

    public function getFechaNacimiento() {
        return $this->fechaNacimiento;
    }

    public function getSexo() {
        return $this->sexo;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setNombreApellidos($nombreApellidos) {
        $this->nombreApellidos = $nombreApellidos;
    }

    public function setNacionalidad($nacionalidad) {
        $this->nacionalidad = $nacionalidad;
    }

    public function setTipoDocumento($tipoDocumento) {
        $this->tipoDocumento = $tipoDocumento;
    }

    public function setValorDocumento($valorDocumento) {
        $this->valorDocumento = $valorDocumento;
    }

    public function setFechaVencimientoDocumento($fechaVencimientoDocumento) {
        $this->fechaVencimientoDocumento = $fechaVencimientoDocumento;
    }

    public function setFechaNacimiento($fechaNacimiento) {
        $this->fechaNacimiento = $fechaNacimiento;
    }

    public function setSexo($sexo) {
        $this->sexo = $sexo;
    }

    public function addListaPaquetes(Paquete $item) {
       $item->setPasajero($this);
       $this->getListaPaquetes()->add($item);
       return $this;
    }
    
    public function removeListaPaquetes(Paquete $item) {
        $this->getListaPaquetes()->removeElement($item);
        $item->setPasajero(null);
    }
    public function getListaPaquetes() {
        return $this->listaPaquetes;
    }

    public function setListaPaquetes($listaPaquetes) {
        $this->listaPaquetes = $listaPaquetes;
    }


    public function getCompra() {
        return $this->compra;
    }

    public function setCompra($compra) {
        $this->compra = $compra;
    }

    public function precio() {
        $precio = 0;
        foreach ($this->listaPaquetes as $paquete) {
            $precio += $paquete->getPrecio();
        }
        return $precio;
    }
    public function getDetallado() {
        return $this->detallado;
    }

    public function setDetallado($detallado) {
        $this->detallado = $detallado;
    }
}

?>