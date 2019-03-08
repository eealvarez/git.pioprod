<?php

namespace Acme\MayaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ServiceController extends Controller
{
    
    public function getServicesAction()
    {
        $response = $this->render('MayaBundle::services.html.twig');
        $response->setMaxAge(3600); //Cache del servidor
        $response->setVary('Accept-Encoding'); //Cache del servidor
        $response->setExpires(new \DateTime('now + 60 minutes')); //Cache del navegador
        return $response;
    }
    
    
}
