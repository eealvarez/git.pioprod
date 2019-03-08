<?php
namespace Acme\MayaBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class AsientosCompraModel {
    
   protected  $cantidad_pasajeros;
    protected $precioIda;
    protected $precioRegreso;
    protected $tipoBus;
    
    
    
    public function __construct() { 
       
        
    }
    public function getCantidad_pasajeros() {
        return $this->cantidad_pasajeros;
    }

    public function getPrecioIda() {
        return $this->precioIda;
    }

    public function getPrecioRegreso() {
        return $this->precioRegreso;
    }

    public function getTipoBus() {
        return $this->tipoBus;
    }

    public function setCantidad_pasajeros($cantidad_pasajeros) {
        $this->cantidad_pasajeros = $cantidad_pasajeros;
    }

    public function setPrecioIda($precioIda) {
        $this->precioIda = $precioIda;
    }

    public function setPrecioRegreso($precioRegreso) {
        $this->precioRegreso = $precioRegreso;
    }

    public function setTipoBus($tipoBus) {
        $this->tipoBus = $tipoBus;
    }






   
}