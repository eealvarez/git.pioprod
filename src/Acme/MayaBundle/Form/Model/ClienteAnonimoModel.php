<?php

namespace Acme\MayaBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ClienteAnonimoModel {

    /**
     * @Assert\NotBlank(message = "El correo no debe estar en blanco")
     * @Assert\Email(
     *     message = "El correo '{{ value }}' no es válido.",
     *     checkMX = true,
     *     checkHost = true
     * )
     * @var string
     */
    protected $correo;

    /**
     * @var string
     */
    protected $nombreApellidos;

    /**
     * @Assert\NotBlank(message = "El tipo de documento no debe estar en blanco")
     */
    protected $tipoDocumento;

    /**
     * @Assert\NotBlank(message = "El numero no debe estar en blanco")

     */
    protected $numeroDocumento;

    /**
     */
    protected $telefono;
    protected $aceptoTerminos;

    /**

     */
    protected $sexo;
    protected $nacionalidad;
    protected $fechaVencimientoDocumento;
    protected $fechaNacimiento;

    public function __construct() {
        
    }

    public function getCorreo() {
        return $this->correo;
    }

    public function getNombreApellidos() {
        return $this->nombreApellidos;
    }

    public function getTipoDocumento() {
        return $this->tipoDocumento;
    }

    public function getNumeroDocumento() {
        return $this->numeroDocumento;
    }

    public function getTelefono() {
        return $this->telefono;
    }

    public function getAceptoTerminos() {
        return $this->aceptoTerminos;
    }

    public function getSexo() {
        return $this->sexo;
    }

    public function getNacionalidad() {
        return $this->nacionalidad;
    }

    public function getFechaVencimientoDocumento() {
        return $this->fechaVencimientoDocumento;
    }

    public function getFechaNacimiento() {
        return $this->fechaNacimiento;
    }

    public function setCorreo($correo) {
        $this->correo = $correo;
    }

    public function setNombreApellidos($nombreApellidos) {
        $this->nombreApellidos = $nombreApellidos;
    }

    public function setTipoDocumento($tipoDocumento) {
        $this->tipoDocumento = $tipoDocumento;
    }

    public function setNumeroDocumento($numeroDocumento) {
        $this->numeroDocumento = $numeroDocumento;
    }

    public function setTelefono($telefono) {
        $this->telefono = $telefono;
    }

    public function setAceptoTerminos($aceptoTerminos) {
        $this->aceptoTerminos = $aceptoTerminos;
    }

    public function setSexo($sexo) {
        $this->sexo = $sexo;
    }

    public function setNacionalidad($nacionalidad) {
        $this->nacionalidad = $nacionalidad;
    }

    public function setFechaVencimientoDocumento($fechaVencimientoDocumento) {
        $this->fechaVencimientoDocumento = $fechaVencimientoDocumento;
    }

    public function setFechaNacimiento($fechaNacimiento) {
        $this->fechaNacimiento = $fechaNacimiento;
    }



}
