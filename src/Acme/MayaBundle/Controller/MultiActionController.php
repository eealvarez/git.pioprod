<?php

namespace Acme\MayaBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Ivory\GoogleMap\Overlays\Animation;
use Ivory\GoogleMap\Events\MouseEvent;
use Ivory\GoogleMapBundle\Entity\InfoWindow;
use Ivory\GoogleMap\Controls\ControlPosition;
use Ivory\GoogleMap\Controls\ZoomControlStyle;
use Acme\BackendBundle\Services\UtilService;
use Acme\BackendBundle\Entity\UserOauth;

/**
*   @Route(path="/ajax")
*/
class MultiActionController extends Controller {
    
    /**
     * @Route(path="/listarOficinas.json", name="ajaxListarOficinas")
    */
    public function listarOficinasAction(Request $request) {
        $optionEstaciones = array();
        $mapDepartamentoEstacion = array();
        $estacionesActivas = $this->getDoctrine()->getRepository('MayaBundle:Estacion')->getEstacionesActivasByDepartamento(); 
        foreach ($estacionesActivas as $estacion) {
            $departamento = $estacion->getDepartamento();
            $nombreDepartamento = ($departamento !== null) ? $departamento->getNombre() : "";
            if(!isset($mapDepartamentoEstacion[$nombreDepartamento])){
                $mapDepartamentoEstacion[$nombreDepartamento] = array();
            }
            $mapDepartamentoEstacion[$nombreDepartamento][] = $estacion;
        }
        foreach ($mapDepartamentoEstacion as $nombreDepartamento => $listaEstaciones) {
            $listaTemp = array();
            foreach ($listaEstaciones as $estacion) {
                $listaTemp[] = array(
                    "id" => $estacion->getId(),
                    "text" => $estacion->getNombre(),
                );
            }
            $optionEstaciones[] = array(
                "text" => strtoupper($nombreDepartamento),
                "children" => $listaTemp
            );
        }
        
        $response = new JsonResponse();
        $response->setData(array(
            'optionEstaciones' => $optionEstaciones,
        ));
        return $response;
    }
    
    /**
     * @Route(path="/loginUserOauth.html", name="ajaxLoginUserOauth")
    */
    public function loginUserOauthAction() {
        return $this->render("HWIOAuthBundle:Connect:login.html.twig");
    }
    
    /**
     * @Route(path="/checkUserOauthLogin.json", name="ajaxCheckUserOauthLogin")
    */
    public function checkUserOauthLoginAction() {
        $isLoginOauth = false;
        $picture =  "";
        $fullname = "";
        $codigo = "";
        $user = $this->getUser();
        if($user !== null && $user instanceof UserOauth){
            $isLoginOauth = true;
            $picture = $user->getProfilePicture();
            $fullname = $user->getRealname();
            $emp = $this->container->getParameter("id_empresa_app");
            $codigo = $user->getCodigo($emp);
        }
        $response = new JsonResponse();
        $response->setData(array(
            'login' => $isLoginOauth,
            'fullname' => $fullname,
            'picture' => $picture,
            'codigo' => $codigo
        ));
        return $response;
    }
    
    /**
     * @Route(path="/getInfoUserOauth.html", name="ajaxGetInfoUserOauth")
    */
    public function getInfoUserOauthAction() {        
        return $this->render("MayaBundle::infoUser.html.twig");
    }
    
    /**
     * @Route(path="/getClausulasEncomiendas.html", name="ajaxClausulasEncomiendas")
    */
    public function getClausulasEncomiendasAction() {        
        return $this->render("MayaBundle::clausualasEncomienda.html.twig");
    }
    
    /**
     * @Route(path="/cr.json", name="ajaxReservar")
    */
    public function crearReservacionAction(Request $request) {
        
        $data = $request->query->get('data');
        if (is_null($data)) {
            $data = $request->request->get('data');
        }
        if($data !== null && trim($data) !== ""){
            $idApp = $this->container->getParameter("id_empresa_app");
            $now = new \DateTime();
            $dataWeb = $now->format('Y-m-d'). "_system_web_".$idApp;
            $claveInterna = $this->container->getParameter("internal_sys_clave_interna");
            $tokenAutLocal = UtilService::encrypt($claveInterna, $dataWeb);
            $data = array( "idWeb" => $idApp, "tokenAut" => $tokenAutLocal, "data" => $data );
                $postdata = http_build_query($data);
                $options = array(
                      'http' => array(
                      'method'  => 'POST',
                      'content' => $postdata,
                      'header'  => "Content-type: application/x-www-form-urlencoded\r\n"));
                $context  = stream_context_create( $options );
                $url =  $this->container->getParameter("internal_sys_url") .
                        $this->container->getParameter("internal_sys_pref") .
                        "cr.json";
                $result = file_get_contents($url, false, $context );
                return new Response($result);
        }
        
        $response = new JsonResponse();
        $response->setData(array(
            "status" => "error",
            "message" => "Faltan parametros"
        ));
        return $response;
    }
    
