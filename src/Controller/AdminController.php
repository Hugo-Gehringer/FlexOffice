<?php

namespace App\Controller;

use App\Entity\Desk;
use App\Entity\Reservation;
use App\Entity\Space;
use App\Entity\User;
use App\Form\UserEditFormType;
use App\Form\ReservationEditFormType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
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
    public function users(Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
    {
        // Ensure user is authenticated and has ADMIN role
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'You must be an admin to access this page');

        // Créer une requête pour récupérer tous les utilisateurs
        $queryBuilder = $entityManager->getRepository(User::class)->createQueryBuilder('u')
            ->orderBy('u.lastname', 'ASC')
            ->addOrderBy('u.firstname', 'ASC');

        // Paginer les résultats
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1), // Numéro de page, par défaut 1
            10 // Nombre d'éléments par page
        );

        return $this->render('admin/users.html.twig', [
            'users' => $pagination,
            'currentUser' => $this->getUser(),
        ]);
    }

    #[Route('/spaces', name: 'app_admin_spaces', methods: ['GET'])]
    public function spaces(Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
    {
        // Ensure user is authenticated and has ADMIN role
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'You must be an admin to access this page');

        // Créer une requête pour récupérer tous les espaces avec leurs hôtes
        $queryBuilder = $entityManager->getRepository(Space::class)->createQueryBuilder('s')
            ->leftJoin('s.host', 'h')
            ->addSelect('h')
            ->orderBy('s.name', 'ASC');

        // Paginer les résultats
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1), // Numéro de page, par défaut 1
            10 // Nombre d'éléments par page
        );

        return $this->render('admin/spaces.html.twig', [
            'spaces' => $pagination,
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
    public function reservations(Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
    {
        // Ensure user is authenticated and has ADMIN role
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'You must be an admin to access this page');

        // Créer une requête pour récupérer toutes les réservations avec les relations
        $queryBuilder = $entityManager->getRepository(Reservation::class)->createQueryBuilder('r')
            ->leftJoin('r.guest', 'g')
            ->leftJoin('r.desk', 'd')
            ->leftJoin('d.space', 's')
            ->addSelect('g', 'd', 's')
            ->orderBy('r.reservationDate', 'DESC');

        // Paginer les résultats
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1), // Numéro de page, par défaut 1
            10 // Nombre d'éléments par page
        );

        return $this->render('admin/reservations.html.twig', [
            'reservations' => $pagination,
            'currentUser' => $this->getUser(),
        ]);
    }

    #[Route('/desks', name: 'app_admin_desks', methods: ['GET'])]
    public function desks(Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
    {
        // Ensure user is authenticated and has ADMIN role
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'You must be an admin to access this page');

        // Créer une requête pour récupérer tous les bureaux avec leurs espaces
        $queryBuilder = $entityManager->getRepository(Desk::class)->createQueryBuilder('d')
            ->leftJoin('d.space', 's')
            ->addSelect('s')
            ->orderBy('s.name', 'ASC')
            ->addOrderBy('d.name', 'ASC');

        // Paginer les résultats
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1), // Numéro de page, par défaut 1
            10 // Nombre d'éléments par page
        );

        return $this->render('admin/desks.html.twig', [
            'desks' => $pagination,
            'currentUser' => $this->getUser(),
        ]);
    }

    #[Route('/users/{id}/delete', name: 'app_admin_user_delete', methods: ['POST', 'DELETE'])]
    public function deleteUser(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        // Ensure user is authenticated and has ADMIN role
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'You must be an admin to access this page');

        // Prevent admin from deleting themselves
        if ($user === $this->getUser()) {
            $this->addFlash('error', 'You cannot delete your own account.');
            return $this->redirectToRoute('app_admin_users');
        }

        // Verify CSRF token
        if ($this->isCsrfTokenValid('delete_user', $request->request->get('_token'))) {
            try {
                $entityManager->remove($user);
                $entityManager->flush();

                $this->addFlash('success', sprintf('User "%s %s" has been successfully deleted.', $user->getFirstname(), $user->getLastname()));
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred while deleting the user. Please try again.');
            }
        } else {
            $this->addFlash('error', 'Invalid CSRF token. Please try again.');
        }

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/users/{id}/edit', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function editUser(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        // Ensure user is authenticated and has ADMIN role
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'You must be an admin to access this page');

        $form = $this->createForm(UserEditFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', sprintf('User "%s %s" has been successfully updated.', $user->getFirstname(), $user->getLastname()));
            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('user/user_edit.html.twig', [
            'user' => $user,
            'user_form' => $form->createView(),
            'currentUser' => $this->getUser(),
        ]);
    }
}
