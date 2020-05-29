<?php

namespace App\Mailer;

use App\Entity\User;

class Mailer 
{
    /**
     * @var Swift_mailer
     */
    private $mailer;
    /**
     * @var Twig Environment
     */
    private $twig;
    /**
     * @param string $mailFrom
     */
    private $mailFrom;

    public function __construct(
        \Twig\Environment $twig, 
        \Swift_Mailer $mailer, 
        string $mailFrom
    ) 
    {
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->mailForm = $mailFrom;
    }
    
    public function sendConfirmationEmail(User $user) 
    {
        $body = $this->twig->render('email/registration.html.twig',
            [
                'user' => $user
            ]
        );
        $message = (new \Swift_Message())
           ->setSubject('Welcome to the micro-post app!')
           ->setFrom($this->mailFrom)
           ->setTo($user->getEmail())
           ->setBody($body, 'text/html');
           
        $this->mailer->send($message);
    }
}
