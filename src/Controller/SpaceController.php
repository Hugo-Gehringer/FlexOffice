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
        $this->denyAccessUnlessGranted('ROLE_HOST', null, 'You must be a host to create spaces');

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

            // L'availability est maintenant gérée automatiquement par le formulaire
            // grâce au by_reference = false

            // Persist the address first
            $entityManager->persist($address);
            // Then persist the space
            $entityManager->persist($space);
            $entityManager->flush();

            flash()->success('Your space has been created successfully!');

            if ($request->headers->get('Accept') === 'text/vnd.turbo-stream.html') {
                // Handle Turbo Drive request
                return new Response(null, 303, ['Location' => $this->generateUrl('app_space_show', ['id' => $space->getId()])]);
            }

            return $this->redirectToRoute('app_space_show', ['id' => $space->getId()]);
        }

        return $this->render('space/new.html.twig', [
            'space_form' => $form->createView(),
            'currentUser' => $user,
        ]);
    }

    #[Route('/my-spaces', name: 'app_my_spaces', methods: ['GET'])]
    public function mySpaces(SpaceRepository $spaceRepository): Response
    {
        // Ensure user is authenticated and has HOST role
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_HOST', null, 'You must be a host to view your spaces');

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
}
