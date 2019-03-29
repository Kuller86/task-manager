<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use App\Util\Util;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/task")
 */
class TaskController extends AbstractController
{
    /**
     * @Route("/create", name="task_create", methods={"POST"})
     */
    public function create(Request $request)
    {
        $task = new Task();
        $task->setStatus(0);
        $task->setPosition(999);

        $form = $this->createForm(TaskType::class, $task, array('user' => $this->getUser()));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($task);
            $entityManager->flush();

            $html = $this->renderView('task/_row.ajax.html.twig', [
                'task' => $task
            ]);
            return $this->json(array('success' => true, 'html' => $html));
        }

        $errors = array();
        Util::fillFormErrors($form, $errors);
        return $this->json(array('success' => false, 'errors' => $errors));
    }

    /**
     * @Route("/{id}/update", name="task_update", methods={"GET","POST"})
     */
    public function update(Request $request, Task $task): Response
    {
        if (!$this->hasAccess($task)) {
            return $this->errorResponse(sprintf('User hasn\'t access to task "%s"', $task->getName()));
        }

        $form = $this->createForm(TaskType::class, $task, array('user' => $this->getUser()));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $html = $this->renderView('task/_row.ajax.html.twig', [
                'task' => $task,
            ]);
            return $this->json(array('success' => true, 'action' => 'update', 'html' => $html, 'data' => array('id' => $task->getId(), 'projectId' => $task->getProject()->getId())));
        }

        $html = $this->renderView('task/edit.ajax.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);

        $errors = array();
        Util::fillFormErrors($form, $errors);

        return $this->json(array('success' => (bool)count($errors), 'html' => $html, 'errors' => $errors));
    }

    /**
     * @Route("/{id}/confirm_delete", name="task_confirm_remove", methods={"GET"})
     */
    public function confirmRemove(Request $request, Task $task): Response
    {
        if (!$this->hasAccess($task)) {
            return $this->errorResponse(sprintf('User hasn\'t access to task "%s"', $task->getName()));
        }

        $html = $this->renderView('task/delete.ajax.html.twig', [
            'task' => $task,
        ]);

        $errors = array();

        return $this->json(array('success' => (bool)count($errors), 'html' => $html, 'errors' => $errors));
    }

    /**
     * @Route("/{id}", name="task_remove", methods={"DELETE"})
     */
    public function remove(Request $request, Task $task): Response
    {
        if (!$this->hasAccess($task)) {
            return $this->errorResponse(sprintf('User hasn\'t access to task "%s"', $task->getName()));
        }

        if ($this->isCsrfTokenValid('delete' . $task->getId(), $request->request->get('_token'))) {
            $data = array('id' => $task->getId());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($task);
            $entityManager->flush();

            return $this->json(array('success' => true, 'action' => 'delete', 'data' => $data));
        }

        $errors = array();

        return $this->json(array('success' => (bool)count($errors), 'errors' => $errors));
    }

    /**
     * @param Task $task
     * @return bool
     */
    private function hasAccess(Task $task)
    {
        $user = $this->getUser();
        $project = $task->getProject();
        $users = $project->getUsers();

        return $users->contains($user);
    }

    /**
     * @param string $mesasge
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    private function errorResponse($mesasge = '')
    {
        $html = $this->renderView('modal_error.ajax.html.twig', [
            'message' => $mesasge,
        ]);

        return $this->json(array('success' => false, 'html' => $html));
    }
}
