<?php
namespace Acme\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
* @ORM\Entity(repositoryClass="Acme\BackendBundle\Repository\RolRepository")
* @ORM\Table(name="custom_rol")
*/
class Rol implements RoleInterface{
    
    /**
    * @ORM\Id
    * @Assert\NotBlank(message = "El nombre no debe estar en blanco")
    * @Assert\Length(
    *      min = "2",
    *      max = "255",
    *      minMessage = "El nombre por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El nombre no puede tener más de {{ limit }} caracteres."
    * ) 
    * @Assert\Regex(
    *     pattern="/^[A-Z\_]{1,255}$/",
    *     match=true,
    *     message="El nombre solo puede tener letras mayúsculas y guiones bajo(_)"
    * )   
    * @ORM\Column(type="string", length=255)
    */    
    protected $nombre;
    
    /**
    * @Assert\Length(
    *      max = "255",
    *      maxMessage = "La descripción no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=255)
    */    
    protected $descripcion;
    
    public function __toString() {
        return 'ROLE_' . strtoupper($this->nombre);
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }
    
    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function getRole() {
        return 'ROLE_' . strtoupper($this->nombre);
    }
}

?>
