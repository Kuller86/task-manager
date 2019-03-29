<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use App\Util\Util;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/project")
 */
class ProjectController extends AbstractController
{
    /**
     * @Route("/", name="project_list", methods={"GET"})
     */
    public function list(Request $request, ProjectRepository $projectRepository): Response
    {

        return $this->render('project/list.html.twig', [
            'projects' => $projectRepository->findByUser($this->getUser()),
        ]);
    }

    /**
     * @Route("/create", name="project_create", methods={"GET","POST"})
     */
    public function create(Request $request): Response
    {
        $project = new Project();
        $project->setStatus(0);
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $project->addUser($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($project);
            $entityManager->flush();

            $html = $this->renderView('project/project.ajax.html.twig', ['project' => $project]);
            return $this->json(array(
                'success' => true,
                'action' => 'create',
                'html' => $html,
                'data' => array(
                    'id' => $project->getId(),
                    'name' => $project->getName(),
                    'icon' => $project->getIcon()
                )
            ));
        }

        $errors = array();
        Util::fillFormErrors($form, $errors);

        $html = $this->renderView('project/new.ajax.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
        ]);
        return $this->json(array('success' => (bool)count($errors), 'data' => $html, 'errors' => $errors));
    }

    /**
     * @Route("/{id}/edit", name="project_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Project $project): Response
    {
        if (!$this->hasAccess($project)) {
            return $this->errorResponse(sprintf('User hasn\'t access to project "%s"', $project->getName()));
        }

        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->json(array('success' => true, 'action' => 'edit', 'data' => array('id' => $project->getId(), 'name' => $project->getName(), 'icon' => $project->getIcon())));
        }

        $errors = array();
        Util::fillFormErrors($form, $errors);

        $html = $this->renderView('project/edit.ajax.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
        ]);
        return $this->json(array('success' => (bool)count($errors), 'data' => $html, 'errors' => $errors));
    }

    /**
     * @Route("/{id}/confirm_delete", name="project_confirm_remove", methods={"GET"})
     */
    public function confirmRemove(Request $request, Project $project): Response
    {
        if (!$this->hasAccess($project)) {
            return $this->errorResponse(sprintf('User hasn\'t access to project "%s"', $project->getName()));
        }

        $html = $this->renderView('project/delete.ajax.html.twig', [
            'project' => $project,
        ]);

        $errors = array();

        return $this->json(array('success' => (bool)count($errors), 'html' => $html, 'errors' => $errors));
    }

    /**
     * @Route("/{id}", name="project_remove", methods={"DELETE"})
     */
    public function remove(Request $request, Project $project): Response
    {
        if (!$this->hasAccess($project)) {
            return $this->errorResponse(sprintf('User hasn\'t access to project "%s"', $project->getName()));
        }

        if ($this->isCsrfTokenValid('delete' . $project->getId(), $request->request->get('_token'))) {
            $data = array('id' => $project->getId());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($project);
            $entityManager->flush();

            return $this->json(array('success' => true, 'action' => 'delete', 'data' => $data));
        }

        $errors = array();

        return $this->json(array('success' => (bool)count($errors), 'errors' => $errors));
    }

    /**
     * @param Project $project
     * @return bool
     */
    private function hasAccess(Project $project)
    {
        $user = $this->getUser();
        $users = $project->getUsers();

        return $users->contains($user);
    }

    private function errorResponse($mesasge = '')
    {
        $html = $this->renderView('modal_error.ajax.html.twig', [
            'message' => $mesasge,
        ]);
        return $this->json(array('success' => false, 'html' => $html));
    }
}
