<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Space;
use App\Form\SpaceFormType;
use App\Repository\SpaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
        // Ensure user is authenticated
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

            $this->addFlash('success', 'Your space has been created successfully!');

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

    #[Route('/{id}', name: 'app_space_show', methods: ['GET'])]
    public function show(Space $space): Response
    {
        // Ensure user is authenticated
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('space/show.html.twig', [
            'space' => $space,
            'desks' => $space->getDesks(),
            'currentUser' => $this->getUser(),
        ]);
    }
}
