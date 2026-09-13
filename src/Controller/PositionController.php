<?php

namespace App\Controller;

use App\Repository\PositionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class PositionController extends AbstractController {

    public function __construct(
        private PositionRepository $positionRepository) {
    }

    #[Route(path: "/position", name: "app_positions")]
    public function index() {
        return $this->render("position/index.html.twig", [

        ]);
    }

    #[Route(path: "/position/show/{id}", name: "app_position_show")]
    public function show(int $id) {
        $position = $this->positionRepository->find($id);
        if (!$position) {
            throw $this->createNotFoundException('The position does not exist');
        }
        return $this->render("position/show.html.twig", [
            // "position"=> $position
        ]);
    }

    #[Route(path: "/position/create", name: "app_position_create")]
    public function create() {
        return $this->render("position/create_or_edit.html.twig", [
            "position" => null
        ]);
    }
    #[Route(path: "/position/edit/{id}", name: "app_position_edit")]
    public function edit(int $id) {
        $position = $this->positionRepository->find($id);
        if (!$position) {
            throw $this->createNotFoundException('The position does not exist');
        }
        return $this->render("position/create_or_edit.html.twig", [
            // "position"=> $position
        ]);
    }
}