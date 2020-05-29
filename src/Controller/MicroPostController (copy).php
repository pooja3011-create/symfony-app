<?php

namespace App\Controller;

use App\Entity\MicroPost;
use App\Form\MicroPostType;
use App\Repository\MicroPostRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;
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
     * 
     * @var RouterInterface $router
     */
    private $router;

    public function __construct(
            \Twig\Environment $twig, 
            MicroPostRepository $microPostRepository,
            FormFactoryInterface $formFactory,
            EntityManagerInterface $entityManager,
            RouterInterface $router
        ) 
    {
        $this->twig = $twig;
        $this->microPostRepository = $microPostRepository;
        $this->formFactory= $formFactory;
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    /**
     * @Route("/", name="micro_post_index")
     */
    public function index() {
        //this one is find post default
//        $html = $this->twig->render(
//            'micro-post/index.html.twig',
//            [
//                'posts' => $this->microPostRepository->findAll()
//            ]
//        );
        //this one is find post By Date(latest post)
        $html = $this->twig->render(
            'micro-post/index.html.twig',
            [
                'posts' => $this->microPostRepository->findBy([],['time'=>'DESC']),
            ]
        );
        return new Response($html);
    }
    
    /**
     * @Route("/{id}", name="micro_post_post")
     */
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

    /**
     * @Route("/add", name="micro_post_add")
     */
    public function add(Request $request) 
    {
        $microPost = new MicroPost;
        $microPost->setTime(new \DateTime());
        
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
    
    
}