<?php

namespace Acme\MayaBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Acme\MayaBundle\Entity\Mensaje;
use Acme\MayaBundle\Form\MensajeType;
use Acme\BackendBundle\Services\UtilService;
use ReCaptcha\ReCaptcha;

/**
* @Route(path="/mensaje")
*/
class MensajeController extends Controller
{
     /**
     * @Route(path="/enviar.html", name="enviarMensaje")
    */
    public function enviarMensajeAction(Request $request, $_route = "enviarMensaje") {
        
        $mensaje = new Mensaje();
        $form = $this->createForm(new MensajeType($this->getDoctrine()), $mensaje);
        
        if ($request->isMethod('POST')) {
            
            $form->bind($request);
            
            $recaptcha = new ReCaptcha($this->container->getParameter("recaptcha_private_key"));
            $gRecaptchaResponse = $request->request->get('g-recaptcha-response');
            if($gRecaptchaResponse === null || trim($gRecaptchaResponse) === ""){
                return UtilService::returnError($this, "Debe definir el recaptcha.");
            }
            
            $resp = $recaptcha->verify($gRecaptchaResponse, null);
            if ($resp->isSuccess()) {
                
                if ($form->isValid()) {    
                
                    $em = $this->getDoctrine()->getManager();
                    $em->getConnection()->beginTransaction();
                    try {

                        $em->persist($mensaje);
                        $em->flush();
                        $em->getConnection()->commit();
                        return UtilService::returnSuccess($this);

                    } catch (\RuntimeException $exc) {
                        $em->getConnection()->rollback();
                        $mensaje = $exc->getMessage();
                        if(UtilService::startsWith($mensaje, 'm1')){
                            $mensajeServidor = $mensaje;
                        }else{
                            $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                        }
                        return UtilService::returnError($this, $mensajeServidor);
                    } catch (\Exception $exc) {
                        $em->getConnection()->rollback();
                        return UtilService::returnError($this);
                    }

                }else{
                   return UtilService::returnError($this, UtilService::getErrorsToForm($form));
                }
                
                
            } else {
                $errors = $resp->getErrorCodes();
                return UtilService::returnError($this, implode('', $errors));
            }
        }
        
        return $this->render('MayaBundle::mensaje.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route
        ));
    }
    
    
}