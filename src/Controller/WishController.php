<?php

namespace App\Controller;

use App\Entity\Wish;
use App\Form\WishType;
use App\Repository\WishRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/wishes')]
final class WishController extends AbstractController
{
    #[Route('/list/{page}', name: 'app_wishes_list', requirements: ['page' => '\d+'], methods: ['GET'])]
    public function showList(WishRepository $wishRepository, int $page, ParameterBagInterface $parameters): Response
    {
        $nbPerPage = $parameters->get('wish')['nb_max'];

        $wishes = $wishRepository->findBy(
            ['isPublished' => true],
            ['dateCreated' => 'DESC'],
            $nbPerPage,
            ($page - 1) * $nbPerPage
        );

        $totalWishes = $wishRepository->count(['isPublished' => true]);
        $totalPages = ceil($totalWishes / $nbPerPage);

        return $this->render('main/wish-list.html.twig', [
            'wishes' => $wishes,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    #[Route('/list-custom', name: 'app_wishes_list_custom')]
    public function showWishCustom(WishRepository $wishRepository): Response
    {
        $wishes = $wishRepository->findWishesCustom();

        return $this->render('main/wish-list.html.twig', [
            'wishes' => $wishes,
            'page' => 1,
            'totalPages' => 10
        ]);
    }

    #[Route('/{id}', name: 'app_wish_details', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function showDetail(Wish $wish, Request $request): Response
    {

        $page = $request->query->getInt('page', 1);

        return $this->render('main/wish-details.html.twig', [
            'wish' => $wish,
            'page' => $page
        ]);
    }

    #[Route('/create', name: 'app_wish_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $wish = new Wish();

        $form = $this->createForm(WishType::class, $wish);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $entityManager->persist($wish);
            $entityManager->flush();

            $this->addFlash('success', 'Wish created successfully');

            return $this->redirectToRoute('app_wish_details', ['id' => $wish->getId()]);
        }
        return $this->render('wish/edit.html.twig', [
            'wish_form' => $form
        ]);
    }

    #[Route('/update/{id}', name: 'app_wish_update', requirements: ['id' => '\d+'])]
    public function update(Request $request, Wish $wish, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(WishType::class, $wish);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $entityManager->flush();

            $this->addFlash('success', 'Wish updated successfully');

            return $this->redirectToRoute('app_wish_details', ['id' => $wish->getId()]);
        }
        return $this->render('wish/edit.html.twig', [
            'wish_form' => $form
        ]);
    }

    #[Route('/delete/{id}', name: 'app_wish_delete', requirements: ['id' => '\d+'])]
    public function delete(Wish $wish, EntityManagerInterface $entityManager, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete'.$wish->getId(), $request->get('token'))) {
            $entityManager->remove($wish);
            $entityManager->flush();

            $this->addFlash('success', 'Wish deleted successfully');
        } else {
            $this->addFlash('error', 'Error while deleting the wish.');
        }
        return $this->redirectToRoute('app_wishes_list', [
            'page' => 1
        ]);
    }
}
