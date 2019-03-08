<?php

namespace Acme\BackendBundle\Handler;

use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler;
use Symfony\Component\Security\Core\SecurityContext;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\FatalErrorException;

class LogoutSuccessHandler implements LogoutSuccessHandlerInterface{
   
    protected $router;
    protected $logger;
    protected $securityContext;

    public function __construct(Router $router, SecurityContext $securityContext, LoggerInterface $logger)
    {
        $this->router = $router;
        $this->securityContext = $securityContext;
        $this->logger = $logger;
    }
	
    public function onLogoutSuccess(Request $request)
    {
         $context = array('code' => 'SEC003' );
         try {
             if($this->securityContext->getToken() !== null){
                $token =  $this->securityContext->getToken();
                $username = "anonimo";
                if($token->getUser() !== null){
                    $username = $token->getUser()->getUsername();
                }
                $this->logger->notice('El usuario: ' . $username . ' salió del sistema.', $context);
             }
         } 
         catch (FatalErrorException $ex) { }
         catch (\Exception $ex) { }
         
         $referer_url = $request->headers->get('referer');
         if($referer_url !== null && trim($referer_url) !== ""){       
            $response = new RedirectResponse($referer_url);
         }else{
            $response = new RedirectResponse($this->router->generate('_maya_homepage'));
         }
         return $response;
    }
}
