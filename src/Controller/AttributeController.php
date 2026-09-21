<?php

namespace App\Controller;

use App\Repository\AttributeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class AttributeController extends AbstractController {
    public function __construct(private AttributeRepository $attributeRepository){}

    #[Route(path:"/attribute/search", name: "app_attribute_search")]
    public function search(Request $request) {
        $query = $request->query->get('query');
        // dd($query);
        $attributes = $this->attributeRepository->search($query);
        $data = array_map(fn($a)=> ['id'=>$a->getId(),'name'=>$a->getName(), 'category'=>$a->getCategory()->getName()], $attributes);
        return $this->json($data);
    }
}