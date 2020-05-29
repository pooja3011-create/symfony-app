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
        $this->loadMicroPosts($manager);
        $this->loadUsers($manager);
    }
    
    private function loadMicroPosts(ObjectManager $manager) 
    {
        for($i = 0;$i < 10; $i++) {
            $microPost = new MicroPost();
            $microPost->setText('Some random text '. rand(0,100));
            $microPost->setTime(new \DateTime('2018-03-15'));
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
        $manager->persist($user);
        $manager->flush();
    }
}
