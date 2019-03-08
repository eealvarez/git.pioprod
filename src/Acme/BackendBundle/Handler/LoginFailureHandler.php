<?php

namespace Acme\BackendBundle\Handler;

use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
//use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Acme\BackendBundle\Entity\User;

class LoginFailureHandler implements AuthenticationFailureHandlerInterface{
   
    protected $router;
    protected $security;
    protected $logger;
    protected $options;
    protected $container;
    
    public function __construct(Router $router, SecurityContext $security, LoggerInterface $logger, $container)
    {
        $this->router = $router;
	$this->security = $security;
        $this->logger = $logger;
        $this->container = $container;
    }

    public function onAuthenticationFailure(Request $request, \Symfony\Component\Security\Core\Exception\AuthenticationException $exception) {
        $data = array();
        $mensaje = $exception->getMessage();
        $token = $exception->getToken();
        $data["code"] = 'SEC002';
        if($token !== null){
            $username = "anonimo";
            if($token->getUser() !== null){
                if($token->getUser() instanceof User){
                    $username = $token->getUser()->getUsername();
                }else{
                    $username = $token->getUser();
                }
            }
            $mensaje = 'Falló el intento de autenticación del usuario (' . $username . ') con la contraseña (' . $token->getCredentials() . ').';
            $data["username"] = $username;
            
            $userManager = $this->container->get('fos_user.user_manager');
            $user = $userManager->findUserByUsername($username); 
            if($user !== null && $user instanceof User){
                $em = $this->container->get("doctrine")->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    $user->addIntentosFallidos();
                    $userManager->updateUser($user);
                    $em->flush();
                    $em->getConnection()->commit();
                 } catch (\RuntimeException $exc) {
                    $em->getConnection()->rollback();
                    $mensaje = 'Falló el intento de autenticación al webservice del usuario (' . $username . ') con la contraseña (' . $token->getCredentials() . '). Ha ocurrido un error actualizando el usuario.';
                    $this->container->get("logger")->warn($mensaje, $data);
                 } catch (\Exception $exc) {
                    $em->getConnection()->rollback();
                    $mensaje = 'Falló el intento de autenticación al webservice del usuario (' . $username . ') con la contraseña (' . $token->getCredentials() . '). Ha ocurrido un error actualizando el usuario.';
                    $this->container->get("logger")->warn($mensaje, $data);
                 }  
            }
        }
        
        $this->logger->warning($mensaje, $data);
        $request->getSession()->set(SecurityContextInterface::AUTHENTICATION_ERROR, $exception);        
        return new RedirectResponse($this->router->generate('custom_fos_user_security_login'));
    }

}
