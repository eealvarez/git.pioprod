<?php

namespace Acme\BackendBundle\Handler;

use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;
use Psr\Log\LoggerInterface;
use Acme\BackendBundle\Entity\User;
//use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface{
   
    protected $container;
	
    public function __construct($container)
    {
        $this->container = $container;
    }
	
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $context = array('code' => 'SEC001' );
        $username = "anonimo";
        if($token->getUser() !== null){
            $username = $token->getUser()->getUsername();
        }
        $this->container->get("logger")->notice('Autenticado satisfactoriamente el usuario:'. $username . ".", $context);
        $session = $this->container->get('request')->getSession();
        $session->set("username", $username); //Para los log fails
        
//        $user = $token->getUser();
//        if($user !== null && $user instanceof User){
//            $userManager = $this->container->get('fos_user.user_manager');
//            $em = $this->container->get("doctrine")->getManager();
//            $em->getConnection()->beginTransaction();
//            try {
//                $user->clearIntentosFallidos();
//                $userManager->updateUser($user);
//                $em->flush();
//                $em->getConnection()->commit();
//             } catch (\RuntimeException $exc) {
//                $em->getConnection()->rollback();
//                $mensaje = 'Autenticado satisfactoriamente pero ocurrio un error actualizando el usuario: ' . $user->getUsername() . '.';
//                $this->container->get("logger")->warn($mensaje, $context);
//             } catch (\Exception $exc) {
//                $em->getConnection()->rollback();
//                $mensaje = 'Autenticado satisfactoriamente pero ocurrio un error actualizando el usuario: ' . $user->getUsername() . '.';
//                $this->container->get("logger")->warn($mensaje, $context);
//             }  
//        }
        
//        if ($this->security->isGranted('ROLE_SUPER_ADMIN')){
//            $response = new RedirectResponse($this->router->generate('home-default'));			
//	}
//	else if ($this->security->isGranted('ROLE_ADMIN')){
//            $response = new RedirectResponse($this->router->generate('home-default'));
//	} 
//	else if ($this->security->isGranted('ROLE_USER')){
//            $response = new RedirectResponse($this->router->generate('home-default'));
////            $referer_url = $request->headers->get('referer');
////            $response = new RedirectResponse($referer_url);
//	}
//	return $response;
        return new RedirectResponse($this->container->get("router")->generate('sonata_admin_dashboard'));
    }
}
