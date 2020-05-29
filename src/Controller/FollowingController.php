<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("is_granted('ROLE_USER')")
 * @Route("/following")
 */
class FollowingController extends AbstractController
{
    /**
     * @Route("/follow/{id}", name="following_follow")
     */
    public function follow(User $userToFollow) 
    {
        /** User CurrentUser */
        $currentUser = $this->getUser();
        /* Check current user and follow user id same or not because we can not follow ourself */
        if($userToFollow->getId() != $currentUser->getId()){
            $currentUser->follow($userToFollow);
            
            $this->getDoctrine()
                ->getManager()
                ->flush();
       
        }
        
        return $this->redirectToRoute('micro_post_user',
            ['username' => $userToFollow->getUsername()]
        );
    }
    
    /**
     * @Route("/unfollow/{id}", name="following_unfollow")
     */
    public function unfollow(User $userToUnfollow) 
    {
         /** User CurrentUser */
        $currentUser = $this->getUser();
        $currentUser->getFollowing()
                ->removeElement($userToUnfollow);
        
        $this->getDoctrine()
                ->getManager()
                ->flush();
        
        return $this->redirectToRoute('micro_post_user',
            ['username' => $userToUnfollow->getUsername()]
        );
    }
    
}