    /**
     * @Route(path="/getInfoEncomiendaByCliente.html", name="ajaxGetInfoEncomiendaByCliente")
    */
    public function getInfoEncomiendaByClienteAction(Request $request) {
        
        $user = $this->getUser();
        
        if ($user && $user !== null && $user instanceof UserOauth) {
            
            $emp = $this->container->getParameter("id_empresa_app");
            $codigo = $user->getCodigo($emp);
            
            $idApp = $this->container->getParameter("id_empresa_app");
            $now = new \DateTime();
            $dataWeb = $now->format('Y-m-d'). "_system_web_".$idApp;
            $claveInterna = $this->container->getParameter("internal_sys_clave_interna");
            $tokenAutLocal = UtilService::encrypt($claveInterna, $dataWeb);

            $data = array( "idWeb" => $idApp, "tokenAut" => $tokenAutLocal, "data" => $codigo );

            $postdata = http_build_query($data);
            $options = array(
                  'http' => array(
                  'method'  => 'POST',
                  'content' => $postdata,
                  'header'  => "Content-type: application/x-www-form-urlencoded\r\n"));
            $context  = stream_context_create( $options );
            $url =  $this->container->getParameter("internal_sys_url") .
                    $this->container->getParameter("internal_sys_pref") .
                    "ieu.json";
            $result = file_get_contents($url, false, $context );
            
            $this->get("acme_redes_sociales")->publicarMuro($user);
            
            return new Response($result);
        }
        
        $response = new JsonResponse();
        $response->setData(array());
        return $response;
    }
    
    /**
     * @Route(path="/getInfoEncomienda.html", name="ajaxGetInfoEncomienda")
    */
    public function getInfoEncomiendaAction(Request $request) {
        
        $idEncomienda = $request->query->get('id');
        if (is_null($idEncomienda)) {
            $idEncomienda = $request->request->get('id');
        }
        
        if (!is_null($idEncomienda) && trim($idEncomienda) !== "") {
            
            $idEncomienda = intval($idEncomienda);
            if($idEncomienda >= 1){
                $idApp = $this->container->getParameter("id_empresa_app");
                $now = new \DateTime();
                $dataWeb = $now->format('Y-m-d'). "_system_web_".$idApp;
                $claveInterna = $this->container->getParameter("internal_sys_clave_interna");
                $tokenAutLocal = UtilService::encrypt($claveInterna, $dataWeb);

                $data = array( "idWeb" => $idApp, "tokenAut" => $tokenAutLocal, "data" => $idEncomienda );

                $postdata = http_build_query($data);
                $options = array(
                      'http' => array(
                      'method'  => 'POST',
                      'content' => $postdata,
                      'header'  => "Content-type: application/x-www-form-urlencoded\r\n"));
                $context  = stream_context_create( $options );
                $url =  $this->container->getParameter("internal_sys_url") .
                        $this->container->getParameter("internal_sys_pref") .
                        "ie.json";
                $result = file_get_contents($url, false, $context );
                return new Response($result);
            }
        }
        
        $response = new JsonResponse();
        $dataOut = array();
        $dataOut[] = "El número de guía '<i>".$idEncomienda."</i>' es incorrecto.";
        $response->setData(array(
            "data" => $dataOut
        ));
        return $response;
    }
    
