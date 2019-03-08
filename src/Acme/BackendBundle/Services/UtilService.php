<?php

namespace Acme\BackendBundle\Services;

use Symfony\Component\HttpFoundation\Response;

class UtilService {
    
   protected $container;
   
    public function __construct($container) { 
         $this->container = $container;
    }
    
    public function generateUniqueId(){
        return $this->generateInternal();
    }
    
    public function generatePin($prefijo = null){
        if($prefijo === null || trim($prefijo) === ""){
            return $this->generateInternal(4);
        }
        else{
            return $prefijo . "-" . $this->generateInternal(4);
        }
    }

    public static function getEANCheckDigit($barcode){
        $barcode = str_pad($barcode, 12, "0", STR_PAD_LEFT);
        $sum = 0;
        for($i=(strlen($barcode)-1);$i>=0;$i--){
            $sum += (($i % 2) * 2 + 1 ) * $barcode[$i];
        }
        $result = (10 - ($sum % 10));
        return $result < 10 ? $result : 0;
    }
        
    public static function convertInToHex($value) { 
        if(!$value){
            return 0;   
        }
        return dechex($value * 6);
    }
    
    private function generateInternal($size){
        $fecha = date("Ymdhis");        
        $username = $this->container->get('security.context')->getToken()->getUser()->getUsername();
        $random = uniqid(rand(), true);
        if($size !== null){
            return strtoupper(substr(UtilService::process(crypt($fecha)),0, $size) . "-" . substr(UtilService::process(crypt($username)),0, $size) . "-" . substr(UtilService::process(crypt($random)),0, $size) ); 
        }else{
            return strtoupper(UtilService::process(crypt($fecha)) . "-" . UtilService::process(crypt($username)) . "-" . UtilService::process(crypt($random)));            
        }
    }
    
    public static function generateSimpleNumericPin($size = 4){
        $str = "";
        for ($index = 0; $index < $size; $index++) {
          $str .= strval(rand(0, 9));
        }
        return $str;
    }
    
    private static function process($rnd_id){
        $rnd_id = strip_tags(stripslashes($rnd_id)); 
        $rnd_id = str_replace(".","",$rnd_id); 
        $rnd_id = strrev(str_replace("/","",$rnd_id));
        return $rnd_id;
    }
    
    public static function diffHours($fecha1, $fecha2){
        if(is_null($fecha1) || is_null($fecha2)){
            return 0;
        }
        $diff = $fecha1->diff($fecha2);
        $hours = $diff->h;
        $hours = $hours + ($diff->days*24);
        return $hours;
    }
            
    public static function compararFechas($primera, $segunda)
    {
//        var_dump($primera);
//        var_dump($segunda);
        if(!is_string($primera)){
            $primera = $primera->format('d-m-Y');
        }
        
        if(!is_string($segunda)){
            $segunda = $segunda->format('d-m-Y');
        }
        
        $valoresPrimera = explode ("-", $primera);  
        $valoresSegunda = explode ("-", $segunda); 
        
        $diaPrimera    = intval($valoresPrimera[0]);  
        $mesPrimera  =   intval($valoresPrimera[1]);  
        $anyoPrimera   = intval($valoresPrimera[2]); 

        $diaSegunda   = intval($valoresSegunda[0]);  
        $mesSegunda = intval($valoresSegunda[1]);  
        $anyoSegunda  = intval($valoresSegunda[2]);

        $diasPrimeraJuliano = gregoriantojd($mesPrimera, $diaPrimera, $anyoPrimera);  
        $diasSegundaJuliano = gregoriantojd($mesSegunda, $diaSegunda, $anyoSegunda);     

        if(!checkdate($mesPrimera, $diaPrimera, $anyoPrimera)){
             throw new \RuntimeException("Fecha no válida: " . $primera);
        }elseif(!checkdate($mesSegunda, $diaSegunda, $anyoSegunda)){
           throw new \RuntimeException("Fecha no válida: " . $segunda);
        }else{
          return  $diasPrimeraJuliano - $diasSegundaJuliano;
        } 
   }
   
   public static function getMapsParametrosQuery($query = null)
   {
       if($query === null){
           $query = $this->get('request')->request->get('query');
       }
       $mapFilters = array();
       $filters = explode('&', $query);
       foreach ($filters as $filter) {    
           $param = explode("=", $filter);
           if($param && count($param) === 2) {
              $key = urldecode($param[0]);
              $value = urldecode($param[1]);
              $mapFilters[$key] = $value;
           }
       }
       return $mapFilters;
   }
   
