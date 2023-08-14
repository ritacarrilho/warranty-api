<?php

namespace App\DataFixtures;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Category;
use App\Entity\User;
use App\Entity\Consumer;
use App\Entity\Equipment;
use App\Entity\Warranty;
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
        $this->loadConsumers($manager);
        $this->loadEquipments($manager);
        $this->loadWarranties($manager);
        $manager->flush();
    }

    /* PRODUCT CATEGORY */
    public function loadCategories($manager)
    {
        for($i=0; $i < count($this->category); $i++) {
            $equip_category = new Category();
            $equip_category->setLabel($this->category[$i]);
                    
            $manager->persist($equip_category);
            $this->addReference('category-' . $i, $equip_category);
        }
    }

    /* USER LOGIN */
    public function loadUsers($manager)
    {
        $test_user = new User();
        $test_user->setEmail( "user@email.test")
                    ->setPassword( $this->pass_hasher->hashPassword($test_user, 'password' ))
                    ->setRoles( array("ROLE_USER") );

        $manager->persist($test_user);
        $this->addReference('user-' . 1, $test_user);

        $admin_user = new User();
        $admin_user->setEmail( "admin@email.test")
                    ->setPassword( $this->pass_hasher->hashPassword($admin_user, 'admin' ))
                    ->setRoles( array("ROLE_ADMIN") );

        $manager->persist($admin_user);
        $this->addReference('user-' . 2, $admin_user);
    }

    /* CONSUMERS INFO */
    public function loadConsumers($manager)
    {
        /* Association */
        $test_consumer = new Consumer();
        $test_consumer->setFirstName("User")
                        ->setLastName("Test")
                        ->setPhone("+33768451233")
                        ->setUserId($this->getReference('user-'. 1));

        $manager->persist($test_consumer);
        $this->addReference('consumer-' . 1, $test_consumer);


        $admin_consumer = new Consumer();
        $admin_consumer->setFirstName("Admin")
                        ->setLastName("Test")
                        ->setPhone("+33785412399")
                        ->setUserId($this->getReference('user-'. 2 ));

        $manager->persist($admin_consumer);
        $this->addReference('consumer-' . 2, $admin_consumer);
    }


    /* EQUIPMENTS */
    public function loadEquipments($manager)
    {
        $equip_one = new Equipment();
        $equip_one->setName("Iphone 13")
                ->setBrand("Apple")
                ->setModel("Iphone 13 blue")
                ->setSerialCode("455D4700TT")
                ->setPurchaseDate(new \DateTime('2022/04/06'))
                ->setCategoryId($this->getReference(('category-'. 6 )))
                ->setUserId($this->getReference('consumer-' . 2));
        
        $manager->persist($equip_one);
        $this->addReference('equipment-' . 1, $equip_one);

        $equip_two = new Equipment();
        $equip_two->setName("Iphone SE")
                ->setBrand("Apple")
                ->setModel("Iphone SE 2020")
                ->setSerialCode("455D4700TT")
                ->setPurchaseDate(new \DateTime('2021/11/23'))
                ->setCategoryId($this->getReference('category-'. 6))
                ->setUserId($this->getReference('consumer-' . 1));
        
        $manager->persist($equip_two);
        $this->addReference('equipment-' . 2, $equip_two);
    }

        /* EQUIPMENTS */
        public function loadWarranties($manager)
        {
            $warranty = new Warranty();
            $warranty->setReference("Iphone 13")
                    ->setStartDate(new \DateTime('2022/04/06'))
                    ->setEndDate(new \DateTime('2022/04/06'))
                    ->setIsActive(1)
                    ->setEquipmentId($this->getReference('equipment-' . 1));
            
            $manager->persist($warranty);
            $this->addReference('warranty-' . 1, $warranty);
        }
}
