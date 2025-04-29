<?php

namespace App\Twig\Components;

use App\Form\ProfileFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('profile_modal')]
class ProfileModal extends AbstractController
{
    public $profileForm;

    public function __construct(
        private FormFactoryInterface $formFactory,
        private Security $security
    ) {
    }

    public function mount(): void
    {
        $user = $this->security->getUser();
        $this->profileForm = $this->formFactory->create(ProfileFormType::class, $user)->createView();
    }
}