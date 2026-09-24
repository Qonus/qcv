<?php
namespace App\Controller\Admin;

use App\Entity\AttributeCategory;
use App\Entity\Tag;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TagCrudController extends AbstractCrudController {
    public static function getEntityFqcn(): string {
        return Tag::class;
    }

    public function configureFields(string $pageName): array|\Traversable {
        yield TextField::new("name", "tag.name");
    }
}