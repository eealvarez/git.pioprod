<?php

namespace Acme\MayaBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
* @Route(path="/gallery")
*/
class GaleriaController extends Controller
{
     /**
     * @Route(path="/home.html", name="gallery_home")
    */
    public function homeAction() {
        return $this->render('MayaBundle:Gallery:home.html.twig');
    }
    
    
}
