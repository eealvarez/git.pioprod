<?php

namespace Acme\MayaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Acme\BackendBundle\Services\UtilService;

/**
 * @ORM\Entity(repositoryClass="Acme\MayaBundle\Repository\ImagenRepository")
 * @ORM\Table(name="galeria_imagen")
 * @ORM\HasLifecycleCallbacks
 */
class Imagen {

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Galeria", inversedBy="imagenes")
     * @ORM\JoinColumn(name="galeria_id", referencedColumnName="id", nullable=false)
     */
    protected $galeria;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $referencia;

    /**
     * @Assert\Length(
     *      max = "100",
     *      maxMessage = "El nombre no puede tener más de {{ limit }} caracteres."
     * )    
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $nombre;

    /**
     * @Assert\Length(      
     *      max = "1024",
     *      maxMessage = "La descripción no puede tener más de {{ limit }} caracteres de largo"
     * )
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $descripcion;

    /**
     * @Assert\File(maxSize="6M")
     */
    protected $file;

    /**
     * @ORM\Column(name="imagen_normal", type="text", nullable=true)
     */
    protected $imagenNormal;

    /**
     * @ORM\Column(name="imagen_pequena", type="text", nullable=true)
     */
    protected $imagenPequena;

    /**
     * @Assert\Length(      
     *      max = "10",
     *      maxMessage = "El formato no puede tener más de {{ limit }} caracteres de largo"
     * )
     * @ORM\Column(type="string", length=10, nullable=false)
     */
    protected $formato;

    /**
     * @ORM\Column(name="ancho", type="integer", nullable=true)
     */
    protected $ancho;

    /**
     * @ORM\Column(name="alto", type="integer", nullable=true)
     */
    protected $alto;

    /**
     * @ORM\Column(name="url", type="string", nullable=true)
     */
    protected $url;

    function __construct() {
        
    }

    public function getId() {
        return $this->id;
    }

    public function getGaleria() {
        return $this->galeria;
    }

    public function getFile() {
        return $this->file;
    }

    public function getImagenNormal() {
        return $this->imagenNormal;
    }

    public function getImagenPequena() {
        return $this->imagenPequena;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setGaleria($galeria) {
        $this->galeria = $galeria;
    }

    public function setFile($file) {
        $this->file = $file;
    }

    public function setImagenNormal($imagenNormal) {
        $this->imagenNormal = $imagenNormal;
    }

    public function setImagenPequena($imagenPequena) {
        $this->imagenPequena = $imagenPequena;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function getFormato() {
        return $this->formato;
    }

    public function setFormato($formato) {
        $this->formato = $formato;
    }

    public function getReferencia() {
        return $this->referencia;
    }

    public function setReferencia($referencia) {
        $this->referencia = $referencia;
    }

    public function getAncho() {
        return $this->ancho;
    }

    public function getAlto() {
        return $this->alto;
    }

    public function setAncho($ancho) {
        $this->ancho = $ancho;
    }

    public function setAlto($alto) {
        $this->alto = $alto;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getUrl() {
        return $this->url;
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    protected function getUploadRootDir() {
        return __DIR__ . '/../../../../web/uploads/';
    }

    protected function getGalleryRootDir() {
        return __DIR__ . '/../../../../web/images/gallery/';
    }

    public function upload() {
        if (null !== $this->file) {
            $this->formato = $this->file->guessExtension();
            $filename = sha1(uniqid(mt_rand(), true)) . '.' . $this->formato;
            $pathImagenNormal = $this->getUploadRootDir() . $filename;
            copy($this->file, $pathImagenNormal);
            UtilService::resize($pathImagenNormal, array(
                'max_width' => $this->ancho !== null ? $this->ancho : 1000,
                'max_height' => $this->alto !== null ? $this->alto : 800,
            ));
            $fpr1 = fopen($pathImagenNormal, "r");
            $this->imagenNormal = base64_encode(fread($fpr1, filesize($pathImagenNormal)));
            fclose($fpr1);
            if (file_exists($pathImagenNormal)) {
                chmod($pathImagenNormal, 0777);
                unlink($pathImagenNormal);
            }

            $filename = sha1(uniqid(mt_rand(), true)) . '.' . $this->formato;
            $pathImagenPequena = $this->getUploadRootDir() . $filename;
            copy($this->file, $pathImagenPequena);
            UtilService::resize($pathImagenPequena, array(
                'max_width' => 254,
                'max_height' => 199,
            ));
            $fpr2 = fopen($pathImagenPequena, "r");
            $this->imagenPequena = base64_encode(fread($fpr2, filesize($pathImagenPequena)));
            fclose($fpr2);
            if (file_exists($pathImagenPequena)) {
                chmod($pathImagenPequena, 0777);
                unlink($pathImagenPequena);
            }

            if (file_exists($this->file)) {
                chmod($this->file, 0777);
                unlink($this->file);
            }

            $pathFileMin = $this->getGalleryRootDir() . 'image_' . $this->getId() . '_min.' . $this->getFormato();
            if (file_exists($pathFileMin)) {
                chmod($pathFileMin, 0777);
                unlink($pathFileMin);
            }

            $pathFileMax = $this->getGalleryRootDir() . 'image_' . $this->getId() . '_max.' . $this->getFormato();
            if (file_exists($pathFileMax)) {
                chmod($pathFileMax, 0777);
                unlink($pathFileMax);
            }
        }
    }

}
