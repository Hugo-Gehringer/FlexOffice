<?php

namespace App\Controller;

use App\Entity\Desk;
use App\Entity\Space;
use App\Form\DeskFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/desk')]
class DeskController extends AbstractController
{
    #[Route('/new/{id}', name: 'app_desk_new', methods: ['GET', 'POST'])]
    #[IsGranted(new Expression("is_granted('ROLE_HOST') or is_granted('ROLE_ADMIN')"))]

    public function new(Request $request, Space $space, EntityManagerInterface $entityManager): Response
    {
        if ($space->getHost() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only add desks to your own spaces.');
        }

        $desk = new Desk();
        $desk->setSpace($space);

        $form = $this->createForm(DeskFormType::class, $desk);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($desk);
            $entityManager->flush();

            $this->addFlash('success', 'Bureau créé avec succès !');

            return $this->redirectToRoute('app_space_show', ['id' => $space->getId()]);
        }

        // Render the regular form page
        return $this->render('desk/new.html.twig', [
            'desk_form' => $form->createView(),
            'space' => $space
        ]);
    }
}
