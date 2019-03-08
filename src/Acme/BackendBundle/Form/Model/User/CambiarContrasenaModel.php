<?php
namespace Acme\BackendBundle\Form\Model\User;

use Symfony\Component\Validator\Constraints as Assert;

class CambiarContrasenaModel {
    
    /**
    * @Assert\NotBlank(message = "El username no debe estar en blanco.")
    */
    protected $username;
    
    /**
    * @Assert\NotBlank(message = "La contraseña nueva no debe estar en blanco.")   
    * @Assert\Length(
    *      min = "2",
    *      max = "25",
    *      minMessage = "La contraseña debe tener {{ limit }} caracteres como mínimo.",
    *      maxMessage = "La contraseña debe tener {{ limit }} caracteres como máximo."
    * )
    */
    protected $plainPassword;
    
    public function getUsername() {
        return $this->username;
    }

    public function getPlainPassword() {
        return $this->plainPassword;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setPlainPassword($plainPassword) {
        $this->plainPassword = $plainPassword;
    }
}