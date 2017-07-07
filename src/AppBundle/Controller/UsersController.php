<?php

namespace AppBundle\Controller;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Post;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UsersController extends FOSRestController implements ClassResourceInterface
{
    public function optionsUsersAction()
    {} // "options_users" [OPTIONS] /users

    public function getUsersAction()
    {} // "get_users"     [GET] /users

    public function newUsersAction()
    {} // "new_users"     [GET] /users/new

    /**
     * POST Route annotation.
     * @Post("/signup")
     */
    public function postUsersAction(ObjectManager $manager, UserPasswordEncoderInterface $encoder, Request $request)
    {
        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.registration.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return  new JsonResponse([$event->getResponse()]);
        }

        $form = $formFactory->createForm([
            'csrf_protection'    => false
        ]);

        $form->setData($user);
        $form->submit($request->request->all());

        if ( !$form->isValid()) {
            return new JsonResponse( $this->getErrorMessages($form) );
//            $event = new FormEvent($form, $request);
//            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_FAILURE, $event);
//
//
//            if (null !== $response = $event->getResponse() ) {
//                return $response;
//            }
//
//            return "error";
        }

        $event = new FormEvent($form, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

        if ($event->getResponse()) {
            return $event->getResponse();
        }

        $userManager->updateUser($user);

        $response = new JsonResponse(
            [
                'msg' => $this->get('translator')->trans('registration.flash.user_created', [], 'FOSUserBundle'),
                'token' => 'abc-123' // some way of creating the token
            ]
        );

        return $response;
    }

    private function getErrorMessages(\Symfony\Component\Form\Form $form) {
        $errors = array();

        foreach ($form->getErrors() as $key => $error) {
            if ($form->isRoot()) {
                $errors['#'][] = $error->getMessage();
            } else {
                $errors[] = $error->getMessage();
            }
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }

        return $errors;
    }
}