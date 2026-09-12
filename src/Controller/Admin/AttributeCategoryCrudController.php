<?php
namespace App\Controller\Admin;

use App\Entity\AttributeCategory;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[IsGranted("ROLE_RECRUITER")]
class AttributeCategoryCrudController extends AbstractCrudController {
    public static function getEntityFqcn(): string {
        return AttributeCategory::class;
    }

    public function configureFields(string $pageName): array|\Traversable {
        yield TextField::new("name");
    }
}