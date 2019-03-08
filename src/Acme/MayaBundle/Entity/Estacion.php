<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Acme\MayaBundle\Entity\TipoEstacion;

/**
* @ORM\Entity(repositoryClass="Acme\MayaBundle\Repository\EstacionRepository")
* @ORM\Table(name="estacion")
* @ORM\HasLifecycleCallbacks()
*/
class Estacion{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="NONE")
    */
    protected $id;
    
   /**
     @ORM\Column(type="string", length=50, unique=true)
    */
    protected $nombre;
    
    /**
    * @ORM\Column(type="text")
    */
    protected $direccion;    
    
     /**
    * @ORM\Column(type="string", length=3, unique=true)
    */
    protected $alias;
    
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $activo;
    
    
    /**
    * @ORM\ManyToOne(targetEntity="TipoEstacion")
    * @ORM\JoinColumn(name="tipoEstacion_id", referencedColumnName="id")        
    */
    protected $tipo;
    
     /**
    * @ORM\Column(type="text", nullable=true)
    */
    protected $listaTelefonos;
    
    /**
    * @ORM\Column(type="text", nullable=true)
    */
    protected $listaCorreos;
    
    /**  
    * @ORM\Column(type="decimal", precision=15, scale=10, nullable=true)
    */
    protected $longitude;
    
    /**  
    * @ORM\Column(type="decimal", precision=15, scale=10, nullable=true)
    */
    protected $latitude;
    
    /**
    * @ORM\ManyToOne(targetEntity="Departamento")
    * @ORM\JoinColumn(name="departamento_id", referencedColumnName="id", nullable=true)        
    */
    protected $departamento;
    
    /**
    * @ORM\Column(type="boolean", nullable=true)
    */
    protected $facturacion;
    
    public function __construct() {
        $this->activo = true;
        $this->facturacion = false;
        $this->listaTelefonos = "";
        $this->listaCorreos = "";
    }
    
    public function __toString() {
        return $this->getAliasNombre();
    }
    
    public function getAliasNombre($separador = "-") {
        if ($this->alias != null && trim($this->alias) != "") {
            return trim($this->alias) . $separador . $this->nombre;
        } else {
            return $this->nombre;
        }
    }
    
    public function getNombreDepartamento() {
        return $this->nombre . ($this->departamento !== null ? ", " . $this->departamento->getNombre() : "");
    }
    
    public function getIdAlias() {
        return strval($this->id) . " - " . $this->alias;
    }
    
    public function getTieneMap() {
        return ($this->latitude !== null && $this->longitude !== null);
    }
    
    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getDireccion() {
        return $this->direccion;
    }

    public function getAlias() {
        return $this->alias;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function getListaTelefonos() {
        return $this->listaTelefonos;
    }

    public function getListaCorreos() {
        return $this->listaCorreos;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setDireccion($direccion) {
        $this->direccion = $direccion;
    }

    public function setAlias($alias) {
        $this->alias = $alias;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    public function setListaTelefonos($listaTelefonos) {
        $this->listaTelefonos = $listaTelefonos;
    }

    public function setListaCorreos($listaCorreos) {
        $this->listaCorreos = $listaCorreos;
    }

    public function getLongitude() {
        return $this->longitude;
    }

    public function getLatitude() {
        return $this->latitude;
    }

    public function setLongitude($longitude) {
        $this->longitude = $longitude;
    }

    public function setLatitude($latitude) {
        $this->latitude = $latitude;
    }
    
    public function getFacturacion() {
        return $this->facturacion;
    }

    public function setFacturacion($facturacion) {
        $this->facturacion = $facturacion;
    }
    
    public function getDepartamento() {
        return $this->departamento;
    }

    public function setDepartamento($departamento) {
        $this->departamento = $departamento;
    }
}

?>