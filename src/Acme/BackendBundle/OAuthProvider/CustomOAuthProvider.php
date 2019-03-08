<?php

namespace Acme\BackendBundle\OAuthProvider;

use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Acme\BackendBundle\Entity\UserOauth;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class CustomOAuthProvider extends OAuthUserProvider{
    
    protected $container;
    protected $doctrine;
    
    public function __construct($container) {
        $this->container = $container;
        $this->doctrine = $this->container->get('doctrine');
   }
   
    public function loadUserByUsername($username) {
        $user = $this->loadInternalUserByUsername($username);
        if ($user !== null) {   return $user; } 
        else {                  throw new UsernameNotFoundException(); }
    }
    
    private function loadInternalUserByUsername($username) {
        $qb = $this->doctrine->getManager()->createQueryBuilder();
        $qb->select('u')
           ->from('BackendBundle:UserOauth', 'u')
           ->where('u.username = :username')
           ->setParameter('username', $username)
           ->setMaxResults(1);
        $result = $qb->getQuery()->getResult();
        if (count($result)) {
            return $result[0];
        } else {
            return null;
        }
    }

    public function supportsClass($class) {
        return $class === 'Acme\BackendBundle\Entity\UserOauth';
    }
	
    public function refreshUser(UserInterface $user) {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user)));
	}
	return $user;
    }
	
    public function loadUserByOAuthUserResponse(UserResponseInterface $response) {
        $result = $response->getResponse();
//        var_dump($result);
//        var_dump($result["error"]["code"]);
//        throw new \RuntimeException("aa");
        $provider = $response->getResourceOwner()->getName();
        $providerId = $response->getUsername();
//        if(!isset($result["error"]["code"])){
            $nickname = $response->getNickname();
            $realname = $response->getRealName();
            $email = $response->getEmail();
            $profilePicture = $response->getProfilePicture();
            $accessToken = $response->getAccessToken();
            $refreshToken = $response->getRefreshToken();
            $tokenSecret = $response->getTokenSecret();
            $expiresIn = $response->getExpiresIn();
            if($provider !== null && trim($provider) !== "" && $providerId !== null && trim($providerId) !== ""){
                $user = $this->loadInternalUserByUsername($provider."-".$providerId);
                if($user === null){
                    $user = new UserOauth($provider, $providerId);
                }
                $user->setRealname($realname);
                $user->setNickname($nickname);
                $user->setEmail($email);
                $user->setProfilePicture($profilePicture);
                $user->setAccessToken($accessToken);
                $user->setRefreshToken($refreshToken);
                $user->setTokenSecret($tokenSecret);
                $user->setExpiresIn($expiresIn);
                $em = $this->doctrine->getManager();
                $em->persist($user);
                $em->flush();
            }  
            
//            if($provider === "facebook"){
//                $facebook = $this->container->get("acme_facebook")->getFacebook();
//                $facebook->publicMakeSignedRequest(array(
//                  'user_id' => $providerId,
//                  'oauth_token' => $accessToken
//                ));
//            }
    
//        }
        return $this->loadUserByUsername($provider."-".$providerId);
    }
}
