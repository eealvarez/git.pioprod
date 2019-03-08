<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\MayaBundle\Validator as CustomAssert;

/**
* @ORM\Entity()
* @ORM\Table(name="bus_tipo")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields ="alias", message="El alias ya existe")
* @Assert\Callback(methods={"validacionesGenerales"})
*/
class TipoBus{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="NONE")
    */
    protected $id;
    
    /**
    * @Assert\Length(
    *      min = "1",
    *      max = "10",
    *      minMessage = "El alias por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El alias no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=10, unique=true)
    */
    protected $alias;
    
    
    /**
    * @Assert\NotBlank(message = "La clase no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="ClaseBus")
    * @ORM\JoinColumn(name="clase_id", referencedColumnName="id")        
    */
    protected $clase;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $nivel2; 
    
    /**
    * @Assert\NotBlank(message = "El total de asientos no debe estar en blanco")
    * @Assert\Regex(
    *     pattern="/^\d*$/",
    *     message="El total de asientos solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "1",
    *      max = "100",
    *      minMessage = "El total de asientos no debe ser menor que {{ limit }}.",
    *      maxMessage = "El total de asientos no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El total de asientos debe ser un número válido."
    * )   
    * @ORM\Column(type="integer")
    */
    protected $totalAsientos;
    
    
    /**
    * @ORM\OneToMany(targetEntity="AsientoBus", mappedBy="tipoBus", 
    * cascade={"persist", "remove"}, orphanRemoval=true)
    */
    protected $listaAsiento;
    protected $listaAsientoHidden;
    
     /**
    * @ORM\OneToMany(targetEntity="SenalBus", mappedBy="tipoBus", 
    * cascade={"persist", "remove"}, orphanRemoval=true)
    */    
    protected $listaSenal;
    protected $listaSenalHidden;
    
    public function __toString() {
        return strval($this->id);
    }
    
    /*
     * VALIDACION DE QUE DOS ELEMENTOS NO TENGAN LA MISMA POSICION EN EL MAPA.
     * VALIDACION DE QUE SE SELECCIONE LOS ASIENTOS ADECUADOS A LOS PERMITIDOS PARA LA CLASE DE BUS.
     */
    public function validacionesGenerales(ExecutionContext $context)
    {
        
  
    }
    
    public function addListaAsiento(AsientoBus $item) {
       $item->setTipoBus($this);
       $this->getListaAsiento()->add($item);
       $this->setTotalAsientos($this->totalAsientos + 1);
       return $this;
    }
    
    public function removeListaAsiento($item) {
        $this->getListaAsiento()->removeElement($item);
        $item->setTipoBus(null);
        $this->setTotalAsientos($this->totalAsientos - 1);
    }
    
    public function addListaSenal(SenalBus $item) {
       $item->setTipoBus($this);
       $this->getListaSenal()->add($item);
       return $this;
    }
    
    public function removeListaSenal($item) { 
        $this->getListaSenal()->removeElement($item); 
        $item->setTipoBus(null);        
    }
    
    
    function __construct() {
        $this->nivel2 = false;
        $this->totalAsientos = 0;
        $this->listaAsiento = new \Doctrine\Common\Collections\ArrayCollection();
        $this->listaSenal = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getAlias() {
        return $this->alias;
    }

    public function getClase() {
        return $this->clase;
    }

    public function getNivel2() {
        return $this->nivel2;
    }

    public function getTotalAsientos() {
        return $this->totalAsientos;
    }


    public function getListaAsiento() {
        return $this->listaAsiento;
    }

    public function getListaAsientoHidden() {
        return $this->listaAsientoHidden;
    }

   public function setId($id) {
        $this->id = $id;
    }

    public function setAlias($alias) {
        $this->alias = $alias;
    }

    public function setClase($clase) {
        $this->clase = $clase;
    }

    public function setNivel2($nivel2) {
        $this->nivel2 = $nivel2;
    }

    public function setTotalAsientos($totalAsientos) {
        $this->totalAsientos = $totalAsientos;
    }
    
    public function setListaAsiento($listaAsiento) {
        $this->listaAsiento = $listaAsiento;
    }

    public function setListaAsientoHidden($listaAsientoHidden) {
        $this->listaAsientoHidden = $listaAsientoHidden;
    }
    public function getListaSenal() {
        return $this->listaSenal;
    }

    public function getListaSenalHidden() {
        return $this->listaSenalHidden;
    }

    public function setListaSenal($listaSenal) {
        $this->listaSenal = $listaSenal;
    }

    public function setListaSenalHidden($listaSenalHidden) {
        $this->listaSenalHidden = $listaSenalHidden;
    }

    public function existeAsientoB (){
        foreach ($this->getListaAsiento() as $asiento) {
            if($asiento->getClase()->getId() === "2"){
                return true;
            }
        }
        return false;
    }
    

}

?>