<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\TaskList;
use App\Entity\Task;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TestFixtures extends Fixture
{
    public function __construct(ManagerRegistry $doctrine, UserPasswordHasherInterface $hasher)
    {
        $this->doctrine = $doctrine;
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        $this->loadUsers($manager, $faker);
        $this->loadTasklists($manager, $faker);
        $this->loadTasks($manager, $faker);
    }

    public function loadUsers(ObjectManager $manager, FakerGenerator $faker): void
    {
        for ($i = 0; $i < 10; $i++) { 
            $user = new User();
            $user->setEmail($faker->freeEmail());
            $user->setRoles(['ROLE_USER']);
            $password = $this->hasher->hashPassword($user, '123');
            $user->setPassword($password);
            $user->setFirstname($faker->firstName($gender = 'male'|'female'));
            $user->setLastname($faker->lastName($gender = 'male'|'female'));
            $user->setVerified(true);
            $date = $date = $faker->dateTimeThisYear();
            $date = DateTime::createFromInterface($date);
            $user->setCreatedAt($date);
            $user->setUpdatedAt($date);

            $manager->persist($user);
        }

        $manager->flush();
    }

    public function loadTaskLists(ObjectManager $manager, FakerGenerator $faker)
    {
        $repository = $this->doctrine->getRepository(User::class);
        $users = $repository->findAll();

        for ($i=0; $i < 5; $i++) { 
            $taskList = new TaskList();
            $taskList->setName($faker->sentence(3));
            $taskList->setDescription($faker->text(50));
            $taskList->setCreatedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2024-01-01 09:00:00'));
            $taskList->setUpdatedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2024-01-01 09:00:00'));
            $taskList->setUser($users[0]);
            
            $manager->persist($taskList);

            $manager->flush();
        }
    }

    public function loadTasks(ObjectManager $manager, FakerGenerator $faker)
    {
        $repository = $this->doctrine->getRepository(User::class);
        $users = $repository->findAll();

        $repository = $this->doctrine->getRepository(TaskList::class);
        $taskLists = $repository->findAll();

        for ($i=0; $i < 50; $i++) { 
            $task = new Task();
            $task->setTitle($faker->sentence(3));
            $task->setDescription($faker->text(50));
            
            // Randomly determines the progress
            $percent = $faker->numberBetween(0, 100);
            $task->setProgress($percent);
            
            // if the progress egal 100, the task is complete
            if ($percent === 100){
                $task->setCompleted(true);
            } else {
                $task->setCompleted(false);
            }

            $task->setCreatedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2024-01-01 09:00:00'));
            $task->setUpdatedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2024-01-01 09:00:00'));

            // Randomly determines a list
            $list = $faker->numberBetween(0, 5);
            $task->setTaskList($taskLists[$list]);

            // Randomly assign a user
            $dev = $faker->numberBetween(1, 11);
            $task->setUser($users[$dev]);
            
            $manager->persist($task);
        }

        $manager->flush();
    }
}
