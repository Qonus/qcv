<?php

namespace App\Controller\Admin;

use App\Entity\Attribute;
use App\Entity\AttributeCategory;
use App\Enum\AttributeDataType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_RECRUITER")]
class AttributeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Attribute::class;
    }

    public function configureFields(string $pageName): iterable
    {

        yield TextField::new('name')
            ->setHelp('Must be globally unique.');
 
        yield AssociationField::new('category')
            // ->setCrudController(AttributeCategoryCrudController::class)
            ->autocomplete();
 
        yield ChoiceField::new('dataType')
            ->setChoices([
                'String (single line)' => AttributeDataType::STRING,
                'Text (markdown)' => AttributeDataType::TEXT,
                'Image (external URL)' => AttributeDataType::IMAGE,
                'Numeric' => AttributeDataType::NUMERIC,
                'Date' => AttributeDataType::DATE,
                'Period (date range)' => AttributeDataType::PERIOD,
                'Boolean' => AttributeDataType::BOOLEAN,
                'One of many (dropdown)' => AttributeDataType::SELECT,
            ])
            ->setFormTypeOption('disabled', $pageName === Crud::PAGE_EDIT);
 
        yield TextareaField::new('description')
            ->hideOnIndex();

        yield BooleanField::new('isBuiltin', 'Built-in');
            // ->hideOnForm();
    }
}
