<?php

namespace App\Controller;

use App\Entity\Desk;
use App\Entity\Reservation;
use App\Form\ReservationFormType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservationRepository->findBy(['guest' => $user], ['reservationDate' => 'DESC']),
            'currentUser' => $user,
        ]);
    }

    #[Route('/new/{id}', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Desk $desk, EntityManagerInterface $entityManager, ReservationRepository $reservationRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$desk->isAvailable()) {
            flash()->error('Ce bureau n\'est pas disponible.');
            return $this->redirectToRoute('app_space_show', ['id' => $desk->getSpace()->getId()]);
        }

        $user = $this->getUser();
        $reservation = $this->createNewReservation($desk, $user);
        $form = $this->createForm(ReservationFormType::class, $reservation);
        $form->handleRequest($request);

        $bookedDates = $this->getFormattedBookedDates($desk, $reservationRepository);

        if ($form->isSubmitted()) {
            return $this->processReservationForm($form, $desk, $reservation, $entityManager, $reservationRepository);
        }

        return $this->render('reservation/new.html.twig', [
            'reservation_form' => $form->createView(),
            'desk' => $desk,
            'currentUser' => $user,
            'booked_dates' => json_encode($bookedDates),
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->ensureUserOwnsReservation($reservation);

        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
            'currentUser' => $this->getUser(),
        ]);
    }

    #[Route('/{id}/cancel', name: 'app_reservation_cancel', methods: ['GET', 'POST'])]
    public function cancel(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->ensureUserOwnsReservation($reservation);

        $reservation->setStatus(ReservationStatus::CANCELLED);
        $entityManager->flush();

        flash()->success('Your reservation has been cancelled successfully!');
        return $this->redirectToRoute('app_reservation_index');
    }

    #[Route('/{id}/delete', name: 'app_reservation_delete', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->ensureUserCanDeleteReservation($reservation);

        if (!$this->isCsrfTokenValid('delete_reservation', $request->request->get('_token'))) {
            flash()->error('Invalid CSRF token. Please try again.');
            return $this->getDeleteRedirectRoute();
        }

        try {
            $entityManager->remove($reservation);
            $entityManager->flush();
            flash()->success('Reservation deleted successfully!');
        } catch (\Exception $e) {
            flash()->error('An error occurred while deleting the reservation. Please try again.');
        }

        return $this->getDeleteRedirectRoute();
    }

    private function createNewReservation(Desk $desk, $user): Reservation
    {
        $reservation = new Reservation();
        $reservation->setDesk($desk);
        $reservation->setGuest($user);
        $reservation->setStatus(ReservationStatus::PENDING);

        return $reservation;
    }

    private function getFormattedBookedDates(Desk $desk, ReservationRepository $reservationRepository): array
    {
        $reservations = $reservationRepository->createQueryBuilder('r')
            ->select('r.reservationDate')
            ->where('r.desk = :desk')
            ->andWhere('r.status != :cancelledStatus')
            ->setParameter('desk', $desk)
            ->setParameter('cancelledStatus', ReservationStatus::CANCELLED)
            ->getQuery()
            ->getResult();

        return array_map(
            fn($reservation) => $reservation['reservationDate']->format('Y-m-d'),
            array_filter($reservations, fn($reservation) => $reservation['reservationDate'] instanceof \DateTimeInterface)
        );
    }

    private function processReservationForm($form, Desk $desk, Reservation $reservation, EntityManagerInterface $entityManager, ReservationRepository $reservationRepository): Response
    {
        if (!$form->isValid()) {
            return $this->handleFormErrors($form, $desk);
        }

        $this->normalizeReservationDate($reservation);
        $validationErrors = $this->getReservationValidationErrors($desk, $reservation->getReservationDate(), $reservationRepository);

        if (!empty($validationErrors)) {
            return $this->handleValidationErrors($validationErrors, $desk);
        }

        $entityManager->persist($reservation);
        $entityManager->flush();

        flash()->success('Votre réservation a bien été créée!');
        return $this->redirectToRoute('app_reservation_index');
    }

    private function normalizeReservationDate(Reservation $reservation): void
    {
        $reservationDate = $reservation->getReservationDate();

        if (!is_string($reservationDate)) {
            return;
        }

        $dateTime = \DateTime::createFromFormat('Y-m-d', $reservationDate) ?: new \DateTime($reservationDate);
        $dateTime->setTime(12, 0, 0);
        $reservation->setReservationDate($dateTime);
    }

    private function getReservationValidationErrors(Desk $desk, $date, ReservationRepository $reservationRepository): array
    {
        $errors = [];
        $normalizedDate = $this->normalizeDate($date);

        if (!$normalizedDate) {
            return ['Invalid date format.'];
        }

        $errors = array_merge($errors, $this->validateDateNotInPast($normalizedDate));
        $errors = array_merge($errors, $this->validateDeskAvailabilityForDay($desk, $normalizedDate));
        $errors = array_merge($errors, $this->validateNoExistingReservation($desk, $normalizedDate, $reservationRepository));

        return $errors;
    }

    private function normalizeDate($date): ?\DateTime
    {
        if ($date instanceof \DateTimeInterface) {
            return $date;
        }

        if (is_string($date)) {
            $dateTime = \DateTime::createFromFormat('Y-m-d', $date) ?: new \DateTime($date);
            $dateTime->setTime(12, 0, 0);
            return $dateTime;
        }

        return null;
    }

    private function validateDateNotInPast(\DateTime $date): array
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        return $date < $today ? ['The reservation date must be in the future.'] : [];
    }

    private function validateDeskAvailabilityForDay(Desk $desk, \DateTime $date): array
    {
        $availability = $desk->getSpace()->getAvailability();
        $dayOfWeek = strtolower($date->format('l'));
        $isAvailableMethod = 'is' . ucfirst($dayOfWeek);

        if (!method_exists($availability, $isAvailableMethod) || !$availability->$isAvailableMethod()) {
            return ['The desk is not available on ' . ucfirst($dayOfWeek) . 's.'];
        }

        return [];
    }

    private function validateNoExistingReservation(Desk $desk, \DateTime $date, ReservationRepository $reservationRepository): array
    {
        $existingReservation = $reservationRepository->createQueryBuilder('r')
            ->where('r.desk = :desk')
            ->andWhere('r.reservationDate = :date')
            ->andWhere('r.status != :cancelledStatus')
            ->setParameter('desk', $desk)
            ->setParameter('date', $date)
            ->setParameter('cancelledStatus', ReservationStatus::CANCELLED)
            ->getQuery()
            ->getOneOrNullResult();

        return $existingReservation ? ['This desk is already booked for the selected date.'] : [];
    }

    private function handleFormErrors($form, Desk $desk): Response
    {
        foreach ($form->getErrors(true) as $error) {
            flash()->error($error->getMessage());
        }

        return $this->redirectToSpaceShow($desk);
    }

    private function handleValidationErrors(array $errors, Desk $desk): Response
    {
        foreach ($errors as $error) {
            flash()->error($error);
        }

        return $this->redirectToSpaceShow($desk);
    }

    private function redirectToSpaceShow(Desk $desk): Response
    {
        return $this->redirectToRoute('app_space_show', ['id' => $desk->getSpace()->getId()]);
    }

    private function ensureUserOwnsReservation(Reservation $reservation): void
    {
        if ($reservation->getGuest() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot access this reservation');
        }
    }

    private function ensureUserCanDeleteReservation(Reservation $reservation): void
    {
        if ($reservation->getGuest() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You cannot delete this reservation');
        }
    }

    private function getDeleteRedirectRoute(): Response
    {
        return $this->isGranted('ROLE_ADMIN')
            ? $this->redirectToRoute('app_admin_reservations')
            : $this->redirectToRoute('app_reservation_index');
    }
}

// Classe de constantes pour les statuts de réservation
class ReservationStatus
{
    public const PENDING = 0;
    public const CONFIRMED = 1;
    public const CANCELLED = 2;
}