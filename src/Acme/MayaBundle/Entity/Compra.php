<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass="Acme\MayaBundle\Repository\CompraRepository")
* @ORM\Table(name="compra")
* @ORM\HasLifecycleCallbacks
*/
class Compra{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @ORM\Column(name="precio", type="decimal", precision=10, scale=2, nullable=false)
    */
    protected $precio;
    
    /**
    * @ORM\Column(name="fecha", type="datetime", nullable=false )
    */
    protected $fecha;
    
    /**
    * @ORM\ManyToOne(targetEntity="Cliente", inversedBy="listaCompras", cascade={"persist", "remove"})
    * @ORM\JoinColumn(name="cliente_id", referencedColumnName="id")
    */
    protected $cliente;

    /**
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_id", referencedColumnName="id")
    */
    protected $estacionFactura;
    
    /**
    * @ORM\OneToMany(targetEntity="Pasajero", mappedBy="compra", cascade={"persist", "remove"})
    */
    protected $listaPasajeros;
    
    /**
    * @ORM\ManyToOne(targetEntity="EstadoCompra")
    * @ORM\JoinColumn(name="estado_id", referencedColumnName="id", nullable=true)        
    */
    protected $estado;
    
    /**
    * @ORM\OneToOne(targetEntity="Factura", inversedBy="compra", cascade={"persist", "remove"})
    * @ORM\JoinColumn(name="factura_id", referencedColumnName="id", nullable=true)
    */
    protected $factura;
    
    /**
    * @ORM\Column(name="notificada", type="boolean")
    */
    protected $notificada;
    
    /**  
    * @ORM\Column(type="string", length=4, nullable=true)
    */
    protected $clave;
    
    function __construct() {
        $this->listaPasajeros = new \Doctrine\Common\Collections\ArrayCollection();
        $this->fecha = new \DateTime();
        $this->notificada = false;
        $this->clave = \Acme\BackendBundle\Services\UtilService::generateSimpleNumericPin();
    }
    
    public function preparedPersist($em){
        if($this->cliente->getId() === null || trim(strval($this->cliente->getId())) === ""){
            $this->cliente->preparedPersist($em);
        }else{
            $this->cliente = $em->merge($this->cliente);
        }
        $this->estacionFactura = $em->merge($this->estacionFactura);
        $this->estado = $em->merge($this->estado);
        foreach ($this->listaPasajeros as $pasajero) {
            $pasajero->preparedPersist($em);
        }
    }
    
    public function getCodigoCompra($idEmpresaApp = "") {
        if ($idEmpresaApp === 1) {
            $idEmpresaApp = "M";
        } else if ($idEmpresaApp === 2) {
            $idEmpresaApp = "P";
        } else {
            $idEmpresaApp = "T";
        }
        return $idEmpresaApp . strval($this->getId());
    }
    
    public function __toString() {
        return strval($this->id);
    }
    
    public function getCantidadPasajeros(){
        return count($this->listaPasajeros);
    }
    
    public function getCantidadPaquetes(){
        $cantidad = 0;
        foreach ($this->listaPasajeros as $item) {
            $cantidad += count($item->getListaPaquetes());
        }
        return $cantidad;
    }
    
    public function getListaIdExternoBoletos(){
        $items = array();
        foreach ($this->listaPasajeros as $pasajero) {
            $items = array_merge($items, $pasajero->getListaIdExternoBoletos());
        }
        return $items;
    }
   
    public function getSerieFactura() {
        if($this->factura !== null){
            return $this->factura->getSerie();
        } else{
            return "";
        }
    }
    public function setSerieFactura($serieFactura) {
        if($this->factura === null){
            $this->factura = new Factura();
            $this->factura->setCompra($this);
        }
        $this->factura->setSerie($serieFactura);
    }
    
    public function getCorrelativoFactura() {
        if($this->factura !== null){
            return $this->factura->getCorrelativo();
        } else{
            return "";
        }
    }
    public function setCorrelativoFactura($correlativo) {
        if($this->factura === null){
            $this->factura = new Factura();
            $this->factura->setCompra($this);
        }
        $this->factura->setCorrelativo($correlativo);
    }
    
    public function getObservacionesFactura() {
        if($this->factura !== null){
            return $this->factura->getObservaciones();
        } else{
            return "";
        }
    }
    public function setObservacionesFactura($observaciones) {
        if($this->factura === null){
            $this->factura = new Factura();
            $this->factura->setCompra($this);
        }
        $this->factura->setObservaciones($observaciones);
    }
    
    public function getId() {
        return $this->id;
    }

    public function getPrecio() {
        return $this->precio;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getCliente() {
        return $this->cliente;
    }

    public function getEstacionFactura() {
        return $this->estacionFactura;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setPrecio($precio) {
        $this->precio = $precio;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function setCliente($cliente) {
        $this->cliente = $cliente;
    }

    public function setEstacionFactura($estacionFactura) {
        $this->estacionFactura = $estacionFactura;
    }

    public function getEstado() {
        return $this->estado;
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
    
    public function getListaPasajeros() {
        return $this->listaPasajeros;
    }

    public function setListaPasajeros($listaPasajeros) {
        $this->listaPasajeros = $listaPasajeros;
    }

    public function addListaPasajeros(Pasajero $item) {
       $item->setCompra($this);
       $this->getListaPasajeros()->add($item);
       return $this;
    }
    
    public function removeListaPasajeros(Pasajero $item) {
        $this->getListaPasajeros()->removeElement($item);
        $item->setCompra(null);
    }
    
    public function getFactura() {
        return $this->factura;
    }

    public function setFactura($factura) {
        $this->factura = $factura;
    }
    
    public function getClave() {
        return $this->clave;
    }

    public function setClave($clave) {
        $this->clave = $clave;
    }
}

?>