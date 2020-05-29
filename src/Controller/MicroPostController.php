<?php

namespace App\Controller;

use App\Entity\MicroPost;
use App\Form\MicroPostType;
use App\Entity\User;
use App\Repository\MicroPostRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
/**
* @Route("/micro-post")
 */
class MicroPostController
{
    /**
     * @var \Twig_Environment
     */
    private $twig;
    /**
     * @var MicroPostRepository
     */
    private $microPostRepository;
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;
    
    /**
     * @var EntityManagerInterface $entityManager
     */
    private $entityManager;
    
    /**
     * @var RouterInterface $router
     */
    private $router;
    
    /**
     * @var FlashBagInterface $flashBag
     */
    private $flashBag;
    
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;


    public function __construct(
            \Twig\Environment $twig, 
            MicroPostRepository $microPostRepository,
            FormFactoryInterface $formFactory,
            EntityManagerInterface $entityManager,
            RouterInterface $router,
            FlashBagInterface $flashBag,
            AuthorizationCheckerInterface $authorizationChecker
            
        ) 
    {
        $this->twig = $twig;
        $this->microPostRepository = $microPostRepository;
        $this->formFactory= $formFactory;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->flashBag = $flashBag;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @Route("/", name="micro_post_index")
     */
    /* get the Post list By date*/ 
    /* here call token storage for featch current user because here MicroPostController class not extends
        Controller class */
    public function index(TokenStorageInterface $tokenStorage, UserRepository $userRepository) 
    {  
        $currentUser = $tokenStorage->getToken()
                ->getUser();
        $usersToFollow = [];
        
        /* Check current user authenticated or not */
        if($currentUser instanceof User) {
            /* show following user post only e.g pooja following tirth so here pooja can see tirth's Post*/
            
            /* Automatically featch data from database using getFollowing() method */
            $posts = $this->microPostRepository->findAllByUsers(
                    $currentUser->getFollowing()
                );
            $usersToFollow = count($posts) === 0 ?
                $userRepository->findAllWithMoreThan5PostsExceptUser(
                    $currentUser
                ) : [];
        } else {
            $posts = $this->microPostRepository->findBy(
                    [],
                    ['time'=>'DESC']);
        }
        $html = $this->twig->render(
            'micro-post/index.html.twig',
            [
                'posts' => $posts,
                'usersToFollow' => $usersToFollow,
            ]
        );
        return new Response($html);
    }
    
    /**
     * @Route("/edit/{id}", name="micro_post_edit")
     * @Security("is_granted('edit', microPost)", message="Access denied")
     */
    public function edit(MicroPost $microPost, Request $request) 
    {
        //$this->denyUnlessGranted('edit',$microPost);
//        if(!$this->authorizationChecker->isGranted('edit', $microPost)) {
//            throw new UnauthorizedHttpException();
//        }
        $form = $this->formFactory->create(MicroPostType::class,$microPost);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($microPost);
            $this->entityManager->flush();
            
            return new RedirectResponse(
                $this->router->generate('micro_post_index')
            );
        }
        
        $html = $this->twig->render('micro-post/add.html.twig',
                ['form'=>$form->createView()]
            );
        return new Response($html);
    }
    /**
     * @Route("/delete/{id}", name="micro_post_delete")
     * @Security("is_granted('delete', microPost)", message="Access denied")
     */
    public function delete(MicroPost $microPost) 
    {
        $this->entityManager->remove($microPost);
        $this->entityManager->flush();
        
        $this->flashBag->add('notice','Micro post was deleted');
        
        return new RedirectResponse(
            $this->router->generate('micro_post_index')
        );
    }

    /**
     * @Route("/add", name="micro_post_add")
     * @Security("is_granted('ROLE_USER')")
     */
    /* For register user can add Post without error if not regiestr then go to login page direct*/
    public function add(Request $request, TokenStorageInterface $tokenStorage) 
    {
       /* For register user can add Post without error if not regiestr then go to login page direct*/
        $user = $tokenStorage->getToken()->getUser();
        
        $microPost = new MicroPost();
        
        /* This time comment because we set setTimeOnPersist() method in micropost entity. and that method call 
        automatically date*/
        //$microPost->setTime(new \DateTime());
        $microPost->setUser($user);
        
        $form = $this->formFactory->create(MicroPostType::class,$microPost);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($microPost);
            $this->entityManager->flush();
            
            return new RedirectResponse($this->router->generate('micro_post_index'));
        }
        
        $html = $this->twig->render('micro-post/add.html.twig',
                ['form'=>$form->createView()]
            );
        return new Response($html);
    }
   
    /**
     * @Route("/user/{username}", name="micro_post_user")
     */
    /* user wise show post click on username show all post them*/
    public function userPosts(User $userWithPosts) 
    {
        $html = $this->twig->render(
            'micro-post/user-posts.html.twig',
            [
                /* Both method we can do it  this one First */
                'posts' => $this->microPostRepository->findBy(
                    ['user' => $userWithPosts],
                    ['time'=>'DESC']
                ),
                'user' => $userWithPosts,
//               /* Second 2*/ 
                //'posts' =>$userWithPosts->getPosts(),
            ]
        );
        return new Response($html);
    }
    /**
     * @Route("/{id}", name="micro_post_post")
     */
    /*get the individual post by id*/ 
    public function post(MicroPost $post)
    {
        //$post = $this->microPostRepository->find($id);
        $html = $this->twig->render('micro-post/post.html.twig',
                [
                    'post' => $post
                ]
            );        
        return new Response($html);
    }
}