<?php

namespace App\Controller\Admin;

use App\Entity\Attribute;
use App\Enum\AttributeDataType;
use App\Form\AttributeOptionFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\HiddenField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted("ROLE_RECRUITER")]
class AttributeCrudController extends AbstractCrudController
{
    public function __construct(private TranslatorInterface $translator){}
    public static function getEntityFqcn(): string
    {
        return Attribute::class;
    }
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('attribute.singular')
            ->setEntityLabelInPlural('attribute.plural');
    }

    public function configureFields(string $pageName): iterable
    {
        yield HiddenField::new('version')->hideOnIndex();
        yield TextField::new('name', 'attribute.name')
            ->setHelp('attribute.help.name');
 
        yield AssociationField::new('category', 'attribute.category')
            // ->setCrudController(AttributeCategoryCrudController::class)
            ->autocomplete();
 
        yield ChoiceField::new('dataType', 'attribute.data_type')
            ->setChoices([
                'attribute.data_types.string' => AttributeDataType::STRING,
                'attribute.data_types.text' => AttributeDataType::TEXT,
                'attribute.data_types.image' => AttributeDataType::IMAGE,
                'attribute.data_types.numeric' => AttributeDataType::NUMERIC,
                'attribute.data_types.date' => AttributeDataType::DATE,
                'attribute.data_types.period' => AttributeDataType::PERIOD,
                'attribute.data_types.boolean' => AttributeDataType::BOOLEAN,
                'attribute.data_types.select' => AttributeDataType::SELECT,
            ])
            ->setFormTypeOption('disabled', $pageName === Crud::PAGE_EDIT);

        yield CollectionField::new('options', 'attribute.options')
            ->setEntryType(AttributeOptionFormType::class)
            ->allowAdd()
            ->allowDelete()
            // ->byReference(false) // Required so Doctrine triggers addOption()/removeOption()
            ->hideOnIndex()
            ->setHelp('attribute.help.options');
            // ->renderFormWhen(
            //     fn ($attribute) => $attribute->getDataType() === AttributeDataType::SELECT
            // );
        
        yield TextareaField::new('description', 'attribute.description')
            ->hideOnIndex();

        yield BooleanField::new('isBuiltin', 'attribute.builtin')
            ->hideOnForm();
    }

    // TODO: Minor issue where user form data is lost on version conflict
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        try {
            parent::updateEntity($entityManager, $entityInstance);
        } catch (OptimisticLockException $e) {
            $this->addFlash(
                'error', 
                $this->translator->trans('errors.version_conflict.message')
            );
            return;
        }
    }
}