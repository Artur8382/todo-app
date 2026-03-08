<?php

namespace App\Controller;

use App\Entity\Todo;
use App\Filter\TodoFilter;
use App\Form\TodoFilterType;
use App\Form\TodoType;
use App\Repository\TodoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dom\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;



final class TodoController extends AbstractController
{

    public function __construct(
        private readonly TodoRepository $todoRepository,
        private readonly EntityManagerInterface $em,
    ) {}

    #[Route('/todo', name: 'app_todo')]
    public function index(Request $request): Response
    {
        $filter = new TodoFilter();
        $form = $this->createForm(TodoFilterType::class, $filter);
        $form->handleRequest($request);

        $todos = $this->todoRepository->findByFilter($filter, $this->getUser());

        return $this->render('todo/index.html.twig', [
            'todos' => $todos,
            'filterForm' => $form,
        ]);
    }

    #[Route('/todo/new', name: 'app_todo_new')]
    public function new(Request $request): Response
    {
        $todo = new Todo();
        $form = $this->createForm(TodoType::class, $todo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $todo->setOwner($this->getUser());
            $this->em->persist($todo);
            $this->em->flush();

            $this->addFlash('success', 'Todo created!');
            return $this->redirectToRoute('app_todo');
        }

        return $this->render('todo/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/todo/{id}/edit', name: 'app_todo_edit')]
    public function edit(Todo $todo, Request $request): Response
    {
        if ($todo->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(TodoType::class, $todo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $todo->setUpdatedAt(new \DateTimeImmutable());
            $this->em->flush();

            $this->addFlash('success', 'Todo updated!');
            return $this->redirectToRoute('app_todo');
        }

        return $this->render('todo/edit.html.twig', [
            'form' => $form,
            'todo' => $todo,
        ]);
    }

    #[Route('/todo/{id}/delete', name: 'app_todo_delete', methods: ['POST'])]
    public function delete(Todo $todo, Request $request): Response
    {
        if ($todo->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $todo->getId(), $request->request->get('_token'))) {
            $this->em->remove($todo);
            $this->em->flush();
            $this->addFlash('success', 'Todo deleted!');
        }

        return $this->redirectToRoute('app_todo');
    }

    #[Route('/todo/{id}/status', name: 'app_todo_status', methods: ['POST'])]
    public function toggleStatus(Todo $todo, Request $request): Response
    {
        if ($todo->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('status' . $todo->getId(), $request->request->get('_token'))) {
            $todo->toggleStatus();
            $this->em->flush();
        }

        return $this->redirectToRoute('app_todo');
    }
}
