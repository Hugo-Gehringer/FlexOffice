<?php

namespace App\Controller;

use App\Entity\Favorite;
use App\Entity\Space;
use App\Repository\FavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/favorites')]
class FavoriteController extends AbstractController
{
    #[Route('/', name: 'app_favorites_index', methods: ['GET'])]
    public function index(Request $request, FavoriteRepository $favoriteRepository, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        
        // Get user's favorites with pagination
        $queryBuilder = $favoriteRepository->findByUserQueryBuilder($user);
        
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            10 // 10 items per page as per user preference
        );

        return $this->render('favorite/index.html.twig', [
            'favorites' => $pagination,
            'currentUser' => $user,
        ]);
    }

    #[Route('/toggle/{id}', name: 'app_favorites_toggle', methods: ['POST'])]
    public function toggle(Space $space, FavoriteRepository $favoriteRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        
        // Check if the space is already favorited
        $existingFavorite = $favoriteRepository->findFavorite($user, $space);
        
        if ($existingFavorite) {
            // Remove from favorites
            $entityManager->remove($existingFavorite);
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'action' => 'removed',
                'message' => 'Espace retiré de vos favoris !'
            ]);
        } else {
            // Add to favorites
            $favorite = new Favorite();
            $favorite->setUser($user);
            $favorite->setSpace($space);
            
            $entityManager->persist($favorite);
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'action' => 'added',
                'message' => 'Espace ajouté à vos favoris !'
            ]);
        }
    }
}
