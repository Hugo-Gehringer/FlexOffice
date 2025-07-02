<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Space;
use App\Form\SpaceFormType;
use App\Repository\AvailabilityRepository;
use App\Repository\SpaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ReservationRepository;

#[Route('/space')]
class SpaceController extends AbstractController
{
    #[Route('/', name: 'app_space_index', methods: ['GET'])]
    public function index(SpaceRepository $spaceRepository): Response
    {
        // Ensure user is authenticated
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('space/index.html.twig', [
            'spaces' => $spaceRepository->findAll(),
            'currentUser' => $this->getUser(),
        ]);
    }

    #[Route('/new', name: 'app_space_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Ensure user is authenticated and has HOST role
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $space = new Space();
        $space->setHost($user);

        // Create a new Address instance
        $address = new Address();
        $space->setAddress($address);

        $form = $this->createForm(SpaceFormType::class, $space);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Make sure the address is properly linked to the space
            $address = $space->getAddress();
            // Persist the address first
            $entityManager->persist($address);
            // Then persist the space
            $entityManager->persist($space);
            $entityManager->flush();

            flash()->success('Your space has been created successfully!');
            return $this->redirectToRoute('app_space_show', ['id' => $space->getId()]);
        }

        return $this->render('space/new.html.twig', [
            'space_form' => $form->createView(),
            'currentUser' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_space_edit', methods: ['GET', 'POST', 'PATCH'])]
    public function edit(Request $request, Space $space, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        if (!$this->isGranted('ROLE_ADMIN') && $space->getHost() !== $user) {
            throw $this->createAccessDeniedException('You can only edit your own spaces.');
        }
        $form = $this->createForm(SpaceFormType::class, $space);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();
            $this->addFlash('success', 'Space updated successfully!');

            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('app_admin_spaces');
            } else {
                return $this->redirectToRoute('app_space_show', ['id' => $space->getId()]);
            }
        }

        return $this->render('space/edit.html.twig', [
            'space' => $space,
            'space_form' => $form->createView(),
            'currentUser' => $user,
        ]);
    }

    #[Route('/my-spaces', name: 'app_my_spaces', methods: ['GET'])]
    public function mySpaces(SpaceRepository $spaceRepository): Response
    {
        // Ensure user is authenticated and has HOST role
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        return $this->render('space/my_spaces.html.twig', [
            'spaces' => $spaceRepository->findByHost($user),
            'currentUser' => $user,
        ]);
    }

    #[Route('/{id}', name: 'app_space_show', methods: ['GET'])]
    public function show(Space $space, AvailabilityRepository $availabilityRepository, ReservationRepository $reservationRepository): Response
    {
        // Ensure user is authenticated
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Get availability for this space
        $availability = $space->getAvailability();

        // Get booked dates for each desk
        $bookedDates = [];
        foreach ($space->getDesks() as $desk) {
            // Get existing reservations for this desk (excluding cancelled ones)
            $existingReservations = $reservationRepository->createQueryBuilder('r')
                ->select('r.reservationDate')
                ->where('r.desk = :desk')
                ->andWhere('r.status != :cancelledStatus') // Exclude cancelled reservations
                ->setParameter('desk', $desk)
                ->setParameter('cancelledStatus', 2) // 2 = cancelled
                ->getQuery()
                ->getResult();

            // Format the dates for JavaScript
            $deskBookedDates = [];
            foreach ($existingReservations as $existingReservation) {
                if ($existingReservation['reservationDate'] instanceof \DateTimeInterface) {
                    $deskBookedDates[] = $existingReservation['reservationDate']->format('Y-m-d');
                }
            }

            $bookedDates[$desk->getId()] = $deskBookedDates;
        }

        return $this->render('space/show.html.twig', [
            'space' => $space,
            'desks' => $space->getDesks(),
            'availability' => $availability,
            'currentUser' => $this->getUser(),
            'booked_dates' => json_encode($bookedDates),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_space_delete', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, Space $space, EntityManagerInterface $entityManager): Response
    {
        // Ensure user is authenticated
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        // Check if user is admin or the host of this space
        if (!$this->isGranted('ROLE_ADMIN') && $space->getHost() !== $user) {
            throw $this->createAccessDeniedException('You can only delete your own spaces.');
        }

        // Verify CSRF token
        if ($this->isCsrfTokenValid('delete_space', $request->request->get('_token'))) {
            try {
                $entityManager->remove($space);
                $entityManager->flush();

                flash()->success('Space deleted successfully!');
            } catch (\Exception $e) {
                flash()->error('An error occurred while deleting the space. Please try again.');
            }
        } else {
            flash()->error('Invalid CSRF token. Please try again.');
        }

        // Redirect based on user role
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_admin_spaces');
        } else {
            return $this->redirectToRoute('app_my_spaces');
        }
    }
}
