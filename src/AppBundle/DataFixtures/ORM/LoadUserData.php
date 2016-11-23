<?php
/**
 * Created by PhpStorm.
 * User: tsv
 * Date: 23.11.16
 * Time: 11:44
 */

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\User;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setName('Владимир Царевников');
        $user->setBalance(100500.22);
        $manager->persist($user);
        $manager->flush();

        $user = new User();
        $user->setName('Иванов Иван Иванович');
        $user->setBalance(100.22);
        $manager->persist($user);
        $manager->flush();

        $user = new User();
        $user->setName('Петров Петр Петрович');
        $user->setBalance(10230.22);
        $manager->persist($user);
        $manager->flush();

        $user = new User();
        $user->setName('Сидоров Сидор Сидорович');
        $user->setBalance(10230.22);
        $manager->persist($user);
        $manager->flush();

    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 1;
    }
}