<?php

namespace App\DataFixtures;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
/**
 * @var
 */
    public $category;
    private $pass_hasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->pass_hasher = $passwordHasher;
        $this->category = ["Large Appliances", "Kitchen & Cooking", "Home", "Beauty & Health", "TV & Image & Sound", "Computer", "Smartphone", "Console", "Connected Objects"];
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadCategories($manager);
        $this->loadUsers($manager);
        $manager->flush();
    }

    /* PRODUCT CATEGORY */
    public function loadCategories($manager)
    {
        for($i=0; $i < count($this->category); $i++) {
            $equip_category = new Category();
            $equip_category->setLabel($this->category[$i]);
                    
            $manager->persist($equip_category);
            // $this->addReference('prod_category-' . $i, $prod_category);
        }
    }

        /* PRODUCT CATEGORY */
        public function loadUsers($manager)
        {
            /* Association */
            $test_user = new User();
            $test_user->setEmail( "user@email.test")
                        ->setPassword( $this->pass_hasher->hashPassword($test_user, 'password' ))
                        ->setRoles( array("ROLE_USER") );

            $manager->persist($test_user);

            $admin_user = new User();
            $admin_user->setEmail( "admin@email.test")
                        ->setPassword( $this->pass_hasher->hashPassword($admin_user, 'admin' ))
                        ->setRoles( array("ROLE_ADMIN") );

            $manager->persist($admin_user);
        }
}
