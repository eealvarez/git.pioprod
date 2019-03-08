<?php

namespace Acme\MayaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Acme\MayaBundle\Form\BuscarConexionesType;
use Acme\MayaBundle\Form\Model\BuscarConexionesModel;
use Acme\MayaBundle\Form\ClienteAnonimoType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Acme\MayaBundle\Entity\Cliente;
use Acme\BackendBundle\Entity\UserOauth;
use Acme\MayaBundle\Form\RecaptchaType;

class TicketController extends Controller
{
    
//    public function getParametersTicketAction()
//    {
//        $formConexion = $this->createForm(new BuscarConexionesType());
//        
//        $cliente = new Cliente();       
//        $formAnonimo = $this->createForm(new ClienteAnonimoType($this->getDoctrine()), $cliente);
//        
//        $response = $this->render('MayaBundle::ticket.html.twig', array(
//            'form1' => $formConexion->createView(),
//            'form2' => $formAnonimo->createView()
//        ));
//        $response->setMaxAge(3600); //Cache del servidor
//        $response->setVary('Accept-Encoding'); //Cache del servidor
//        $response->setExpires(new \DateTime('now + 60 minutes')); //Cache del navegador
//        
//        return $response;
//    }
    
    public function getParametersTicketAction()
    {
        
        $model = new BuscarConexionesModel();
        $formConexion = $this->createForm(new BuscarConexionesType($this->getDoctrine()), $model, array(
            "em" => $this->getDoctrine()->getManager(),
        ));
        
        $cliente = new Cliente();       
        $formAnonimo = $this->createForm(new ClienteAnonimoType($this->getDoctrine()), $cliente);
        
        $response = $this->render('MayaBundle::ticket3.html.twig', array(
            'form1' => $formConexion->createView(),
            'form2' => $formAnonimo->createView()
        ));
        $response->setMaxAge(3600); //Cache del servidor
        $response->setVary('Accept-Encoding'); //Cache del servidor
        $response->setExpires(new \DateTime('now + 60 minutes')); //Cache del navegador
        
        return $response;
    }
    
    
    
    
}
