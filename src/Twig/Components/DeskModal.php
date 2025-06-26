<?php

namespace App\Twig\Components;

use App\Entity\Space;
use App\Form\DeskFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('desk_modal')]
class DeskModal extends AbstractController
{
    public $desk_form;
    public $space;

    public function __construct(
        private FormFactoryInterface $formFactory
    ) {
    }

    public function mount(Space $space): void
    {
        $this->space = $space;
        $desk = new \App\Entity\Desk();
        $desk->setSpace($space);
        $this->desk_form = $this->formFactory->create(DeskFormType::class, $desk)->createView();
    }
}
