<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $userPasswordEncoder;

    public function __construct(UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    public function load(ObjectManager $manager): void
    {
        $userAdmin = new User();
        $userSpellchecker = new User();
        $userWriter = new User();

        $userAdmin
            ->setUsername('admin')
            ->setRoles(['ROLE_ADMIN'])
            ->setEmail('admin@test.com')
        ;

        $userSpellchecker
            ->setUsername('spellchecker')
            ->setRoles(['ROLE_SPELLCHECKER'])
            ->setEmail('spellchecker@test.com')
        ;

        $userWriter
            ->setUsername('writer')
            ->setRoles(['ROLE_WRITER'])
            ->setEmail('writer@test.com')
        ;

        $pwd = $this->userPasswordEncoder->encodePassword($userAdmin, 'password');
        $userAdmin->setPassword($pwd);
        $userSpellchecker->setPassword($pwd);
        $userWriter->setPassword($pwd);

        $manager->persist($userAdmin);
        $manager->persist($userSpellchecker);
        $manager->persist($userWriter);
        $manager->flush();
    }
}
