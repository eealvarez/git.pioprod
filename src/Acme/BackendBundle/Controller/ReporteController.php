<?php

namespace Acme\BackendBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Acme\BackendBundle\Services\UtilService;
use Acme\BackendBundle\Form\Model\ReporteModel;

/**
*   @Route(path="/admin/reportes")
*/
class ReporteController extends Controller {
    
    private $pathComponente;
    private $pathResportes;
    private $reportManager;
    
    function __construct() {
        
        define('JAVA_INC_URL','http://localhost:8080/PHPJRU/java/Java.inc');
        define('PHP_JRU_VERSION','1.0');
        if( ! function_exists('java')){
            if( ini_get("allow_url_include"))
                require_once(JAVA_INC_URL);
            else
                die ('necesita habilitar allow_url_include en php.ini para poder usar php-jru.');
        }
        
        define('PJRU_PDF','pdf');
        define('PJRU_OPEN_DOCUMENT','odt');
        define('PJRU_EXCEL','xls');
        define('PJRU_HTML','html');
        define('PJRU_RICH_TEXT','rtf');
        define('PJRU_TXT','txt');
        define('PJRU_DOCX','docx');
        define('PJRU_PPTX','pptx');
        define('PJRU_XML','xml');
        
        $this->pathComponente = $this->getPathComponente();
        $this->pathResportes = $this->getPathReportes();
        
        require_once $this->pathComponente . 'JdbcConnection.php';
        require_once($this->pathComponente. 'PJRU.php');
        require_once $this->pathComponente. 'JdbcAdapters\JdbcAdapterInterface.php';
        require_once $this->pathComponente. 'PJRUConexion.php';
        require_once($this->pathComponente. 'ReportManager\ReportManager.php');
        
        $reportManager = new \ReportManager();
        $this->reportManager = $reportManager;
        $reportManager->extensionFolder = $this->pathResportes; 
    }
    
    private function chechDefaultConnection() {
        $connetion = $this->reportManager->getConnetionDefalt();
        if($connetion === null){
            $container = $this->container;
            $type = $container->getParameter('database_type');
            $host = $container->getParameter('database_host');
            $port = $container->getParameter('database_port');
            $user = $container->getParameter('database_user');
            $pass = $container->getParameter('database_password');
            $database = $container->getParameter('database_name');
            $connetion = \PJRUConexion::get($type,$host,$port,$database,$user,$pass);
            $this->reportManager->setConnetionDefalt($connetion); 
        }
    }
    
    private function getPathComponente() {
        $clase = new \ReflectionClass("Acme\BackendBundle\PHPJRU\PHPJRU");
        $fileName = $clase->getFileName();
        $basePath = str_replace("PHPJRU.php", "", $fileName);
        return $basePath;
    }
    
    private function getPathReportes() {
        $clase = new \ReflectionClass("Acme\BackendBundle\Reportes\Reportes");
        $fileName = $clase->getFileName();
        $basePath = str_replace("\Reportes.php", "", $fileName);
        return $basePath;
    }
    
    protected function getRootDir()
    {
        return __DIR__.'\\..\\..\\..\\..\\web\\';
    }
    
    /*
        $type: PJRU_PDF, PJRU_EXCEL, PJRU_HTML, PJRU_RICH_TEXT
    */
    private function generarReporte($name, $request) {
        try {
            
            $type = $request->query->get('type');
            if (is_null($type)) {
                $type = $request->request->get('type');
            }
            if (is_null($type)) {
               $type = PJRU_PDF;
            }else if($type === "PDF"){
               $type = PJRU_PDF;
            }else if($type === "DOCX"){
               $type = PJRU_DOCX;
            }else if($type === "EXCEL"){
               $type = PJRU_EXCEL;
            }else if($type === "HTML"){
               $type = PJRU_HTML;
            }else if($type === "RTF"){
               $type = PJRU_RICH_TEXT;
            }else if($type === "TXT"){
               $type = PJRU_TXT;
            }else if($type === "XML"){
               $type = PJRU_XML;
            }else if($type === "PPTX"){
               $type = PJRU_PPTX;
            }

            $this->chechDefaultConnection();        
            $pathFile = $this->reportManager->RunToFile($name, $type, $this->container);
            $nameFile = "";
            if(substr_count($pathFile, "\\") != 0)
            {
                $lastSeparator = strrpos($pathFile, "\\"); 
                $nameFile = substr($pathFile, $lastSeparator+1);    
            }
            if(file_exists($pathFile)){
                $newPathFile = $this->getRootDir() . "reporte\\" . $nameFile;
                if(copy($pathFile, $newPathFile)){
                    unlink($pathFile);
                    return $this->render('BackendBundle:Commun:assetPath.html.twig', array(
                        'path' => "reporte/".$nameFile
                    ));
                }else{
                    throw new \RuntimeException("No se pudo copiar el reporte...");
                }
            }else{
                throw new \RuntimeException("No se pudo generar el reporte...");
            }
            
        }catch (\ErrorException $ex) {
            var_dump($ex->getMessage());
            return UtilService::returnError($this, "m1Ha ocurrido un error generando el reporte.");
        }catch (\RuntimeException $ex) {
            var_dump($ex->getMessage());
            $mensaje = $ex->getMessage();
            if(UtilService::startsWith($mensaje, 'm1')){
                $mensajeServidor = $mensaje;
            }else{
                $mensajeServidor = "m1Ha ocurrido un error generando el reporte.";
            }
            return $this->render('BackendBundle:Commun:respuestaServidor.html.twig', array(
                'mensajeServidor' => $mensajeServidor
            ));
        }catch (\Exception $ex) {
            var_dump($ex->getMessage());
            return UtilService::returnError($this, "m1Ha ocurrido un error generando el reporte.");
        }
    }
    
    /**
     * @Route(path="/reporteFactura.html", name="reporte-factura")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function reporteExistenciasActivosCirculantesPorLote(Request $request, $_route) {
        return $this->generarReporte("factura", $request);
    }
}

?>
