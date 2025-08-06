<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ProfileFormType;
use App\Form\UserEditFormType;
use Doctrine\ORM\EntityManagerInterface;
use Flasher\Prime\FlasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig');
    }

    #[Route('/profile/edit', name: 'app_profile_edit', methods: ['GET', 'PATCH'])]
    public function profileEdit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('User must be logged in');
        }

        $form = $this->createForm(UserEditFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            flash()->success('Profile updated successfully!');
            $referer = $request->headers->get('referer');
        }

        return $this->render('user/user_edit.html.twig', [
            'user' => $user,
            'user_form' => $form->createView(),
        ]);
    }
}