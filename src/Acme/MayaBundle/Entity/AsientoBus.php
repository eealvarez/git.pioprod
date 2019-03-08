<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
* @ORM\Table(name="asiento")
* @ORM\HasLifecycleCallbacks
*/
class AsientoBus{
    
   
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="NONE")
    */
    protected $id;
    
    /**
    * @ORM\ManyToOne(targetEntity="TipoBus", inversedBy="listaAsiento")
    * @ORM\JoinColumn(name="tipoBus_id", referencedColumnName="id")
    */
    protected $tipoBus;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $nivel2;
    
    /**
    * @ORM\ManyToOne(targetEntity="ClaseAsiento")
    * @ORM\JoinColumn(name="clase_id", referencedColumnName="id")        
    */
    protected $clase;
    
    /**
    * @ORM\Column(type="integer")
    */
    protected $numero;
    
    /**
    * @ORM\Column(type="integer")
    */
    protected $coordenadaX;
    
    /**
    * @ORM\Column(type="integer")
    */
    protected $coordenadaY;
    
    public function __toString() {
         $str = "Id:" . strval($this->id);
        $str .= "Nro:" . strval($this->numero);
        if($this->nivel2 === false){
            $str .= ", Nivel:1";
        }else{
            $str .= ", Nivel:2";
        }
        //$str .= ", Clase:" . $this->getClase()->getNombre();
        return  $str;
    }
    
    function __construct() {
        
    }
    
    public function getId() {
        return $this->id;
    }

    public function getTipoBus() {
        return $this->tipoBus;
    }

    public function getNivel2() {
        return $this->nivel2;
    }

    public function getClase() {
        return $this->clase;
    }

    public function getNumero() {
        return $this->numero;
    }

    public function getCoordenadaX() {
        return $this->coordenadaX;
    }

    public function getCoordenadaY() {
        return $this->coordenadaY;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setTipoBus($tipoBus) {
        $this->tipoBus = $tipoBus;
    }

    public function setNivel2($nivel2) {
        $this->nivel2 = $nivel2;
    }

    public function setClase($clase) {
        $this->clase = $clase;
    }

    public function setNumero($numero) {
        $this->numero = $numero;
    }

    public function setCoordenadaX($coordenadaX) {
        $this->coordenadaX = $coordenadaX;
    }

    public function setCoordenadaY($coordenadaY) {
        $this->coordenadaY = $coordenadaY;
    }
    


}

?>