    /**
     * @Route(path="/getInfoEstacion.html", name="ajaxGetInfoEstacion")
    */
    public function getInfoEstacionAction(Request $request) {
        
        $idEstacion = $request->query->get('id');
        if (is_null($idEstacion)) {
            $idEstacion = $request->request->get('id');
        }
        
        if(!is_null($idEstacion) && trim($idEstacion) !== ""){
            $estacion = $this->getDoctrine()->getRepository('MayaBundle:Estacion')->find($idEstacion);
            if($estacion !== null){                
                $map = null;
                if($estacion->getTieneMap()){
                    $longitude = $estacion->getLongitude();
                    $latitude = $estacion->getLatitude();
                    $map = $this->get('ivory_google_map.map');
                    $infoWindow = new InfoWindow();
                    $infoWindow->setPrefixJavascriptVariable('info_window_');
                    $infoWindow->setPosition(0, 0, true);
                    $infoWindow->setPixelOffset(1.1, 2.1, 'px', 'pt');
                    $descripcion = "<address>".
                                   "<span class='glyphicon glyphicon-tag'></span>&nbsp;<strong>".$estacion->getNombre()." </strong> ( ".$estacion->getAlias()." )<br>".
                                   "<span class='glyphicon glyphicon-globe'></span>&nbsp;".$estacion->getDireccion()." <br>".
                                   ( trim($estacion->getListaTelefonos()) !== "" ? "<span class='glyphicon glyphicon-phone-alt'></span>&nbsp;".trim($estacion->getListaTelefonos()) : "" ) .
                                   "</address>";                   
                    
                    $infoWindow->setContent($descripcion);
                    $infoWindow->setOpen(false);
                    $infoWindow->setAutoOpen(true);
                    $infoWindow->setOpenEvent(MouseEvent::MOUSEOVER);
                    $infoWindow->setAutoClose(false);
                    $infoWindow->setOption('disableAutoPan', true);
                    $infoWindow->setOption('zIndex', 10);
                    $infoWindow->setOptions(array(
                        'disableAutoPan' => true,
                        'zIndex'         => 10,
                    ));

                    $marker = $this->get('ivory_google_map.marker');
                    $marker->setPosition($longitude, $latitude, true);
                    $marker->setAnimation(Animation::BOUNCE);
                    $marker->setInfoWindow($infoWindow);
                    $map->addMarker($marker);

                    $map->setZoomControl(ControlPosition::TOP_LEFT, ZoomControlStyle::DEFAULT_);
                    $map->setCenter($longitude, $latitude, true);
                }
                
                return $this->render("MayaBundle:Map:mapa.html.twig" , array(
                    "estacion" => $estacion,
                    "map" => $map
                ));
            }
        }
        
        return new Response("Detalles no disponibles.");
    }
    
    /**
     * @Route(path="/getHistoria.html", name="ajaxGetHistoria")
    */
    public function getHistoriaAction() {
        $idEmpresa = $this->container->getParameter("id_empresa_app");
        return $this->render("MayaBundle::historia_".$idEmpresa.".html.twig");
    }
    
     /**
     * @Route(path="/getDataImagen.jpg", name="ajaxDataImagen")
    */
    public function getDataImagenAction(Request $request) {
        
        $idImagen = $request->query->get('id');
        if (is_null($idImagen)) {
            $idImagen = $request->request->get('id');
        } 
        
        $full = $request->query->get('full');
        if (is_null($full)) {
            $full = $request->request->get('full');
            if (is_null($full)) {
                $full = false;
            }
        }
        
        $imagen = $this->getDoctrine()->getRepository('MayaBundle:Imagen')->find($idImagen);
        if($imagen === null){
            return UtilService::returnError($this, "La imagen con identificador " . $idImagen . " no existe");
        }
        if($full === 'true' ||$full === 1 || $full === '1'){
           $full = true; 
        }else{
           $full = false; 
        }
        
        $pathFile = $this->getGalleryRootDir() . 'image_' . $imagen->getId() . '_' . ($full === true ? 'max' : 'min') . "." . $imagen->getFormato();
        if(!file_exists($pathFile)){
            $ifp = fopen($pathFile, "wb");
            if($full){
                fwrite($ifp, base64_decode($imagen->getImagenNormal())); 
            }else{
                fwrite($ifp, base64_decode($imagen->getImagenPequena())); 
            }
            fclose($ifp);
        }
        
        $response = new \Symfony\Component\HttpFoundation\StreamedResponse();
        $response->headers->set('Content-Type', 'image/jpeg');
        $response->setCallback(function () use ($pathFile) {
            $bytes = @readfile($pathFile);
            if ($bytes === false || $bytes <= 0)
                throw new NotFoundHttpException();
        });
        $response->setMaxAge(43200);
        return $response;
    }
    
    protected function getGalleryRootDir()
    {
        return __DIR__.'/../../../../web/images/gallery/';
    }
}

?>
