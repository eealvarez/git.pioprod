<?php

namespace Acme\BackendBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Acme\BackendBundle\Form\Type\User\CreateUserType;
use Acme\BackendBundle\Form\Type\User\EditUserType;
use Acme\BackendBundle\Form\Type\User\ChangePasswordUserType;
use Acme\BackendBundle\Form\Model\User\CambiarContrasenaModel;

class UserCRUDController extends CRUDController {
 
    public function changePasswordAction()
    {
        // the key used to lookup the template
        $templateKey = 'changePassword';    
        
        $object = new CambiarContrasenaModel();
//
        $this->admin->setSubject($object);
        
        $form = $this->createForm(new ChangePasswordUserType($this->getDoctrine()), $object); 
        
        /** @var $form \Symfony\Component\Form\Form */
//        $form = $this->admin->getForm();
        $form->setData($object);

        if ($this->getRestMethod()== 'POST') {
            
            $form->bind($this->get('request'));
            $isFormValid = $form->isValid();
            
            $userManager = $this->container->get('fos_user.user_manager');
            $username = $form->get('username')->getData();
            $user = $userManager->findUserByUsernameOrEmail($username);
            if($user === null){
                $isFormValid = false;
                $form->addError(new \Symfony\Component\Form\FormError("El username no existe"));                
            }   
            
            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                
                $user->setPlainPassword($form->get('plainPassword')->getData());
                $user->setDateLastUdate(new \DateTime());
                $credentialsExpireAt = new \DateTime();
                $daysCredentialsExpire = $this->container->getParameter("days_credentials_expire");
                if(!$daysCredentialsExpire){ $daysCredentialsExpire = 90; }
                $credentialsExpireAt->modify("+" . $daysCredentialsExpire . " day");
                $user->setCredentialsExpireAt($credentialsExpireAt);
                $user->setCredentialsExpired(false);
                $userManager->updateUser($user);
                
                return $this->renderJson(array(
                        'result' => 'ok'
                ));

            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest()) {
                    $this->addFlash('sonata_flash_error', 'flash_create_error');
                }
            }
        }

        $view = $form->createView();
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());
        return $this->render('BackendBundle:UserAdmin:changePassword.html.twig', array(
            'action' => 'changePassword',
            'form'   => $view,
            'object' => $object,
        ));
    }
    
    public function editAction($id = null, Request $request = null)
    {
        $templateKey = 'edit';

        $id = $request->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
        }

        $this->admin->setSubject($object);

        $form = $this->createForm(new EditUserType($this->getDoctrine()), $object, array(
            "edit" => true
        ));
        
        $form->setData($object);

        if ($this->getRestMethod($request) == 'POST') {
            
            $form->setData($object);            
            $form->submit($request);
            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode($request) || $this->isPreviewApproved($request))) {

                try {
                    
                    $userManager = $this->container->get('fos_user.user_manager');
                    $object->setDateLastUdate(new \DateTime());
                    if($object->getExpired() === true){
                        $object->setExpiresAt(new \DateTime());
                    }else{
                        $object->setExpiresAt(null);
                    }
                    $userManager->updateUser($object);

                    if ($this->isXmlHttpRequest($request)) {
                        return $this->renderJson(array(
                            'result'    => 'ok',
                            'objectId'  => $this->admin->getNormalizedIdentifier($object)
                        ), 200, array(), $request);
                    }

                    $this->addFlash(
                        'sonata_flash_success',
                        $this->admin->trans(
                            'flash_edit_success',
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                            'SonataAdminBundle'
                        )
                    );

                    // redirect to edit mode
                    return $this->redirectTo($object, $request);

                } catch (ModelManagerException $e) {
                    $this->handleModelManagerException($e);

                    $isFormValid = false;
                }
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest($request)) {
                    $this->addFlash(
                        'sonata_flash_error',
                        $this->admin->trans(
                            'flash_edit_error',
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                            'SonataAdminBundle'
                        )
                    );
                }
            } elseif ($this->isPreviewRequested($request)) {
                // enable the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

        return $this->render($this->admin->getTemplate($templateKey), array(
            'action' => 'edit',
            'form'   => $view,
            'object' => $object,
        ), null, $request);
    }
    
    public function createAction(Request $request = null)
    {
        $templateKey = 'edit';

        if (false === $this->admin->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        $object = $this->admin->getNewInstance();

        $this->admin->setSubject($object);

        $form = $this->createForm(new CreateUserType($this->getDoctrine()), $object, array(
            "edit" => false
        )); 
        
        $form->setData($object);

        if ($this->getRestMethod($request) == 'POST') {
            
            $form->setData($object);
            $form->submit($request);
            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode($request) || $this->isPreviewApproved($request))) {

                if (false === $this->admin->isGranted('CREATE', $object)) {
                    throw new AccessDeniedException();
                }

                try {
                    
                    $userManager = $this->container->get('fos_user.user_manager');
                    $dateCreate = new \DateTime();
                    $object->setDateLastUdate($dateCreate);
                    $object->setDateCreate($dateCreate);
                    $credentialsExpireAt = new \DateTime();
                    $daysCredentialsExpire = $this->container->getParameter("days_credentials_expire");
                    if(!$daysCredentialsExpire){ $daysCredentialsExpire = 90; }
                    $credentialsExpireAt->modify("+" . $daysCredentialsExpire . " day");
                    $object->setCredentialsExpireAt($credentialsExpireAt);
                    $object->setIntentosFallidos(0);
                    $userManager->updateUser($object);

                    if ($this->isXmlHttpRequest($request)) {
                        return $this->renderJson(array(
                            'result' => 'ok',
                            'objectId' => $this->admin->getNormalizedIdentifier($object)
                        ), 200, array(), $request);
                    }

                    $this->addFlash(
                        'sonata_flash_success',
                        $this->admin->trans(
                            'flash_create_success',
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                            'SonataAdminBundle'
                        )
                    );

                    // redirect to edit mode
                    return $this->redirectTo($object, $request);

                } catch (ModelManagerException $e) {
                    $this->handleModelManagerException($e);

                    $isFormValid = false;
                }
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest($request)) {
                    $this->addFlash(
                        'sonata_flash_error',
                        $this->admin->trans(
                            'flash_create_error',
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                            'SonataAdminBundle'
                        )
                    );
                }
            } elseif ($this->isPreviewRequested($request)) {
                // pick the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

        return $this->render($this->admin->getTemplate($templateKey), array(
            'action' => 'create',
            'form'   => $view,
            'object' => $object,
        ), null, $request);
    }
    
}

?>
