<?php

namespace Acme\BackendBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Acme\BackendBundle\Services\UtilService;
use Acme\BackendBundle\Exceptions\IpNotValidException;
use Acme\BackendBundle\Exceptions\AccessAppWebException;

class CustomUserChecker extends UserChecker implements UserCheckerInterface{
    
    protected $container;
    
    function __construct($container) {
        $this->container = $container;
    }
    
    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof AdvancedUserInterface) {
            return;
        }
        
        if (isset($user->getAccessAppWeb) && !$user->getAccessAppWeb()) {
            $ex = new AccessAppWebException("Usted no está autorizado a acceder a la web.");
            $ex->setUser($user);
            throw $ex;
        }
        
        if(isset($user->getIpRanges)){
            $clientIp = $this->container->get("request")->getClientIp();
            $ipRanges = $user->getIpRanges();
            if(!UtilService::isValidIpRequestOfUser($ipRanges, $clientIp)){
                $ex = new IpNotValidException("Usted no puede acceder desde esa dirección IP.");
                $ex->setUser($user);
                throw $ex;
            }
        }
        
        if (isset($user->isCredentialsNonExpired) && !$user->isCredentialsNonExpired()) {
            $ex = new CredentialsExpiredException('Sus credenciales han expirado.');
            $ex->setUser($user);
            throw $ex;
        }
        
    }

    /**
     * {@inheritdoc}
     */
    public function checkPostAuth(UserInterface $user)
    {
        if (!$user instanceof AdvancedUserInterface) {
            return;
        }
        
        if (!$user->isAccountNonLocked()) {
            $ex = new LockedException('Su usuario está bloqueado.');
            $ex->setUser($user);
            throw $ex;
        }

        if (!$user->isEnabled()) {
            $ex = new DisabledException('Su usuario no está habilitado.');
            $ex->setUser($user);
            throw $ex;
        }

        if (!$user->isAccountNonExpired()) {
            $ex = new AccountExpiredException('Su usuario ha expirado.');
            $ex->setUser($user);
            throw $ex;
        }
    }
}
