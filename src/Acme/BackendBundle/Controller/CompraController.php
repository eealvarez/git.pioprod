<?php

namespace Acme\BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Acme\BackendBundle\Services\UtilService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Acme\BackendBundle\Form\Type\Compra\FacturarCompraType;
use Acme\MayaBundle\Entity\EstadoCompra;
use Acme\MayaBundle\Entity\EstadoFactura;
use Acme\BackendBundle\Form\Type\Compra\EnviarFacturaType;
use Acme\BackendBundle\Form\Type\Compra\RecibirFacturaType;
use Acme\BackendBundle\Form\Type\Compra\EntregarFacturaType;

/**
* @Route(path="/admin/compra")
*/
class CompraController extends Controller{
    
    /**
     * @Route(path="/listarCompras.html", name="admin-compra-listar")
    */
    public function listarComprasPaginadasAction(Request $request){
        return $this->render('BackendBundle:Compra:list.html.twig');
    }
    
    /**
     * @Route(path="/listarCompras.json", name="admin-compra-listar-paginado")
    */
    public function listarComprasAction($_route) {

        $total = 0;
        $rows = array();   
        $request = $this->get('request');
        $current = $request->request->get('current');
        $rowCount = $request->request->get('rowCount');
        try {
            if($current !== null && is_numeric($current) && $rowCount !== null && is_numeric($rowCount)){
                $sortRequest = "";
                $orderRequest = "";
                $searchPhrase = $request->request->get('searchPhrase');
                $result = $this->getDoctrine()->getRepository('MayaBundle:Compra')
                        ->getComprasPaginados($current, $rowCount, $sortRequest, $orderRequest, $searchPhrase, $this->getUser());
                foreach($result['items'] as $item)
                {
                    $row = array(
                        'id' => strval($item->getId()),
                        'estacionFactura' => $item->getEstacionFactura()->getNombre(),
                        'fecha' => $item->getFecha() === null ? "" : $item->getFecha()->format('d-m-Y H:i:s'),
                        'precio' => "Q " . $item->getPrecio(),
                        'cliente' => $item->getCliente()->getNombreApellidos(),
                        'idEstadoCompra' => $item->getEstado()->getId(),
                        'estadoCompra' => $item->getEstado()->getNombre(),
                        'cantidadBoletos' => count($item->getListaIdExternoBoletos()),
                        'factura' => $item->getFactura() === null ? "Pendiente" : $item->getFactura()->getInfo1(),
                        'idEstadoFactura' => $item->getFactura() === null ? "" : $item->getFactura()->getEstado()->getId(),
                        'estadoFactura' => $item->getFactura() === null ? "Pendiente" : $item->getFactura()->getEstado()->getNombre(),
                        'notificada' => $item->getNotificada() === true ? "Si" : "No",
                    );
                    $rows[] = $row;
                }
                $total = $result['total'];
                $current = $result['current'];
                $rowCount = $result['rowCount'];
            }

        } catch (\Exception $exc) {
            var_dump($exc->getMessage());
        }

        $response = new JsonResponse();
        $response->setData(array(
            'total' => $total,
            'current' => $current,
            'rowCount' => $rowCount,
            'rows' => $rows
        ));
        return $response;
    }
    
