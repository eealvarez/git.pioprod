<?php

namespace Acme\BackendBundle\Services;

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
use Facebook\GraphUser;
use Acme\BackendBundle\Entity\UserOauth;

class RedesSocialesService{
    
    const PUBLICIDAD_LOGIN = "_LOGIN";
    const PUBLICIDAD_BOLETO = "_BOLETO";
    const PUBLICIDAD_ENCO = "_ENCOMIENDA";

    protected $container;
    protected $doctrine;
    protected $logger;
  
   public function __construct($container) { 
        $this->container = $container;
        $this->doctrine = $this->container->get('doctrine');
        $this->logger = $this->container->get('logger');
   }
   
   public function publicarMuro($user = null, $type = "", $text = ""){
       
        try {
            
            $message = null;
            $description = null;
            $urlFile = null;
            $galerias = $this->doctrine->getRepository('MayaBundle:Galeria')->listarGaleriaByReferencia("PUBLICIDAD".$type);
            if(count($galerias) > 0){
                $galeria = $galerias[0];
                $imagenes = $galeria->getImagenes();
                $cantidad = count($imagenes);
                if( $cantidad > 0){
                    $pos = rand(0,($cantidad-1));
                    $imagen =  $imagenes[$pos];
                    $message = $imagen->getNombre();
                    $description = $imagen->getDescripcion();
                    $name = 'image_' . $imagen->getId() . '_max.' . $imagen->getFormato();
                    $pathFile = $this->getGalleryRootDir() . $name;
                    if(!file_exists($pathFile)){
                        $ifp = fopen($pathFile, "wb");
                        fwrite($ifp, base64_decode($imagen->getImagenNormal()));
                        fclose($ifp);
                    }
                    $urlFile =  $this->container->getParameter("url_empresa_app") . "/images/gallery/" . $name;
                }
            }
            
            
            if($user instanceof UserOauth){
                
                if($user->getProvider() === UserOauth::PROVIDER_FACEBOOK){
                    FacebookSession::setDefaultApplication(
                        $this->container->getParameter("facebook_client_id"), 
                        $this->container->getParameter("facebook_client_secret"));
                    
                    $token = $user->getAccessToken();
                    $facebookSession = new FacebookSession($token);
                    $options = array(
                        'published' => 'true',
                        'link' => $this->container->getParameter("url_empresa_app"),
                        'message' => $this->container->getParameter("nombre_empresa_app") . $text,
                        'name' => $this->container->getParameter("nombre_empresa_app"),
                        'caption' => $this->container->getParameter("url_empresa_app"),
                    );
                    if($message !== null){ $options['message'] = $text . $message; }
                    if($description !== null){ $options['description'] = $description; }
                    if($urlFile !== null){ $options['picture'] = $urlFile; }
                    
                    $facebookRequest = new FacebookRequest($facebookSession, 'POST', '/me/feed', $options);
                    $response = $facebookRequest->execute();
                    $graphObject = $response->getGraphObject();
                    
                    $this->logger->warn("Posted with id: " . $graphObject->getProperty('id'));
                    
                }else if($user->getProvider() === UserOauth::PROVIDER_TWITTER){
                    $this->logger->warn("El proveedor twitter aun no esta soportado");
                }else if($user->getProvider() === UserOauth::PROVIDER_GOOGLE){
                    $this->logger->warn("El proveedor google aun no esta soportado");
                }
            }
            
        } catch(FacebookRequestException $ex) {
            $this->logger->warn("Ocurrio una exception publicando en el muro de facebook. CODE: ".$ex->getCode() . ". MENSSAGE: " . $ex->getMessage());
        } catch(\Exception $ex) {
            $this->logger->warn("Ocurrio una exception publicando en el muro. CODE: ".$ex->getCode() . ". MENSSAGE: " . $ex->getMessage());
        }
   }
   
   protected function getGalleryRootDir()
   {
        return __DIR__.'/../../../../web/images/gallery/';
   }
}

?>
