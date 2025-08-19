<?php

namespace App\Controller;

use App\Entity\Wish;
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
    public function showDetail(int $id, WishRepository $wishRepository, Request $request): Response
    {
        $wish = $wishRepository->find($id);

        if (!$wish) {
            throw $this->createNotFoundException('Wish not found, go search somewhere else dude');
        }

        $page = $request->query->getInt('page', 1);

        return $this->render('main/wish-details.html.twig', [
            'wish' => $wish,
            'page' => $page
        ]);
    }
}