    public static function setParameterToQuery($query, $name, $value = null, $isLike = true)
    {
        if($value !== null){
            if(is_string($value) && trim($value) === ""){
                return $query;
            }
            if($isLike){
                $query->setParameter($name, "%".$value."%");
            }else{
                $query->setParameter($name, $value);
            }
        }
        return $query;
    }
    
    public static function setParameterToQuerySTR($querySTR, $alias ,$nameFilter, $value = null, $isLike = true)
    {
        if($value !== null){
            if(is_string($value) && trim($value) === ""){
                return $querySTR;
            }
            
            if(UtilService::contains($querySTR, "where")){
                $querySTR .= " and ";
            }else{
                $querySTR .= " where ";
            }
            
            if(is_array($alias)){
                $querySTR .= " ( ";
                for ($index = 0; $index < count($alias); $index++) {
                    if($index !== 0){
                        $querySTR .= " OR ";
                    }
                     if($isLike){
                         $querySTR .= " (".$alias[$index]." like :".$nameFilter.") ";
                     }else{
                         $querySTR .= " (".$alias[$index]." = :".$nameFilter.") ";
                     }
                }
                $querySTR .= " ) ";
            }else{
                if($isLike){
                    $querySTR .= " (".$alias." like :".$nameFilter.") ";
                }else{
                    $querySTR .= " (".$alias." = :".$nameFilter.") ";
                }
            }
        }
        return $querySTR;
    }
    
    public static function getValueToMap($mapFilters, $name, $defaul = null)
    {
        if(array_key_exists($name, $mapFilters) && $mapFilters[$name] !== null && trim($mapFilters[$name]) !== ""){
           return $mapFilters[$name];
        }else{
            if($defaul !== null){
                return $defaul;
            }
            return null;
        }
    }
    
    public static function getErrorsToForm($form)
    {
        foreach ($form->getErrors() as $error) {
            return $error->getMessage();
        }

        foreach ($form->all() as $key => $child) {
            $error = self::getErrorsToForm($child);
            if($error !== ""){
                return $error;
            }
        }
        
        return "";
    }
    
    public static function startsWith($haystack, $needle)
    {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }
    
