<?php

namespace Acme\BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sonata\AdminBundle\Controller\CRUDController;
use Acme\MayaBundle\Entity\ItinerarioCompuesto;
use Acme\BackendBundle\Form\Type\ItinerarioCompuesto\ItinerarioCompuestoType;
use Acme\MayaBundle\Entity\ItinerarioItem;

class ItinerarioCompuestoCRUDController extends CRUDController {
 
    public function cargarListaDesdeHiddenHaciaObjeto(ItinerarioCompuesto $itinerarioCompuesto, $listaParadasIntermediasHidden)
    {
        $listaNewJson = json_decode($listaParadasIntermediasHidden);
        foreach ($listaNewJson as $json) { 
            $item = new ItinerarioItem();
            $item->setBajaEn($this->getDoctrine()->getRepository('MayaBundle:Estacion')->find($json->idEstacion));
            $item->setOrden($json->orden);
            foreach ($json->listaItinerariosSimples as $json1) { 
                $item->getListaItinerarioSimple()->add($this->getDoctrine()->getRepository('MayaBundle:ItinerarioSimple')->find($json1->idItinerario));
            }
            $itinerarioCompuesto->addListaItinerarioItem($item);
        }        
        
    }
    
    public function isNewId($id)
    {
        return ($id === null || trim($id) === "" || trim($id) === "0");
    }
    
    public function editAction($id = null, Request $request = null)
    {
//        $request = $this->resolveRequest($request);
        // the key used to lookup the template
        $templateKey = 'edit';

        $id = $request->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
        }

        $this->admin->setSubject($object);

        $form = $this->createForm(new ItinerarioCompuestoType($this->getDoctrine()), $object, array(
            "edit" => true
        ));
        
        $form->setData($object);

        if ($this->getRestMethod($request) == 'POST') {
            
            $form->setData($object);            
            $form->submit($request);
            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode($request) || $this->isPreviewApproved($request))) {

                try {
                    
                    $object = $this->admin->update($object);
                    $this->get('acme_backend_conexion')->procesarConexionPorItinerarioCompuesto($object);

                    if ($this->isXmlHttpRequest($request)) {
                        return $this->renderJson(array(
                            'result'    => 'ok',
                            'objectId'  => $this->admin->getNormalizedIdentifier($object)
                        ), 200, array(), $request);
                    }

                    $this->addFlash(
                        'sonata_flash_success',
                        $this->admin->trans(
                            'flash_edit_success',
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                            'SonataAdminBundle'
                        )
                    );

                    // redirect to edit mode
                    return $this->redirectTo($object, $request);

                } catch (ModelManagerException $e) {
                    $this->handleModelManagerException($e);

                    $isFormValid = false;
                }
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest($request)) {
                    $this->addFlash(
                        'sonata_flash_error',
                        $this->admin->trans(
                            'flash_edit_error',
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                            'SonataAdminBundle'
                        )
                    );
                }
            } elseif ($this->isPreviewRequested($request)) {
                // enable the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

        return $this->render($this->admin->getTemplate($templateKey), array(
            'action' => 'edit',
            'form'   => $view,
            'object' => $object,
        ), null, $request);
    }
    
    public function createAction(Request $request = null)
    {
//        $request = $this->resolveRequest($request);
        // the key used to lookup the template
        $templateKey = 'edit';

        if (false === $this->admin->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        $object = $this->admin->getNewInstance();

        $this->admin->setSubject($object);

        $form = $this->createForm(new ItinerarioCompuestoType($this->getDoctrine()), $object, array(
            "edit" => false
        )); 
        
        $form->setData($object);

        if ($this->getRestMethod($request) == 'POST') {
            
            $commad = $this->get('request')->request->get("backendbundle_itinerario_compuesto_type");
//            var_dump($commad);
            $listaParadasIntermediasHidden = $commad["listaParadasIntermediasHidden"];
            $this->cargarListaDesdeHiddenHaciaObjeto($object, $listaParadasIntermediasHidden);
            
            $firstItinerarioSimple = $object->getListaItinerarioItem()->first()->getListaItinerarioSimple()->first();
            $object->setDiaSemana($firstItinerarioSimple->getDiaSemana());
            $object->setHorarioCiclico($firstItinerarioSimple->getHorarioCiclico());
            $object->setEstacionDestino($this->getDoctrine()->getRepository('MayaBundle:Estacion')->find($commad["idEstacionDestino"]));
            $object->calculateDiasHorariosStr();
            
            $form->setData($object);
            $form->submit($request);
            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode($request) || $this->isPreviewApproved($request))) {

                if (false === $this->admin->isGranted('CREATE', $object)) {
                    throw new AccessDeniedException();
                }

                try {
                    
                    
                    $object = $this->admin->create($object);
                    $this->get('acme_backend_conexion')->procesarConexionPorItinerarioCompuesto($object);

                    if ($this->isXmlHttpRequest($request)) {
                        return $this->renderJson(array(
                            'result' => 'ok',
                            'objectId' => $this->admin->getNormalizedIdentifier($object)
                        ), 200, array(), $request);
                    }

                    $this->addFlash(
                        'sonata_flash_success',
                        $this->admin->trans(
                            'flash_create_success',
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                            'SonataAdminBundle'
                        )
                    );

                    // redirect to edit mode
                    return $this->redirectTo($object, $request);

                } catch (ModelManagerException $e) {
                    $this->handleModelManagerException($e);

                    $isFormValid = false;
                }
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest($request)) {
                    $this->addFlash(
                        'sonata_flash_error',
                        $this->admin->trans(
                            'flash_create_error',
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                            'SonataAdminBundle'
                        )
                    );
                }
            } elseif ($this->isPreviewRequested($request)) {
                // pick the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

        return $this->render($this->admin->getTemplate($templateKey), array(
            'action' => 'create',
            'form'   => $view,
            'object' => $object,
        ), null, $request);
    }
}

?>
