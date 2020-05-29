<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Entity\MicroPost;
use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder) 
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    
    public function load(ObjectManager $manager)
    {
        /* First Add load users because we should add Reference in ReferenceRepository.php*/
        $this->loadUsers($manager);
        $this->loadMicroPosts($manager);
    }
    
    private function loadMicroPosts(ObjectManager $manager) 
    {
        for($i = 0;$i < 10; $i++) {
            $microPost = new MicroPost();
            $microPost->setText('Some random text '. rand(0,100));
            $microPost->setTime(new \DateTime('2018-03-15'));
            
            /* ManyToOne Relation for set user*/
            $microPost->setUser($this->getReference('pooja'));
            $manager->persist($microPost);
        }
        $manager->flush();
    }
    
    private function loadUsers(ObjectManager $manager) 
    {
        $user = new User();
        $user->setUsername('pooja');
        $user->setFullName('Pooja Patel');
        $user->setEmail('pp30111991@gmail.com');
        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user, 
                    'pooja123'
            )
        );
        
        /* Add Reference for OneToMany Relation */
        $this->addReference('pooja', $user);
        $manager->persist($user);
        $manager->flush();
    }
}
