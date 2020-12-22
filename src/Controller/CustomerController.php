<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Customer;
use App\EntityConstant\CustomerConstant;
use App\Form\CustomerType;
use App\Repository\CustomerRepository;
use App\Routing\ReturnToAwareControllerTrait;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Ttskch\PaginatorBundle\Context;
use Ttskch\PaginatorBundle\Doctrine\Counter;
use Ttskch\PaginatorBundle\Doctrine\Slicer;

/**
 * @Route("/customer", name="customer_")
 */
class CustomerController extends AbstractController
{
    use ReturnToAwareControllerTrait;

    private EntityManagerInterface $em;
    private CustomerRepository $repository;

    public function __construct(EntityManagerInterface $em, CustomerRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;
    }

    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(Context $context): Response
    {
        $qb = $this->repository->createQueryBuilder('c');
        $context->initialize('id', new Slicer($qb), new Counter($qb));

        return $this->render('customer/index.html.twig', [
            'slice' => $context->slice,
        ]);
    }

    /**
     * @Route("/new", name="new", methods={"GET", "POST"})
     * @IsGranted("ROLE_ALLOWED_TO_EDIT")
     */
    public function new(Request $request): Response
    {
        $customer = new Customer();
        $customer->state = CustomerConstant::getValidStates()[0];

        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($customer);
            $this->em->flush();

            $this->addFlash('success', 'Customer is successfully added.');

            return $this->redirectToRouteOrReturn('customer_index');
        }

        return $this->render('customer/new.html.twig', [
            'customer' => $customer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function show(Customer $customer): Response
    {
        return $this->render('customer/show.html.twig', [
            'customer' => $customer,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_ALLOWED_TO_EDIT")
     */
    public function edit(Request $request, Customer $customer): Response
    {
        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'Customer is successfully updated.');

            return $this->redirectToRouteOrReturn('customer_show', ['id' => $customer->getId()]);
        }

        return $this->render('customer/edit.html.twig', [
            'customer' => $customer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     * @IsGranted("ROLE_ALLOWED_TO_EDIT")
     */
    public function delete(Request $request, Customer $customer): Response
    {
        if ($this->isCsrfTokenValid('delete'.$customer->getId(), $request->request->get('_token'))) {
            $this->em->remove($customer);

            try {
                $this->em->flush();
                $this->addFlash('success', 'Customer is successfully deleted.');
            } catch (ForeignKeyConstraintViolationException $e) {
                $this->addFlash('danger', 'Customer cannot be deleted because it owns some related contents.');
            }
        }

        return $this->redirectToRouteOrReturn('customer_index');
    }
}
