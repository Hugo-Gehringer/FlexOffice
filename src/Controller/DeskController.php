<?php

namespace App\Controller;

use App\Entity\Desk;
use App\Entity\Space;
use App\Form\DeskFormType;
use Doctrine\ORM\EntityManagerInterface;
use Flasher\Prime\FlasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/desk')]
class DeskController extends AbstractController
{
    #[Route('/new/{id}', name: 'app_desk_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Space $space, EntityManagerInterface $entityManager): Response
    {
        // Ensure user is authenticated and has HOST role
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_HOST', null, 'You must be a host to create desks');

        // Ensure the user is the host of the space
        if ($space->getHost() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only add desks to spaces you host');
        }

        $desk = new Desk();
        $desk->setSpace($space);

        // Create the form
        $form = $this->createForm(DeskFormType::class, $desk);

        // Handle form submission
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            flash()->info('Form was submitted');

            if ($form->isValid()) {
                flash()->info('Form is valid');

                // Form is valid, save the desk
                $entityManager->persist($desk);
                $entityManager->flush();

                flash()->success('Your desk has been created successfully!');

                if ($request->headers->get('Accept') === 'text/vnd.turbo-stream.html') {
                    // Return Turbo Stream response
                    return $this->render('desk/_success_turbo_stream.html.twig', [
                        'desks' => $space->getDesks(),
                    ]);
                }

                return $this->redirectToRoute('app_space_show', ['id' => $space->getId()]);
            } else {
                // Form is not valid
                flash()->error('There are errors in your form. Please check and try again.');

                // Get form errors for debugging
                foreach ($form->getErrors(true) as $error) {
                    flash()->error($error->getMessage());
                }
            }
        }

        // If it's an AJAX request, return just the modal content
        if ($request->isXmlHttpRequest()) {
            return $this->render('components/desk_modal.html.twig', [
                'desk_form' => $form->createView(),
                'space' => $space
            ]);
        }

        // Otherwise redirect to the space page
        return $this->redirectToRoute('app_space_show', ['id' => $space->getId()]);
    }
}
