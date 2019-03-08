<?php

class FacturaReportExtension extends \ReportExtension{
    
    public $container = null;
    public $reportFileName = "factura";
    public $alias = "factura";
    public $enabled = true;
    public function getParam(){
        $parameters = new java ('java.util.HashMap');
        if($this->container !== null){
            $user = $this->container->get('security.context')->getToken()->getUser();
            $now = new DateTime();
            $parameters->put('FECHA_DIA', $now->format('d/m/Y H:i:s'));
            $parameters->put('USUARIO_ID', intval($user->getId()));
            $parameters->put('USUARIO_NOMBRE', $user->getUsername());
            $nombreEmpresaReporte = $this->container->getParameter("nombre_empresa_app");
            $parameters->put('EMPRESA_NOMBRE', $nombreEmpresaReporte);
            
            $request = $this->container->get("request");
            $id = $request->get('id');
            if($id === null || trim($id) === ""){
                throw new RuntimeException("m1Debe especificar el identificador de la compra.");
            }
            $parameters->put('DATA_COMPRA_ID', intval(trim($id)));
        }
        return $parameters;
    }
    public function getSqlSentence(){}
    public function getHtmlOptions(){}
    public function beforeRun(){}
    public function afterRun($outfilename){}
    public function getConexion(){}
    public function setContainer($container){
        $this->container = $container;
    }
}
