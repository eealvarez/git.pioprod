<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;
use Acme\MayaBundle\Validator as CustomAssert;

/**
* @ORM\Entity(repositoryClass="Acme\MayaBundle\Repository\ClienteRepository")
* @ORM\Table(name="cliente", uniqueConstraints={@ORM\UniqueConstraint(name="CUSTOM_IDX_CLIENTE_NACION_TIPO_DPI_NOMBRE_NIT", columns={"nacionalidad_id", "documento_id", "numeroDocumento", "nombreApellidos", "nit"})})
* @ORM\HasLifecycleCallbacks
*/
class Cliente{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
       
    /**
    * @Assert\Email(
    *     message = "El correo '{{ value }}' no es válido.",
    *     checkMX = true,
    *     checkHost = false
    * )
    * @Assert\Length(
    *      min = "3",
    *      max = "40",
    *      minMessage = "El correo por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El correo no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=40, nullable=true)
    */
    protected $correo;
    
    /**
    * @Assert\NotBlank(message = "El nombre no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "100",
    *      minMessage = "El nombre por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El nombre no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=100, nullable=false)
    */
    protected $nombreApellidos;
       
    /**
    * @Assert\Length(
    *      min = "8",
    *      max = "15",
    *      minMessage = "El teléfono por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El teléfono no puede tener más de {{ limit }} caracteres."
    * ) 
    * @Assert\Regex(
    *     pattern="/((^\d{8,10}$)|(^\d{8,10}[\-|,]{1,1}\d{8,10}$))/",
    *     match=true,
    *     message="Teléfonos solo puede contener números y una coma. No puede tener espacios."
    * )
    * @ORM\Column(type="string", length=21, nullable=true)
    */
    protected $telefono;
    
    /**
    * @Assert\NotBlank(message = "El número del documento no debe estar en blanco")
    * @Assert\Length(
    *      max = "40",
    *      maxMessage = "El número del documento no puede tener más de {{ limit }} caracteres."
    * )  
    * @Assert\Regex(
    *     pattern="/(^[a-zA-Z0-9\s]{0,40}$)/",
    *     match=true,
    *     message="El número del documento solo puede contener números, letras y espacios. No puede tener guiones."
    * )    
    * @ORM\Column(type="string", length=40, nullable=false)
    */
    
    protected $numeroDocumento;
    
    /**
    * @Assert\Length(
    *      max = "20",
    *      maxMessage = "El nit no puede tener más de {{ limit }} caracteres."
    * )
    * @Assert\Regex(
    *     pattern="/((^C\/F$)|(^[A-Z0-9]{0,20}$)|(^[A-Z0-9]{0,15}[\-]{1,1}[A-Z0-9]{1,4}$))/",
    *     match=true,
    *     message="El NIT solo puede contener un guion, números y letras mayúsculas. No puede tener espacios."
    * )
    * @ORM\Column(type="string", length=20, nullable=false)
    */
    protected $nit;
    
    /**
    * @ORM\OneToOne(targetEntity="Acme\BackendBundle\Entity\UserOauth", inversedBy="cliente" , cascade={"persist"})
    * @ORM\JoinColumn(name="usuario_id", referencedColumnName="id", nullable=true)
    */
    protected $usuario;
    
    /**
    * @ORM\OneToMany(targetEntity="Compra", mappedBy="cliente")
    */
    protected $listaCompras;
    
    /**
    * @ORM\ManyToOne(targetEntity="Nacionalidad", cascade={"persist"})
    * @ORM\JoinColumn(name="nacionalidad_id", referencedColumnName="id")
    */
    protected $nacionalidad;
    
    /**
     * @ORM\ManyToOne(targetEntity="Documento", cascade={"persist"})
     * @ORM\JoinColumn(name="documento_id", referencedColumnName="id")   
     */
    protected $tipoDocumento;
    
    
    /**
    * @Assert\Date(message = "Fecha no valida")
    * @ORM\Column(name="fecha_vencimiento_documento", type="date", nullable=true)
    */
    protected $fechaVencimientoDocumento;
    
    /**
    * @Assert\Date(message = "Fecha no valida")
    * @ORM\Column(name="fecha_nacimiento", type="date", nullable=true)
    */
    protected $fechaNacimiento;
    
    /**
    * @ORM\ManyToOne(targetEntity="Sexo", cascade={"persist"})
    * @ORM\JoinColumn(name="sexo_id", referencedColumnName="id", nullable=true)   
    */
    protected $sexo;
    
    public function __toString() {
        return strval($this->id);
    }
    
    function __construct() {
        $this->compras = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function preparedPersist($em){
        if($this->usuario !== null){
            $this->usuario = $em->merge($this->usuario);
        }
        if($this->nacionalidad !== null){
            $this->nacionalidad = $em->merge($this->nacionalidad);
        }
        if($this->tipoDocumento !== null){
            $this->tipoDocumento = $em->merge($this->tipoDocumento);
        }
        if($this->sexo !== null){
            $this->sexo = $em->merge($this->sexo);
        }
    }
    
    public function getId() {
        return $this->id;
    }

    public function getCorreo() {
        return $this->correo;
    }

    public function getNombreApellidos() {
        return $this->nombreApellidos;
    }

    public function getTelefono() {
        return $this->telefono;
    }

    public function getNumeroDocumento() {
        return $this->numeroDocumento;
    }

    public function getUsuario() {
        return $this->usuario;
    }

    public function getListaCompras() {
        return $this->listaCompras;
    }

    public function getNacionalidad() {
        return $this->nacionalidad;
    }

    public function getTipoDocumento() {
        return $this->tipoDocumento;
    }

    public function getFechaVencimientoDocumento() {
        return $this->fechaVencimientoDocumento;
    }

    public function getSexo() {
        return $this->sexo;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setCorreo($correo) {
        $this->correo = $correo;
    }

    public function setNombreApellidos($nombreApellidos) {
        $this->nombreApellidos = $nombreApellidos;
    }

    public function setTelefono($telefono) {
        $this->telefono = $telefono;
    }

    public function setNumeroDocumento($numeroDocumento) {
        $this->numeroDocumento = $numeroDocumento;
    }

    public function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

    public function setListaCompras($listaCompras) {
        $this->listaCompras = $listaCompras;
    }

    public function setNacionalidad($nacionalidad) {
        $this->nacionalidad = $nacionalidad;
    }

    public function setTipoDocumento($tipoDocumento) {
        $this->tipoDocumento = $tipoDocumento;
    }

    public function setFechaVencimientoDocumento($fechaVencimientoDocumento) {
        $this->fechaVencimientoDocumento = $fechaVencimientoDocumento;
    }

    public function setSexo($sexo) {
        $this->sexo = $sexo;
    }

    public function getFechaNacimiento() {
        return $this->fechaNacimiento;
    }

    public function setFechaNacimiento($fechaNacimiento) {
        $this->fechaNacimiento = $fechaNacimiento;
    }

    public function getNit() {
        return $this->nit;
    }

    public function setNit($nit) {
        $this->nit = $nit;
    }

   

}

?>