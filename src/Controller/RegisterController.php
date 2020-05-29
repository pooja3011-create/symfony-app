<?php

namespace App\Controller;

use App\Entity\User;
use App\Event\UserRegisterEvent;
use App\Event\UserSubscriber;
use App\Security\TokenGenerator;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RegisterController extends AbstractController
{
    /**
     * @Route("/register", name="user_register")
     */
    public function register(
            UserPasswordEncoderInterface $passwordEncoder, 
            Request $request,
            EventDispatcherInterface $eventDispatcher,
            TokenGenerator $tokenGenerator
        ) 
    {
        $user = new User();
        $form = $this->createForm(
            UserType::class, 
            $user
        );
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword(
                $user, 
                $user->getPlainPassword()
            );
            $user->setPassword($password);
            
            /* set token for confirmation like*/
            $user->setConfirmationToken(
                $tokenGenerator->getRandomSecureToken(30)
            );
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            
            /* Event Dispachting for registered user*/
            
            $userRegisterEvent = new userRegisterEvent($user);
            $eventDispatcher->dispatch(
                userRegisterEvent:: NAME, 
                $userRegisterEvent
            );
                    
            return $this->redirectToRoute('micro_post_index');
        }
        
        return $this->render('register/register.html.twig',
            ['form' => $form->createView()]
        );
    }
}
