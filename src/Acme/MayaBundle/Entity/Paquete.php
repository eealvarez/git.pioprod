<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Acme\MayaBundle\Entity\Boleto;


/**
 * @ORM\Entity(repositoryClass="Acme\MayaBundle\Repository\PaqueteRepository")
 * @ORM\Table(name="paquete")
 * @ORM\HasLifecycleCallbacks
 */
class Paquete {

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
     * @ORM\ManyToOne(targetEntity="Pasajero", inversedBy="listaPaquetes")
     * @ORM\JoinColumn(name="pasajero_id", referencedColumnName="id")
     */
    protected $pasajero;

    /**
     * @ORM\ManyToOne(targetEntity="Conexion")
     * @ORM\JoinColumn(name="conexion_id", referencedColumnName="id")
     */
    protected $conexion;
    
    /**
     * @ORM\OneToMany(targetEntity="Boleto", mappedBy="paquete", 
     * cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $listaBoletos;

    function __construct() {
        $this->listaBoletos = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function preparedPersist($em){
         $this->conexion = $em->merge($this->conexion);
         foreach ($this->listaBoletos as $boleto) {
            $boleto->preparedPersist($em);
        }
    }
    
    public function __toString() {
        return strval($this->id);
    }
    
    public function getListaIdExternoBoletos(){
        $items = array();
        foreach ($this->listaBoletos as $boleto) {
            $items[] = $boleto->getIdExternoBoleto();
        }
        return $items;
    }

    public function getId() {
        return $this->id;
    }

    public function getPrecio() {
        return $this->precio;
    }

    public function getPasajero() {
        return $this->pasajero;
    }

    public function getConexion() {
        return $this->conexion;
    }

    public function getListaBoletos() {
        return $this->listaBoletos;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setPrecio($precio) {
        $this->precio = $precio;
    }

    public function setPasajero($pasajero) {
        $this->pasajero = $pasajero;
    }

    public function setConexion($conexion) {
        $this->conexion = $conexion;
    }

    public function setListaBoletos($listaBoletos) {
        $this->listaBoletos = $listaBoletos;
    }

    public function addListaBoletos(Boleto $item) {
       $item->setPaquete($this);
       $this->getListaBoletos()->add($item);
       return $this;
    }
    
    public function removeListaPaquetes(Boleto $item) {
        $this->getListaBoletos()->removeElement($item);
        $item->setPaquete(null);
    }
    
    public function existeBoletoConexion($idSalida){
        foreach ($this->listaBoletos as $boleto) {
            if($boleto->getConexion()->getIdExterno() === $idSalida)
                return true;
        }
        return false;
    }
}

?>