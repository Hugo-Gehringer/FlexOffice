<?php

namespace App\Controller;

use App\Entity\Desk;
use App\Entity\Reservation;
use App\Form\ReservationFormType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Flasher\Prime\FlasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reservation')]
class ReservationController extends AbstractController
{
    #[Route('/', name: 'app_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): Response
    {
        // Ensure user is authenticated and has GUEST role
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_GUEST', null, 'You must be a guest to view reservations');

        $user = $this->getUser();

        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservationRepository->findBy(['guest' => $user]),
            'currentUser' => $user,
        ]);
    }

    #[Route('/new/{id}', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Desk $desk, EntityManagerInterface $entityManager): Response
    {
        // Ensure user is authenticated and has GUEST role
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_GUEST', null, 'You must be a guest to make reservations');

        $user = $this->getUser();
        $reservation = new Reservation();
        $reservation->setDesk($desk);
        $reservation->setGuest($user);
        $reservation->setStatus(0); // 0 = pending, 1 = confirmed, 2 = cancelled

        $form = $this->createForm(ReservationFormType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reservation);
            $entityManager->flush();

            flash()->success('Your reservation has been created successfully!');

            return $this->redirectToRoute('app_reservation_index');
        }

        return $this->render('reservation/new.html.twig', [
            'reservation_form' => $form->createView(),
            'desk' => $desk,
            'currentUser' => $user,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        // Ensure user is authenticated
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        // Ensure the user can only see their own reservations
        if ($reservation->getGuest() !== $user) {
            throw $this->createAccessDeniedException('You cannot view this reservation');
        }

        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
            'currentUser' => $user,
        ]);
    }

    #[Route('/{id}/cancel', name: 'app_reservation_cancel', methods: ['GET', 'POST'])]
    public function cancel(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        // Ensure user is authenticated
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        // Ensure the user can only cancel their own reservations
        if ($reservation->getGuest() !== $user) {
            throw $this->createAccessDeniedException('You cannot cancel this reservation');
        }

        $reservation->setStatus(2); // 2 = cancelled
        $entityManager->flush();

        flash()->success('Your reservation has been cancelled successfully!');

        return $this->redirectToRoute('app_reservation_index', [
            'currentUser' => $user,
        ]);
    }
}
