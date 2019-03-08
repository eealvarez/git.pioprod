<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* @ORM\Entity
* @ORM\Table(name="sexo")
*/
class Sexo{
    
    const MASCULINO = 1;
    const FEMENINO = 2;
    
     /**
    * @ORM\Id
    * @ORM\Column(type="smallint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotBlank(message = "La sigla no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "1",
    *      minMessage = "La sigla por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "La sigla no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=1, nullable=false)
    */
    protected $sigla;
    
    /**
    * @Assert\NotBlank(message = "El nombre no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "40",
    *      minMessage = "El nombre por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El nombre no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=40, nullable=false)
    */
    protected $nombre;
    
    public function __toString() {
        return strval($this->id);
    }
    
    public function getId() {
        return $this->id;
    }

    public function getSigla() {
        return $this->sigla;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setSigla($sigla) {
        $this->sigla = $sigla;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }
}

?>