    public static function endsWith($haystack, $needle)
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }
    
    public static function contains($haystack, $needle)
    {
        return stripos($haystack, $needle) !== false;
    }
    
    public static function checkExistImpresora($impresorasDisponibles, $pathImpresora)
    {
        if(strpos($impresorasDisponibles, $pathImpresora) === false){
            if(substr_count($pathImpresora, "\\") >= 3){  //existe un direccion de ip.
                $posInit = strpos($pathImpresora, "\\", 3) + 1;
                $nameImpresora = substr($pathImpresora, $posInit , 100);
                if(strpos($impresorasDisponibles, $nameImpresora) === false){
                    return false;
                }else{
                    return true;
                }
            }   
            return false;
        }
        return true;
    }
    
    /*
        Función para encriptar datos de forma reversible
        $key: Clave
        $data: Datos a encriptar
        $algorithm: Algoritmo a usar
        $mode: El cuarto argumento indica el modo de encriptado o desencriptado
        $iv = Vector de inicializacion.
    */
    public static function encrypt($key, $data, $algorithm = MCRYPT_RIJNDAEL_256, $mode = MCRYPT_MODE_CBC)
    {
        $encrypted_data = mcrypt_encrypt($algorithm, md5($key), $data, $mode, md5(md5($key)));
//        $iv = mcrypt_create_iv(mcrypt_get_iv_size($algorithm, $mode), MCRYPT_DEV_URANDOM);
//        $encrypted_data = mcrypt_encrypt($algorithm, $key, $data, $mode, $iv);
        $plain_text = base64_encode($encrypted_data);
        return $plain_text;
    }
    
    /*
        Función para deencriptar datos de forma reversible
    */
    public static function decrypt($key, $plain_text, $algorithm = MCRYPT_RIJNDAEL_256, $mode = MCRYPT_MODE_CBC)
    {
        $encrypted_data = base64_decode($plain_text);
        $decoded = rtrim(mcrypt_decrypt($algorithm, md5($key), $encrypted_data, $mode, md5(md5($key))), "\0");
//        $iv = mcrypt_create_iv(mcrypt_get_iv_size($algorithm, $mode), MCRYPT_DEV_URANDOM);
//        $decoded = mcrypt_decrypt($algorithm, $key, $encrypted_data, $mode, $iv);
        return $decoded;
    }
    
    public static function generarBitChequeo($data){
        if(!$data){ return ""; }
        $data = trim($data);
        if($data === ""){ return ""; }
        $bits = UtilService::calcularBitChequeo($data);
        return $data . "-" . $bits;
    }
    
    public static function calcularBitChequeo($data){
        if(!$data){ return ""; }
        $data = trim($data);
        if($data === ""){ return ""; }
        $data = strrev($data); //Invierte cadena
        $values = "";
        for ($index = 0; $index < strlen($data); $index++ ) {
            $values .= strval(ord(substr($data, $index, 1)));
        }        
        $pivote = 2;
        $longitudCadena = strlen($values);
        $cantidadTotal = 0;
        $b = 1;
        for ($index = 0; $index < $longitudCadena; $index++ ) {
            if ($pivote == $longitudCadena - 1) {
                $pivote = 2;
            }
            $temporal = intval(substr($values, $index, 1));
            $b++;
            $temporal *= $pivote;
            $pivote++;
            $cantidadTotal += $temporal;
        }
        $cantidadTotal = 11 - $cantidadTotal % 11;
        return $cantidadTotal;
    }
    
    public static function checkBitChequeo($data){
        if(!$data){ return false; }
        $data = trim($data);
        if($data === ""){ return false; }
        $values = explode("-", $data);
        if(count($values) !== 2){
            return false;
        }else{
            $bist = UtilService::calcularBitChequeo($values[0]);
            if(intval($bist) === intval($values[1])){
                return true;
            }else{
                return false;
            }
        }
    }
    
    public static function removeBitChequeo($data){
        if(!$data){ return ""; }
        $data = trim($data);
        if($data === ""){ return ""; }
        $values = explode("-", $data);
        if(count($values) !== 2){
            return "";
        }else{
            return $values[0];
        }
    }
    
    public static function isValidIpRequestOfUser(array $networks, $ip){
        if(count($networks) === 0){
            return true;
        }else{
            foreach ($networks as $network) {
//                var_dump("chequeando ip:" . $ip . ", en la red:" . $network);
               if(UtilService::netMatch($network, $ip) === true){
                   return true;
               }
            }
            return false;
        }
    }
    
    public  static function netMatch($network, $ip) {
        try {
            $network = trim($network);
            $orig_network = $network;
            $ip = trim($ip);
            if ($ip == $network) {
                return true;
            }
            $network = str_replace(' ', '', $network);
            if (strpos($network, '*') !== false) {
                if (strpos($network, '/') !== false) {
                    $asParts = explode('/', $network);
                    $network = @ $asParts[0];
                }
                $nCount = substr_count($network, '*');
                $network = str_replace('*', '0', $network);
                if ($nCount == 1) {
                    $network .= '/24';
                } else if ($nCount == 2) {
                    $network .= '/16';
                } else if ($nCount == 3) {
                    $network .= '/8';
                } else if ($nCount > 3) {
                    return true; // if *.*.*.*, then all, so matched
                }
            }

            $d = strpos($network, '-');
            if ($d === false) {
                $ip_arr = explode('/', $network);
                if (!preg_match("@\d*\.\d*\.\d*\.\d*@", $ip_arr[0], $matches)){
                    $ip_arr[0].=".0";    // Alternate form 194.1.4/24
                }
                $network_long = ip2long($ip_arr[0]);
                $x = ip2long($ip_arr[1]);
                $mask = long2ip($x) == $ip_arr[1] ? $x : (0xffffffff << (32 - $ip_arr[1]));
                $ip_long = ip2long($ip);
                return ($ip_long & $mask) == ($network_long & $mask);
            } else {
                $from = trim(ip2long(substr($network, 0, $d)));
                $to = trim(ip2long(substr($network, $d+1)));
                $ip = ip2long($ip);
                return ($ip>=$from and $ip<=$to);
            }
        } catch (\ErrorException $ex) {
            return false;
        } catch (\Exception $ex) {
            return false;
        }
    }
    
    public static function getQueryOrder($order, $sort, $mapFieldToColumnsSorted) {
        
        $queryOrder = "";
        if($order !== null && trim($order) !== "" && $sort !== null && trim($sort) !== ""){
            $sortArray = explode(",", $sort);
            $orderArray = explode(",", $order);
            if(count($orderArray) === count($sortArray)){
                for ($index = 0; $index < count($sortArray); $index++) {
                    if(!array_key_exists(trim($sortArray[$index]), $mapFieldToColumnsSorted)){
                        continue;
                    }else{
                        $column = $mapFieldToColumnsSorted[trim($sortArray[$index])];
                        $columnOrder = "ASC";
                        $mapKeyOrder = array(
                            '' => 'asc',
                            'asc' => 'asc',
                            'desc' => 'desc',
                        );
                        if(array_key_exists(trim($orderArray[$index]), $mapKeyOrder)){
                            $columnOrder = $mapKeyOrder[trim($orderArray[$index])];
                        }
                        $queryOrder .= ($queryOrder !== "" ? " ," : " " ). $column . " " . $columnOrder . " ";
                    }
                }
            }
        }
        return $queryOrder;
    }
    
    public static  function aplicarDescuento($importe, $cantidad) {
        $result = round(abs($importe - UtilService::calcularDescuento($importe, $cantidad)), 0, PHP_ROUND_HALF_UP);
        return $result;
    }
    
    public static  function calcularDescuento($importe, $cantidad) {
        if($cantidad < 3){
            return 0;
        }else if($cantidad <= 5){      
            return round(0.05 * $importe, 0, PHP_ROUND_HALF_UP);    //5%
        }else if($cantidad <= 9){        
            return round(0.07 * $importe, 0, PHP_ROUND_HALF_UP);    //7%
        }else{        
            return round(0.10 * $importe, 0, PHP_ROUND_HALF_UP);    //10%
        }
    }
    
    public static  function roundBy($a, $b = 5) {
        return (int)($a / $b + 0.5) * $b;
    }
    
    public static  function move_item_to_top(&$array, $key) {
        if(isset($array[$key])) {
            $temp = array($key => $array[$key]);
            unset($array[$key]);
            $array = $temp + $array;
        }
    }
    
    public static  function move_item_to_bottom(&$array, $key) {
        if(isset($array[$key])) {
            $value = $array[$key];
            unset($array[$key]);
            $array[$key] = $value;
        }
    }
    
    public static  function returnSuccess($context, $options = array()) {
        $options['mensajeServidor'] = "m0";
        return $context->render('MayaBundle:Commun:respuestaServidor.html.twig', $options);
    }
    
    public static  function returnError($context, $textError = null) {
       if($textError === null || trim($textError) === ""){
           $textError = "m1Ha ocurrido un error en el sistema";
       }
       if(!UtilService::startsWith($textError, "m1")){
            $textError = "m1" . $textError;
       }
       return $context->render('MayaBundle:Commun:respuestaServidor.html.twig', array(
            'mensajeServidor' => $textError
       ));
    }
    
