<?php

namespace App\Security;

use App\Entity\MicroPost;
use App\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class MicroPostVoter extends Voter
{
    const EDIT = 'edit';
    const DELETE = 'delete';
    
    public function __construct(AccessDecisionManagerInterface $decisionManager) 
    {
        $this->decisionManager = $decisionManager;
    }
    
    /* $attribute means delete and edit*/
    /* $subject means Entity object like MicroPost */
    protected function supports($attribute,$subject) 
    {
        /* check edit and delete in attribute array*/
        if(!in_array($attribute, [self::EDIT, self::DELETE])){
            return false;
        }
        /* check subject is MicroPost*/
        if(!$subject instanceof MicroPost){
            return false;
        }
        return true;
    }
    
    protected function voteOnAttribute($attribute,$subject,TokenInterface $token) 
    {
        if($this->decisionManager->decide($token, [User::ROLE_ADMIN])) {
            return true;
        }
        $authenticatedUser = $token->getUser();
        
        /* Check authinicated user is Entity of User */
        if(!$authenticatedUser instanceof User){
            return false;
        }
        /* we have already check subiject above in support so just declare here we dont need again check 
        because first call supports function */
         $microPost = $subject;
        
         /* we would be verify if the user assigned to the micro post is actually the same user as ther currenly
         authenticated user which fetched can get user */
        return $microPost->getUser()->getId() === $authenticatedUser->getId();
    }
}
