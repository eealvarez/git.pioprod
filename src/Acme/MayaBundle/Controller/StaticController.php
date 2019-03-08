<?php

namespace Acme\MayaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class StaticController extends Controller
{
    
    public function homeAction()
    {
        $response = $this->render('MayaBundle::home.html.twig');
        $response->setMaxAge(3600); //Cache del servidor
        $response->setVary('Accept-Encoding'); //Cache del servidor
        $response->setExpires(new \DateTime('now + 60 minutes')); //Cache del navegador
        return $response;
    }
    
    public function staticAction($page, $ancla)
    {
        $response = $this->render('MayaBundle::'.$page.'.html.twig', array('ancla'=>$ancla));
        $response->setMaxAge(3600); //Cache del servidor
        $response->setVary('Accept-Encoding'); //Cache del servidor
        $response->setExpires(new \DateTime('now + 60 minutes')); //Cache del navegador
        return $response;
    }
    
     protected function getRootDir()
    {
        return __DIR__.'\\..\\..\\..\\..\\web\\';
    }
}
