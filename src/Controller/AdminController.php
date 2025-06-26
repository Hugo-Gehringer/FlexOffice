<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Space;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_dashboard', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Ensure user is authenticated and has ADMIN role
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'You must be an admin to access this page');

        // Get counts for dashboard
        $userCount = $entityManager->getRepository(User::class)->count([]);
        $spaceCount = $entityManager->getRepository(Space::class)->count([]);
        $reservationCount = $entityManager->getRepository(Reservation::class)->count([]);

        return $this->render('admin/dashboard.html.twig', [
            'userCount' => $userCount,
            'spaceCount' => $spaceCount,
            'reservationCount' => $reservationCount,
            'currentUser' => $this->getUser(),
        ]);
    }

    #[Route('/users', name: 'app_admin_users', methods: ['GET'])]
    public function users(EntityManagerInterface $entityManager): Response
    {
        // Ensure user is authenticated and has ADMIN role
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'You must be an admin to access this page');

        $users = $entityManager->getRepository(User::class)->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'currentUser' => $this->getUser(),
        ]);
    }

    #[Route('/spaces', name: 'app_admin_spaces', methods: ['GET'])]
    public function spaces(EntityManagerInterface $entityManager): Response
    {
        // Ensure user is authenticated and has ADMIN role
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'You must be an admin to access this page');

        $spaces = $entityManager->getRepository(Space::class)->findAll();

        return $this->render('admin/spaces.html.twig', [
            'spaces' => $spaces,
            'currentUser' => $this->getUser(),
        ]);
    }

    #[Route('/spaces/{id}/delete', name: 'app_admin_space_delete', methods: ['POST', 'DELETE'])]
    public function deleteSpace(Request $request, Space $space, EntityManagerInterface $entityManager): Response
    {
        // Ensure user is authenticated and has ADMIN role
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'You must be an admin to access this page');

        // Verify CSRF token
        if ($this->isCsrfTokenValid('delete_space', $request->request->get('_token'))) {
            try {
                // Delete the space (this will cascade to desks and their reservations)
                $entityManager->remove($space);
                $entityManager->flush();

                $this->addFlash('success', sprintf('Space "%s" has been successfully deleted.', $space->getName()));
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred while deleting the space. Please try again.');
            }
        } else {
            $this->addFlash('error', 'Invalid CSRF token. Please try again.');
        }

        return $this->redirectToRoute('app_admin_spaces');
    }

    #[Route('/reservations', name: 'app_admin_reservations', methods: ['GET'])]
    public function reservations(EntityManagerInterface $entityManager): Response
    {
        // Ensure user is authenticated and has ADMIN role
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'You must be an admin to access this page');

        $reservations = $entityManager->getRepository(Reservation::class)->findAll();

        return $this->render('admin/reservations.html.twig', [
            'reservations' => $reservations,
            'currentUser' => $this->getUser(),
        ]);
    }
}