    /**
     * @Route(path="/consultarCompra.html", name="admin-compra-consultar")
    */
    public function consultarCompraAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
            if (is_null($id)) {
                return UtilService::returnError($this, "m1No se pudo obtener el id de la compra.");
            }
        }
        
        $compra = $this->getDoctrine()->getRepository('MayaBundle:Compra')->find($id);
        if ($compra === null) {
            return UtilService::returnError($this, "m1La compra con id: " .$id." no existe.");
        }
        
        return $this->render('BackendBundle:Compra:consultar.html.twig', array(
            'compra' => $compra
        ));
    }
    
    /**
     * @Route(path="/facturarCompra.html", name="admin-compra-facturar")
    */
    public function facturarComprasAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
            if (is_null($id)) {
                $command = $request->request->get('backendbundle_facturar_compra_type'); //Submit
                if($command !== null){
                    $id = $command["id"];
                }
                
                if (is_null($id)) {
                    return UtilService::returnError($this, "m1No se pudo obtener el id de la compra.");
                }
            }
        }
        
        $compra = $this->getDoctrine()->getRepository('MayaBundle:Compra')->find($id);
        if ($compra === null) {
            return UtilService::returnError($this, "m1La compra con id: " .$id." no existe.");
        }
        
        $form = $this->createForm(new FacturarCompraType($this->getDoctrine()), $compra);
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $compra->setEstado($this->getDoctrine()->getRepository('MayaBundle:EstadoCompra')->find(EstadoCompra::FACTURADA));
                    $factura = $compra->getFactura();
                    $factura->setEstado($this->getDoctrine()->getRepository('MayaBundle:EstadoFactura')->find(EstadoFactura::CREADA));
                    
                    $erroresItems = $this->get('validator')->validate($compra);
                    if($erroresItems !== null && count($erroresItems) != 0){
                        return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                    }
                    
                    $erroresItems = $this->get('validator')->validate($factura);
                    if($erroresItems !== null && count($erroresItems) != 0){
                        return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                    }
                    
                    $em->persist($compra);
                    $em->flush();
                    $em->getConnection()->commit();
                    return UtilService::returnSuccess($this);

                } catch (\RuntimeException $exc) {
                    $em->getConnection()->rollback();
                    $mensaje = $exc->getMessage();
                    var_dump($mensaje);
                    if(UtilService::startsWith($mensaje, 'm1')){
                        $mensajeServidor = $mensaje;
                    }else{
                        $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    }
                    return UtilService::returnError($this, $mensajeServidor);
                } catch (\Exception $exc) {
                    $em->getConnection()->rollback();
                    $mensaje = $exc->getMessage();
                    var_dump($mensaje);
                    return UtilService::returnError($this);
                }

            }else{
                return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('BackendBundle:Compra:facturar.html.twig', array(
            'form' => $form->createView(),
            'compra' => $compra,
            'route' => $_route
        ));
    }
    
    /**
     * @Route(path="/enviarFactura.html", name="admin-enviar-facturar")
    */
    public function enviarFacturaAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
            if (is_null($id)) {
                $command = $request->request->get('backendbundle_enviar_factura_type'); //Submit
                if($command !== null){
                    $id = $command["id"];
                }
                
                if (is_null($id)) {
                    return UtilService::returnError($this, "m1No se pudo obtener el id de la compra.");
                }
            }
        }
        
        $compra = $this->getDoctrine()->getRepository('MayaBundle:Compra')->find($id);
        if ($compra === null) {
            return UtilService::returnError($this, "m1La compra con id: " .$id." no existe.");
        }
        
        $form = $this->createForm(new EnviarFacturaType($this->getDoctrine()), $compra);
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $factura = $compra->getFactura();
                    $factura->setEstado($this->getDoctrine()->getRepository('MayaBundle:EstadoFactura')->find(EstadoFactura::ENVIADA));
                    
                    $erroresItems = $this->get('validator')->validate($factura);
                    if($erroresItems !== null && count($erroresItems) != 0){
                        return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                    }
                    
                    $em->persist($factura);
                    $em->flush();
                    $em->getConnection()->commit();
                    return UtilService::returnSuccess($this);

                } catch (\RuntimeException $exc) {
                    $em->getConnection()->rollback();
                    $mensaje = $exc->getMessage();
                    var_dump($mensaje);
                    if(UtilService::startsWith($mensaje, 'm1')){
                        $mensajeServidor = $mensaje;
                    }else{
                        $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    }
                    return UtilService::returnError($this, $mensajeServidor);
                } catch (\Exception $exc) {
                    $em->getConnection()->rollback();
                    $mensaje = $exc->getMessage();
                    var_dump($mensaje);
                    return UtilService::returnError($this);
                }

            }else{
                return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('BackendBundle:Compra:enviarFactura.html.twig', array(
            'form' => $form->createView(),
            'compra' => $compra,
            'route' => $_route
        ));
    }
    
    /**
     * @Route(path="/enviarAllFacturas.html", name="admin-enviar-all-facturas")
    */
    public function enviarAllFacturasAction(Request $request, $_route) {
        
        if ($request->isMethod('POST')) {
            $ids = $request->query->get('ids');
            if (is_null($ids)) {
                $ids = $request->request->get('ids');
                if (is_null($ids)) {
                    return UtilService::returnError($this, "m1No se pudo obtener los identificadores de la compra.");
                }
            }
            var_dump($ids);
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                    
                $ids = explode(",", $ids);
                foreach ($ids as $id) {
                    if($id === null || trim($id) === ""){
                        continue;
                    }
                    $compra = $this->getDoctrine()->getRepository('MayaBundle:Compra')->find($id);
                    if ($compra === null) {
                        return UtilService::returnError($this, "m1La compra con id: " .$id." no existe.");
                    }
                    $factura = $compra->getFactura();
                    if($factura === null){
                        return UtilService::returnError($this, "m1La compra con id: " .$id." no está asociada a ninguna factura.");
                    }
                    if($factura->getEstado()->getId() !== intval(EstadoFactura::CREADA)){
                        return UtilService::returnError($this, "m1Solamente se pueden enviar facturas que esten en estado creada."
                                . " La factura de la compra con id: " .$id." está en estado " . $factura->getEstado()->getNombre() . ".");
                    }
                    $factura->setEstado($this->getDoctrine()->getRepository('MayaBundle:EstadoFactura')->find(EstadoFactura::ENVIADA));
                    $erroresItems = $this->get('validator')->validate($factura);
                    if($erroresItems !== null && count($erroresItems) != 0){
                        return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                    }
                    $em->persist($factura);
                }
                
                $em->flush();
                $em->getConnection()->commit();
                return UtilService::returnSuccess($this);

            } catch (\RuntimeException $exc) {
                
                $em->getConnection()->rollback();
                $mensaje = $exc->getMessage();
                var_dump($mensaje);
                if(UtilService::startsWith($mensaje, 'm1')){
                    $mensajeServidor = $mensaje;
                }else{
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                }
                    return UtilService::returnError($this, $mensajeServidor);
                    
            } catch (\Exception $exc) {
                $em->getConnection()->rollback();
                $mensaje = $exc->getMessage();
                var_dump($mensaje);
                return UtilService::returnError($this);
            }
        }
        
        return UtilService::returnError($this, "La petición solo soporta POST");
    }
    
    /**
     * @Route(path="/recibirFactura.html", name="admin-recibir-facturar")
    */
    public function recibirFacturaAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
            if (is_null($id)) {
                $command = $request->request->get('backendbundle_recibir_factura_type'); //Submit
                if($command !== null){
                    $id = $command["id"];
                }
                
                if (is_null($id)) {
                    return UtilService::returnError($this, "m1No se pudo obtener el id de la compra.");
                }
            }
        }
        
        $compra = $this->getDoctrine()->getRepository('MayaBundle:Compra')->find($id);
        if ($compra === null) {
            return UtilService::returnError($this, "m1La compra con id: " .$id." no existe.");
        }
        
        $form = $this->createForm(new RecibirFacturaType($this->getDoctrine()), $compra);
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $factura = $compra->getFactura();
                    $factura->setEstado($this->getDoctrine()->getRepository('MayaBundle:EstadoFactura')->find(EstadoFactura::RECIBIDA));
                    
                    $erroresItems = $this->get('validator')->validate($factura);
                    if($erroresItems !== null && count($erroresItems) != 0){
                        return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                    }
                    
                    $em->persist($factura);
                    $em->flush();
                    $em->getConnection()->commit();
                    return UtilService::returnSuccess($this);

                } catch (\RuntimeException $exc) {
                    $em->getConnection()->rollback();
                    $mensaje = $exc->getMessage();
                    var_dump($mensaje);
                    if(UtilService::startsWith($mensaje, 'm1')){
                        $mensajeServidor = $mensaje;
                    }else{
                        $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    }
                    return UtilService::returnError($this, $mensajeServidor);
                } catch (\Exception $exc) {
                    $em->getConnection()->rollback();
                    $mensaje = $exc->getMessage();
                    var_dump($mensaje);
                    return UtilService::returnError($this);
                }

            }else{
                return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('BackendBundle:Compra:recibirFactura.html.twig', array(
            'form' => $form->createView(),
            'compra' => $compra,
            'route' => $_route
        ));
    }
    
    /**
     * @Route(path="/recibirAllFacturas.html", name="admin-recibir-all-facturas")
    */
    public function recibirAllFacturasAction(Request $request, $_route) {
        
        if ($request->isMethod('POST')) {
            $ids = $request->query->get('ids');
            if (is_null($ids)) {
                $ids = $request->request->get('ids');
                if (is_null($ids)) {
                    return UtilService::returnError($this, "m1No se pudo obtener los identificadores de la compra.");
                }
            }
            echo $ids;
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                    
                $ids = explode(",", $ids);
                foreach ($ids as $id) {
                    if($id === null || trim($id) === ""){
                        continue;
                    }
                    $compra = $this->getDoctrine()->getRepository('MayaBundle:Compra')->find($id);
                    if ($compra === null) {
                        return UtilService::returnError($this, "m1La compra con id: " .$id." no existe.");
                    }
                    $factura = $compra->getFactura();
                    if($factura === null){
                        return UtilService::returnError($this, "m1La compra con id: " .$id." no está asociada a ninguna factura.");
                    }
                    if($factura->getEstado()->getId() !== intval(EstadoFactura::ENVIADA)){
                        return UtilService::returnError($this, "m1Solamente se pueden recibir facturas que esten en estado enviada."
                                . " La factura de la compra con id: " .$id." está en estado " . $factura->getEstado()->getNombre() . ".");
                    }
                    $factura->setEstado($this->getDoctrine()->getRepository('MayaBundle:EstadoFactura')->find(EstadoFactura::RECIBIDA));
                    $erroresItems = $this->get('validator')->validate($factura);
                    if($erroresItems !== null && count($erroresItems) != 0){
                        return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                    }
                    $em->persist($factura);
                }
                
                $em->flush();
                $em->getConnection()->commit();
                return UtilService::returnSuccess($this);

            } catch (\RuntimeException $exc) {
                
                $em->getConnection()->rollback();
                $mensaje = $exc->getMessage();
                var_dump($mensaje);
                if(UtilService::startsWith($mensaje, 'm1')){
                    $mensajeServidor = $mensaje;
                }else{
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                }
                    return UtilService::returnError($this, $mensajeServidor);
                    
            } catch (\Exception $exc) {
                $em->getConnection()->rollback();
                $mensaje = $exc->getMessage();
                var_dump($mensaje);
                return UtilService::returnError($this);
            }
        }
        
        return UtilService::returnError($this, "La petición solo soporta POST");
    }
    
    /**
     * @Route(path="/entregarFactura.html", name="admin-entregar-facturar")
    */
    public function entregarFacturaAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
            if (is_null($id)) {
                $command = $request->request->get('backendbundle_entregar_factura_type'); //Submit
                if($command !== null){
                    $id = $command["id"];
                }
                
                if (is_null($id)) {
                    return UtilService::returnError($this, "m1No se pudo obtener el id de la compra.");
                }
            }
        }
        
        $compra = $this->getDoctrine()->getRepository('MayaBundle:Compra')->find($id);
        if ($compra === null) {
            return UtilService::returnError($this, "m1La compra con id: " .$id." no existe.");
        }
        
        $form = $this->createForm(new EntregarFacturaType($this->getDoctrine()), $compra);
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $factura = $compra->getFactura();
                    $factura->setEstado($this->getDoctrine()->getRepository('MayaBundle:EstadoFactura')->find(EstadoFactura::ENTREGADA));
                    
                    $erroresItems = $this->get('validator')->validate($factura);
                    if($erroresItems !== null && count($erroresItems) != 0){
                        return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                    }
                    
                    $em->persist($factura);
                    $em->flush();
                    $em->getConnection()->commit();
                    return UtilService::returnSuccess($this);

                } catch (\RuntimeException $exc) {
                    $em->getConnection()->rollback();
                    $mensaje = $exc->getMessage();
                    var_dump($mensaje);
                    if(UtilService::startsWith($mensaje, 'm1')){
                        $mensajeServidor = $mensaje;
                    }else{
                        $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    }
                    return UtilService::returnError($this, $mensajeServidor);
                } catch (\Exception $exc) {
                    $em->getConnection()->rollback();
                    $mensaje = $exc->getMessage();
                    var_dump($mensaje);
                    return UtilService::returnError($this);
                }

            }else{
                return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('BackendBundle:Compra:entregarFactura.html.twig', array(
            'form' => $form->createView(),
            'compra' => $compra,
            'route' => $_route
        ));
    }
}