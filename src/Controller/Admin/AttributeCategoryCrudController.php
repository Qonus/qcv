<?php
namespace App\Controller\Admin;

use App\Entity\AttributeCategory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_RECRUITER")]
class AttributeCategoryCrudController extends AbstractCrudController {
    public static function getEntityFqcn(): string {
        return AttributeCategory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('attribute_category.singular')
            ->setEntityLabelInPlural('attribute_category.plural');
    }


    public function configureFields(string $pageName): iterable {
        yield TextField::new('name', 'attribute_category.name');
    }
}