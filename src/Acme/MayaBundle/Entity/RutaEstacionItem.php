<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
* @ORM\Entity(repositoryClass="Acme\MayaBundle\Repository\RutaEstacionItemRepository")
* @ORM\Table(name="ruta_estacion_item")
* @ORM\HasLifecycleCallbacks
* @Assert\Callback(methods={"validacionesGenerales"})
* @DoctrineAssert\UniqueEntity(fields = {"ruta" , "estacion"}, message="La estación ya esta asociada a la ruta.")
*/
class RutaEstacionItem {
   
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @ORM\ManyToOne(targetEntity="Ruta", inversedBy="listaEstacionesIntermediaOrdenadas")
    * @ORM\JoinColumn(name="ruta_codigo", referencedColumnName="codigo", nullable=false)
    */
    protected $ruta;
    
    /**
    * @Assert\NotNull(message = "La estación intermedia de la ruta no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_id", referencedColumnName="id", nullable=false)   
    */
    protected $estacion;
    
    /**
    * @Assert\NotNull(message = "La posicion de la estación no debe estar en blanco")
    * @Assert\Regex(
    *     pattern="/^\d*$/",
    *     message="El número solo puede contener números"
    * ) 
    * @ORM\Column(type="integer", nullable=true)
    */
    protected $posicion;
    
    function __construct() {
        $this->posicion = 0;
    }
    
    public function __toString() {
        $str = "";
        if($this->ruta !== null){
            $str .= "Ruta:" . $this->ruta;
        }
        if($this->estacion !== null){
            $str .= "|Estación:" . $this->estacion;
        }
        if($this->estacion !== null){
            $str .= "|POS:" . $this->posicion;
        }
        return $str;
    }
    
    public function validacionesGenerales(ExecutionContext $context)
    {
        
    }

    public function getId() {
        return $this->id;
    }

    public function getRuta() {
        return $this->ruta;
    }

    public function getEstacion() {
        return $this->estacion;
    }

    public function getPosicion() {
        return $this->posicion;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setRuta($ruta) {
        $this->ruta = $ruta;
    }

    public function setEstacion($estacion) {
        $this->estacion = $estacion;
    }

    public function setPosicion($posicion) {
        $this->posicion = $posicion;
    }
}