//  Solo esta diseñado para el uso desde los comandos.
    public static function sendEmail($context, $subject, $to, $body, $attach = array(), $toHidden= array(), $intentos = 3) {
       $logger = $context->get('logger');
       $mailer = $context->get('mailer');
       try {
            $logger->warn("Intento Nro: " . strval(abs(4 - $intentos)) . ". Asunto: " . $subject . ".");
           
            if($intentos === 0){
                $error = "No se puedo enviar el correo, superado la cantida de intentos permitidos";
                $logger->error($error);
                throw new \RuntimeException($error);
            }
            
            sleep(10); //Para no sobrecargar el servidor de correo.
            
            $mailer->getTransport()->stop();
            if(!$mailer->getTransport()->isStarted()){
                 $mailer->getTransport()->start();
            }
            
            $message = $mailer->createMessage()
                      ->setSubject($subject)
                      ->setFrom($context->getParameter("mailer_user"))
                      ->setTo($to)
                      ->setBody($body);
           
            if(count($toHidden) !== 0){
                $message->setCc($toHidden);
            }
                   
            foreach ($attach as $value) {
                if($value !== null && trim($value) !== ""){
                    $message->attach(\Swift_Attachment::fromPath(trim($value)));
                }
            }
           
            $result = $mailer->send($message);
            $mailer->getTransport()->stop();
            var_dump($result);
            $logger->warn("Result:" . $result .".");
            return $result;
           
       } catch (\RuntimeException $ex) {
           $logger->error($ex->getMessage());
           $intentos--;
           UtilService::sendEmail($context, $subject, $to, $body, $attach, $toHidden, $intentos);
       } catch (\ErrorException $ex) {
           $logger->error($ex->getMessage());
           $intentos--;
           UtilService::sendEmail($context, $subject, $to, $body, $attach, $toHidden, $intentos);
       }catch (\Exception $ex) {
           $logger->error($ex->getMessage());
           $intentos--;
           UtilService::sendEmail($context, $subject, $to, $body, $attach, $toHidden, $intentos);
       }        
    }
    
    public static  function chechModifiedResponse($context, $request) {
        $response = new Response();
//        $value = md5($context->getUser()->getUsername());
        $version = $context->get('service_container')->getParameter("version");
        $value = intval($context->getUser()->getId()) + intval($version);
        $response->setETag($value);
        if ($response->isNotModified($request)) {
            return $response;
        }else{
            return null;
        }
    }
    
    public static  function setTagResponse($context, $response, $private = true) {
        if($private === true){
            $response->setPrivate();
        }
//        $value = md5($context->getUser()->getUsername());
        $version = $context->get('service_container')->getParameter("version");
        $value = intval($context->getUser()->getId()) + intval($version);
        $response->setETag($value);
        return $response;
    }
    
    public static function resize($file_path, $options = array())
    {   
        $defaultOptions = array(
            'max_width' => 400,
            'max_height' => 400,
            'scaled' => false,
            'jpeg_quality' => 100,
            'png_quality' => 6,
        );
        
        $options = array_merge($defaultOptions, $options);
        
        list($img_width, $img_height) = @getimagesize($file_path);
        if (!$img_width || !$img_height) {
            return false;
        }
        $width = isset($options['max_width'])?$options['max_width']:$img_width;
        $height = isset($options['max_height'])?$options['max_height']:$img_height;
        $scale = min(
            $width / $img_width,
            $height / $img_height
        );

        $scaled_width = $img_width * $scale;
        $scaled_height = $img_height * $scale;

        if($options['scaled'] == false) {
            $width = $scaled_width;
            $height = $scaled_height;
        }

        $new_img = @imagecreatetruecolor($width, $height);
        $colorTransparent = imagecolorallocatealpha($new_img, 255, 255, 255, 0);
        imagefill($new_img, 0, 0, $colorTransparent);
        switch (strtolower(substr(strrchr($file_path, '.'), 1))) {
            case 'jpg':
            case 'jpeg':
                $src_img = @imagecreatefromjpeg($file_path);
                $write_image = 'imagejpeg';
                $image_quality = isset($options['jpeg_quality']) ? $options['jpeg_quality'] : 90;
                break;
            case 'gif':
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                $src_img = @imagecreatefromgif($file_path);
                $write_image = 'imagegif';
                $image_quality = null;
                break;
            case 'png':
                @imagealphablending($new_img, false);
                @imagesavealpha($new_img, true);
                $src_img = @imagecreatefrompng($file_path);
                $write_image = 'imagepng';
                $image_quality = isset($options['png_quality']) ? $options['png_quality'] : 6;
                break;
            default:
                $src_img = null;
        }

        $dst_x = 0; $dst_y = 0;
        if($options['scaled'] == true) {
        	$dst_x = abs($scaled_width-$width)/2;
        	$dst_y = abs($scaled_height-$height)/2;
        }
        $success = $src_img && @imagecopyresampled(
            $new_img,
            $src_img,
            $dst_x, 
            $dst_y, 
            0, 0,
            $scaled_width,
            $scaled_height,
            $img_width,
            $img_height
        ) && $write_image($new_img, $file_path, $image_quality);

        @imagedestroy($src_img);
        @imagedestroy($new_img);
        return $success;        
    }
    public static function getDateString ($date){
        $dayname = date("D", $date);
        $monthname = date("n", $date);
        $dia = date("j", $date);
        $year = date("Y", $date);
        $day_week = "Domingo";
        $month = "Diciembre";
        switch ($dayname)
            {
                case "mon":  $day_week = "Lunes"; break;
                case "tue" : $day_week = "Martes"; break;
                case "wed" : $day_week = "Miercoles"; break;
                case "thu" : $day_week = "Jueves"; break;
                case "fri" : $day_week = "Viernes"; break;
                case "sat" : $day_week = "Sabado"; break;
            } 
        switch ($monthname)
            {
                case  1: $month = "enero"; break;
                case  2: $month = "febrero"; break;
                case  3: $month = "marzo"; break;
                case  4: $month = "abril"; break;
                case  5: $month = "mayo"; break;
                case  6: $month = "junio"; break;
                case  7: $month = "julio"; break;
                case  8: $month = "agosto"; break;
                case  9: $month = "septiembre"; break;
                case 10: $month = "octubre"; break;
                case 11: $month = "noviembre"; break;
            } 
        
            return $day_week.", ".$dia." de ".$month." de ".$year;
        
    }
    public static function calcularTarifa($tarifa) {      
        return $tarifa + UtilService::recargo($tarifa);
    }
    public static function recargo($tarifa){
        if($tarifa < 100 ){
            return 3;
        }else if($tarifa < 200){
            return 5;
        }else if($tarifa < 300){
            return 10;
        }else{
            return 15;
        }
    }
}

?>
