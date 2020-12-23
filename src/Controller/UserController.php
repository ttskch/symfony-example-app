<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\UserChangePasswordType;
use App\Form\UserDeleteType;
use App\Form\UserEditType;
use App\Form\UserProfileEditType;
use App\Form\UserType;
use App\Pagination\Criteria\UserCriteria;
use App\Pagination\Form\UserSearchType;
use App\Pagination\Paginator\UserPaginator;
use App\Repository\UserRepository;
use App\Routing\ReturnToAwareControllerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Ttskch\PaginatorBundle\Context;

/**
 * @Route("/user", name="user_")
 */
class UserController extends AbstractController
{
    use ReturnToAwareControllerTrait;

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
     * @Route("/profile", name="profile_show", methods={"GET"})
     */
    public function profileShow(): Response
    {
        return $this->render('user/profile_show.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    /**
     * @Route("/profile/edit", name="profile_edit", methods={"GET", "POST"})
     */
    public function profileEdit(Request $request): Response
    {
        $form = $this->createForm(UserProfileEditType::class, $user = $this->getUser());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash('success', 'Profile is successfully updated.');

            return $this->redirectToRouteOrReturn('user_profile_show');
        }

        return $this->render('user/profile_edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/profile/change_password", name="profile_change_password", methods={"GET", "POST"})
     */
    public function profileChangePassword(Request $request): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(UserChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->plainPassword = $form->get('newPassword')->getData();

            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash('success', 'Password is successfully updated.');

            return $this->redirectToRouteOrReturn('user_profile_show');
        }

        return $this->render('user/profile_change_password.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(Context $context, UserPaginator $paginator): Response
    {
        $context->initialize(
            'u.id',
            [$paginator, 'sliceByCriteria'],
            [$paginator, 'countByCriteria'],
            UserCriteria::class,
            UserSearchType::class,
        );

        return $this->render('user/index.html.twig', [
            'slice' => $context->slice,
            'form' => $context->form->createView(),
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

            return $this->redirectToRouteOrReturn('user_index');
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
     * @IsGranted("EDIT", subject="user", statusCode=403)
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'User is successfully updated.');

            return $this->redirectToRouteOrReturn('user_show', ['id' => $user->getId()]);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/change_password", name="change_password", methods={"GET", "POST"})
     * @IsGranted("EDIT", subject="user", statusCode=403)
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

            return $this->redirectToRouteOrReturn('user_show', ['id' => $user->getId()]);
        }

        return $this->render('user/change_password.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete", methods={"GET", "DELETE"})
     * @IsGranted("DELETE", subject="user", statusCode=403)
     */
    public function delete(Request $request, User $user): Response
    {
        $form = $this->createForm(UserDeleteType::class, null, [
            'self' => $user,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var User $alternateUser */
            $alternateUser = ($alternateUserId = $form->get('alternateUser')->getData())
                ? $this->repository->find($alternateUserId)
                : null
            ;

            if (!$alternateUser) {
                throw new \RuntimeException();
            }

            // if the user owns some related contents, let the alternate user own them.
            // nothing for now.

            $this->em->remove($user);
            $this->em->flush();

            $this->addFlash('success', 'User is successfully deleted.');

            return $this->redirectToRouteOrReturn('user_index');
        }

        return $this->render('user/delete.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
