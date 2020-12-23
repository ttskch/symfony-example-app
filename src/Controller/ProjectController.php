<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Project;
use App\EntityConstant\ProjectConstant;
use App\Form\ProjectType;
use App\Pagination\Criteria\ProjectCriteria;
use App\Pagination\Form\ProjectSearchType;
use App\Pagination\Paginator\ProjectPaginator;
use App\Repository\ProjectRepository;
use App\Routing\ReturnToAwareControllerTrait;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Ttskch\PaginatorBundle\Context;

/**
 * @Route("/project", name="project_")
 */
class ProjectController extends AbstractController
{
    use ReturnToAwareControllerTrait;

    private EntityManagerInterface $em;
    private ProjectRepository $repository;

    public function __construct(EntityManagerInterface $em, ProjectRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;
    }

    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(Context $context, ProjectPaginator $paginator): Response
    {
        $context->initialize(
            'p.id',
            [$paginator, 'sliceByCriteria'],
            [$paginator, 'countByCriteria'],
            ProjectCriteria::class,
            ProjectSearchType::class,
        );

        return $this->render('project/index.html.twig', [
            'slice' => $context->slice,
            'form' => $context->form->createView(),
        ]);
    }

    /**
     * @Route("/new", name="new", methods={"GET", "POST"})
     * @IsGranted("ROLE_ALLOWED_TO_EDIT")
     */
    public function new(Request $request): Response
    {
        $project = new Project();
        $project->state = ProjectConstant::getValidStates()[0];

        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($project);
            $this->em->flush();

            $this->addFlash('success', 'Project is successfully added.');

            return $this->redirectToRouteOrReturn('project_index');
        }

        return $this->render('project/new.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function show(Project $project): Response
    {
        return $this->render('project/show.html.twig', [
            'project' => $project,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_ALLOWED_TO_EDIT")
     */
    public function edit(Request $request, Project $project): Response
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'Project is successfully updated.');

            return $this->redirectToRouteOrReturn('project_show', ['id' => $project->getId()]);
        }

        return $this->render('project/edit.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     * @IsGranted("ROLE_ALLOWED_TO_EDIT")
     */
    public function delete(Request $request, Project $project): Response
    {
        if ($this->isCsrfTokenValid('delete'.$project->getId(), $request->request->get('_token'))) {
            $this->em->remove($project);

            try {
                $this->em->flush();
                $this->addFlash('success', 'Project is successfully deleted.');
            } catch (ForeignKeyConstraintViolationException $e) {
                $this->addFlash('danger', 'Project cannot be deleted because it owns some related contents.');
            }
        }

        return $this->redirectToRouteOrReturn('project_index');
    }
}
