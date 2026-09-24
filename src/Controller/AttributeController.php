<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\AttributeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class AttributeController extends AbstractController {
    public function __construct(private AttributeRepository $attributeRepository){}

    #[Route(path:"/attribute/search", name: "app_attribute_search")]
    public function search(Request $request, #[CurrentUser]User $user) {
        $query = $request->query->get('query');
        $forUser = $request->query->get('forUser');
        // dd($query);
        $attributes = null;
        if ($forUser) {
            $attributes = $this->attributeRepository->searchNewForUser($user, $query);
        } else {
            $attributes = $this->attributeRepository->search($query);
        }
        $data = array_map(fn($a)=> ['id'=>$a->getId(),'name'=>$a->getName(), 'category'=>$a->getCategory()->getName()], $attributes);
        return $this->json($data);
    }
}