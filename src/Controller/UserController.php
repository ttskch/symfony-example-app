<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\UserChangePasswordType;
use App\Form\UserEditType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Ttskch\PaginatorBundle\Context;
use Ttskch\PaginatorBundle\Doctrine\Counter;
use Ttskch\PaginatorBundle\Doctrine\Slicer;

/**
 * @Route("/user", name="user_")
 */
class UserController extends AbstractController
{
    private EntityManagerInterface $em;
    private UserRepository $repository;

    public function __construct(EntityManagerInterface $em, UserRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home_index');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(): Response
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(Context $context): Response
    {
        $qb = $this->repository->createQueryBuilder('u');
        $context->initialize('id', new Slicer($qb), new Counter($qb));

        return $this->render('user/index.html.twig', [
            'slice' => $context->slice,
        ]);
    }

    /**
     * @Route("/new", name="new", methods={"GET", "POST"})
     * @IsGranted("ROLE_ALLOWED_TO_EDIT_USER")
     */
    public function new(Request $request): Response
    {
        $form = $this->createForm(UserType::class, $user = new User());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash('success', 'User is successfully added.');

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_ALLOWED_TO_EDIT_USER")
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'User is successfully updated.');

            return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/change_password", name="change_password", methods={"GET", "POST"})
     * @IsGranted("ROLE_ALLOWED_TO_EDIT_USER")
     */
    public function changePassword(Request $request, User $user): Response
    {
        $form = $this->createForm(UserChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->plainPassword = $form->get('newPassword')->getData();

            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash('success', 'Password is successfully updated.');

            return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
        }

        return $this->render('user/change_password.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     * @IsGranted("ROLE_ALLOWED_TO_EDIT_USER")
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $this->em->remove($user);

            try {
                $this->em->flush();
                $this->addFlash('success', 'User is successfully deleted.');
            } catch (ForeignKeyConstraintViolationException $e) {
                $this->addFlash('danger', 'User cannot be deleted because it owns some related contents.');
            }
        }

        return $this->redirectToRoute('user_index');
    }
}
