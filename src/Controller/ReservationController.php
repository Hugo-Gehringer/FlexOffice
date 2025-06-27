<?php

namespace App\Controller;

use App\Entity\Desk;
use App\Entity\Reservation;
use App\Form\ReservationFormType;
use App\Form\ReservationEditFormType;
use App\Form\ReservationUserEditFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ReservationRepository;
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
            'reservations' => $reservationRepository->findBy(['guest' => $user], ['reservationDate' => 'DESC']),
            'currentUser' => $user,
        ]);
    }

    #[Route('/new/{id}', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Desk $desk, EntityManagerInterface $entityManager, ReservationRepository $reservationRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_GUEST', null, 'Vous devez être invité pour réserver.');

        if (!$desk->isAvailable()) {
            flash()->error('Ce bureau n\'est pas disponible.');
            return $this->redirectToRoute('app_space_show', ['id' => $desk->getSpace()->getId()]);
        }

        $user = $this->getUser();
        $reservation = new Reservation();
        $reservation->setDesk($desk);
        $reservation->setGuest($user);
        $reservation->setStatus(0);

        $form = $this->createForm(ReservationFormType::class, $reservation, [
            'desk' => $desk,
        ]);
        $form->handleRequest($request);

        // Récupération des dates déjà réservées
        $existingReservations = $reservationRepository->createQueryBuilder('r')
            ->select('r.reservationDate')
            ->where('r.desk = :desk')
            ->andWhere('r.status != :cancelledStatus')
            ->setParameter('desk', $desk)
            ->setParameter('cancelledStatus', 2)
            ->getQuery()
            ->getResult();

        $bookedDates = array_map(fn($r) => $r['reservationDate'] instanceof \DateTimeInterface ? $r['reservationDate']->format('Y-m-d') : null, $existingReservations);

        $isTurbo = $request->headers->get('Accept') === 'text/vnd.turbo-stream.html' || $request->request->get('turbo') === '1';

        if ($form->isSubmitted()) {
            // Dump the form data for debugging
            dump($form->getData());

            if ($form->isValid()) {
                // Ensure the reservation date is a DateTime object
                $reservationDate = $reservation->getReservationDate();
                if (is_string($reservationDate)) {
                    // Create DateTime object without timezone conversion
                    $reservationDate = \DateTime::createFromFormat('Y-m-d', $reservationDate);
                    if ($reservationDate === false) {
                        // Fallback to regular DateTime creation
                        $reservationDate = new \DateTime($reservationDate);
                    }
                    // Set time to noon to avoid timezone issues
                    $reservationDate->setTime(12, 0, 0);
                    $reservation->setReservationDate($reservationDate);
                }

                $errors = $this->validateReservationDate($desk, $reservation->getReservationDate(), $reservationRepository);
                if (empty($errors)) {
                    $entityManager->persist($reservation);
                    $entityManager->flush();

                    // Always redirect to reservation index after successful form submission
                    flash()->success('Votre réservation a bien été créée!');
                    return $this->redirectToRoute('app_reservation_index');
                } else {
                    foreach ($errors as $error) {
                        flash()->error($error);
                    }

                    // Always redirect to space show page for errors
                    return $this->redirectToRoute('app_space_show', ['id' => $desk->getSpace()->getId()]);
                }
            } else {
                // Erreurs de formulaire Symfony
                foreach ($form->getErrors(true) as $error) {
                    flash()->error($error->getMessage());
                }

                // Always redirect to space show page for errors
                return $this->redirectToRoute('app_space_show', ['id' => $desk->getSpace()->getId()]);
            }
        }

        if ($isTurbo) {
            return $this->render('shared/components/reservation_modal.html.twig', [
                'desk' => $desk,
                'bookedDates' => $bookedDates,
                'reservation_form' => $form->createView(),
            ]);
        }

        return $this->render('reservation/new.html.twig', [
            'reservation_form' => $form->createView(),
            'desk' => $desk,
            'currentUser' => $user,
            'booked_dates' => json_encode($bookedDates),
        ]);
    }


    /**
     * Validate the reservation date
     *
     * @param Desk $desk The desk to check
     * @param \DateTimeInterface $date The date to check
     * @param ReservationRepository $reservationRepository The repository to check for existing reservations
     * @return array An array of error messages, empty if valid
     */
    private function validateReservationDate(Desk $desk, $date, ReservationRepository $reservationRepository): array
    {
        $errors = [];

        // Convert string date to DateTime if needed
        if (is_string($date)) {
            $date = \DateTime::createFromFormat('Y-m-d', $date);
            if ($date === false) {
                $date = new \DateTime($date);
            }
            // Set time to noon to avoid timezone issues
            $date->setTime(12, 0, 0);
        }

        if (!$date instanceof \DateTimeInterface) {
            $errors[] = 'Invalid date format.';
            return $errors;
        }

        // Check if the date is in the past
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        if ($date < $today) {
            $errors[] = 'The reservation date must be in the future.';
        }

        // Check if the desk is available on this date
        $availability = $desk->getSpace()->getAvailability();
        $dayOfWeek = strtolower($date->format('l')); // Get day name (monday, tuesday, etc.)
        $isAvailableMethod = 'is' . ucfirst($dayOfWeek);

        if (!method_exists($availability, $isAvailableMethod) || !$availability->$isAvailableMethod()) {
            $errors[] = 'The desk is not available on ' . ucfirst($dayOfWeek) . 's.';
        }

        // Check if there's already a reservation for this desk on this date
        $existingReservation = $reservationRepository->createQueryBuilder('r')
            ->where('r.desk = :desk')
            ->andWhere('r.reservationDate = :date')
            ->andWhere('r.status != :cancelledStatus') // Exclude cancelled reservations
            ->setParameter('desk', $desk)
            ->setParameter('date', $date)
            ->setParameter('cancelledStatus', 2) // 2 = cancelled
            ->getQuery()
            ->getOneOrNullResult();

        if ($existingReservation) {
            $errors[] = 'This desk is already booked for the selected date.';
        }

        return $errors;
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

    #[Route('/{id}/delete', name: 'app_reservation_delete', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        // Ensure user is authenticated
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        // Ensure the user can only delete their own reservations or is admin
        if ($reservation->getGuest() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You cannot delete this reservation');
        }

        // Verify CSRF token
        if ($this->isCsrfTokenValid('delete_reservation', $request->request->get('_token'))) {
            try {
                $entityManager->remove($reservation);
                $entityManager->flush();

                flash()->success('Reservation deleted successfully!');
            } catch (\Exception $e) {
                flash()->error('An error occurred while deleting the reservation. Please try again.');
            }
        } else {
            flash()->error('Invalid CSRF token. Please try again.');
        }

        // Redirect based on user role
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_admin_reservations');
        } else {
            return $this->redirectToRoute('app_reservation_index');
        }
    }
}
