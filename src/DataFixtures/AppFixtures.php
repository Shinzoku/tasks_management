<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\TaskList;
use App\Entity\Task;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(ManagerRegistry $doctrine, UserPasswordHasherInterface $hasher)
    {
        $this->doctrine = $doctrine;
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadTaskLists($manager);
        $this->loadTasks($manager);
    }

    public function loadUsers(ObjectManager $manager): void
    {
        // Admin
        $user = new User();
        $user->setEmail('admin@example.com');
        $user->setRoles(['ROLE_ADMIN']);
        $password = $this->hasher->hashPassword($user, '123');
        $user->setPassword($password);
        $user->setFirstname('nicolas');
        $user->setLastname('bernon');
        $user->setCreatedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2024-01-01 09:00:00'));
        $user->setUpdatedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2024-01-01 09:00:00'));
        $user->setVerified(true);

        $manager->persist($user);

        $userDatas = [
            [
                'email' => 'foo.foo@example.com',
                'roles' => ['ROLE_USER'],
                'password' => '123',
                'firstname' => 'foo',
                'lastname' => 'foo',
                'createdAt' => DateTime::createFromFormat('Y-m-d H:i:s', '2024-01-01 10:00:00'),
                'updatedAt' => DateTime::createFromFormat('Y-m-d H:i:s', '2024-01-01 10:00:00'),
                'verified' => true
            ],
            [
                'email' => 'bar.bar@example.com',
                'roles' => ['ROLE_USER'],
                'password' => '123',
                'firstname' => 'bar',
                'lastname' => 'bar',
                'createdAt' => DateTime::createFromFormat('Y-m-d H:i:s', '2024-01-01 10:00:00'),
                'updatedAt' => DateTime::createFromFormat('Y-m-d H:i:s', '2024-01-01 10:00:00'),
                'verified' => true
            ],
            [
                'email' => 'baz.baz@example.com',
                'roles' => ['ROLE_USER'],
                'password' => '123',
                'firstname' => 'baz',
                'lastname' => 'baz',
                'createdAt' => DateTime::createFromFormat('Y-m-d H:i:s', '2024-01-01 10:00:00'),
                'updatedAt' => DateTime::createFromFormat('Y-m-d H:i:s', '2024-01-01 10:00:00'),
                'verified' => true
            ],
        ];

        foreach ($userDatas as $userData) {
            $user = new User();
            $user->setEmail($userData['email']);
            $user->setRoles($userData['roles']);
            $password = $this->hasher->hashPassword($user, $userData['password']);
            $user->setPassword($password);
            $user->setFirstname($userData['firstname']);
            $user->setLastname($userData['lastname']);
            $user->setCreatedAt($userData['createdAt']);
            $user->setUpdatedAt($userData['updatedAt']);
            $user->setVerified($userData['verified']);

            $manager->persist($user);
        }

        $manager->flush();
    }

    public function loadTaskLists(ObjectManager $manager)
    {
        $repository = $this->doctrine->getRepository(User::class);
        $users = $repository->findAll();

        $taskList = new TaskList();
        $taskList->setName('Name of the list');
        $taskList->setDescription('Description of the list');
        $taskList->setCreatedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2024-01-01 09:00:00'));
        $taskList->setUpdatedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2024-01-01 09:00:00'));
        $taskList->setUser($users[0]);

        $manager->persist($taskList);

        $manager->flush();
    }

    public function loadTasks(ObjectManager $manager)
    {
        $repository = $this->doctrine->getRepository(User::class);
        $users = $repository->findAll();

        $repository = $this->doctrine->getRepository(TaskList::class);
        $taskLists = $repository->findAll();

        $task = new Task();
        $task->setTitle('Title of the task 1');
        $task->setDescription('Description of the task 1');
        $task->setCompleted(false);
        $task->setProgress(0);
        $task->setCreatedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2024-01-01 09:00:00'));
        $task->setUpdatedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2024-01-01 09:00:00'));
        $task->setTaskList($taskLists[0]);
        $task->setUser($users[1]);

        $manager->persist($task);

        $task = new Task();
        $task->setTitle('Title of the task 2');
        $task->setDescription('Description of the task 2');
        $task->setCompleted(false);
        $task->setProgress(0);
        $task->setCreatedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2024-01-01 09:00:00'));
        $task->setUpdatedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2024-01-01 09:00:00'));
        $task->setTaskList($taskLists[0]);
        $task->setUser($users[2]);

        $manager->persist($task);

        $task = new Task();
        $task->setTitle('Title of the task 3');
        $task->setDescription('Description of the task 3');
        $task->setCompleted(false);
        $task->setProgress(0);
        $task->setCreatedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2024-01-01 09:00:00'));
        $task->setUpdatedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2024-01-01 09:00:00'));
        $task->setTaskList($taskLists[0]);
        $task->setUser($users[3]);

        $manager->persist($task);

        $task = new Task();
        $task->setTitle('Title of the task 4');
        $task->setDescription('Description of the task 4');
        $task->setCompleted(false);
        $task->setProgress(0);
        $task->setCreatedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2024-01-01 09:00:00'));
        $task->setUpdatedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2024-01-01 09:00:00'));
        $task->setTaskList($taskLists[0]);

        $manager->persist($task);

        $manager->flush();
    }

}
