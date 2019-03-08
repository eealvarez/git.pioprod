<?php

namespace Acme\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
use Facebook\GraphUser;


/**
*   @Route(path="/secured")
*/
class SecuredController extends Controller {
      
    
    /**
     * @Route(path="/", name="secured_home")
    */
    public function homeAction(Request $request) {
        return $this->render('BackendBundle:Security:home.html.twig', array(
            'user' => $this->getUser()
        ));
    }
    
    /**
     * @Route(path="/facebook/publicar", name="secured_facebook_publicar")
    */
    public function facebookPublicarAction(Request $request) {
       
        FacebookSession::setDefaultApplication(
                $this->container->getParameter("facebook_client_id"), 
                $this->container->getParameter("facebook_client_secret"));
        
        try {
            
            $user = $this->getUser();
            $token = $user->getAccessToken();
            $facebookSession = new FacebookSession($token);
            $facebookRequest = new FacebookRequest($facebookSession, 'POST', '/me/feed', array(
                'link' => 'www.example.com',
                'message' => 'User provided message'
             ));
            $response = $facebookRequest->execute();
            $graphObject = $response->getGraphObject();
            echo "Posted with id: " . $graphObject->getProperty('id')."<BR>";
                
        } catch(FacebookRequestException $ex) {
            echo "Exception occured, code: " . $ex->getCode();
            echo " with message: " . $ex->getMessage();
        } catch(\Exception $ex) {
            var_dump($ex->getMessage());
        }
        return new Response("ok");
    }
    
    
    
//    /**
//     * @Route(path="/login", name="secured_login")
//    */
//    public function loginAction(Request $request) {
//        $session = $request->getSession();
//
//        // get the login error if there is one
//        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
//            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
//        } else {
//            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
//            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
//        }
//        
//        return $this->render('BackendBundle:Secured:login.html.twig', array(
//            // last username entered by the user
//            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
//            'error'         => $error,
//        ));
//    }
    
//    /**
//     * @Route(path="/login/facebook", name="secured_login_facebook")
//    */
//    public function facebookAction()
//    {
//        return $this->render('BackendBundle:Secured:facebook.html.twig');
//    }
}

?>
