<?php

namespace App\Tests\Controller;

use App\Entity\Todo;
use App\Entity\User;
use App\Enum\TodoStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class TodoControllerTest extends WebTestCase
{
   private function createUser(EntityManagerInterface $em, UserPasswordHasherInterface $hasher, string $email): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setPassword($hasher->hashPassword($user, 'password123'));
        $em->persist($user);
        $em->flush();

        return $user;
    }

    public function testUnauthenticatedRedirectsToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/todo');

        self::assertResponseRedirects('/login');
    }

    public function testAuthenticatedUserSeesTodoPage(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = $this->createUser($em, $hasher, 'test@test.com');
        $client->loginUser($user);
        $client->request('GET', '/todo');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'My Todos');
    }

    public function testCreateTodo(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = $this->createUser($em, $hasher, 'create@test.com');
        $client->loginUser($user);

        $client->request('GET', '/todo/new');
        $client->submitForm('Create', [
            'todo[title]' => 'Test Todo',
            'todo[description]' => 'Test description',
            'todo[status]' => TodoStatus::OPEN->value,
        ]);

        self::assertResponseRedirects('/todo');
        $client->followRedirect();
        self::assertSelectorTextContains('.list-group', 'Test Todo');
    }

    public function testUserCannotSeeOtherUserTodos(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $userA = $this->createUser($em, $hasher, 'usera@test.com');
        $userB = $this->createUser($em, $hasher, 'userb@test.com');

        $todo = new Todo();
        $todo->setTitle('User A secret todo');
        $todo->setStatus(TodoStatus::OPEN);
        $todo->setOwner($userA);
        $em->persist($todo);
        $em->flush();

        $client->loginUser($userB);
        $client->request('GET', '/todo/' . $todo->getId() . '/edit');

        self::assertResponseStatusCodeSame(403);
    }


}
