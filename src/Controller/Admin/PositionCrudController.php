<?php

namespace App\Controller\Admin;

use App\Entity\Position;
use App\Entity\Tag;
use App\Enum\Level;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PositionCrudController extends AbstractCrudController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Position::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('company');
        yield TextField::new('name');
        yield TextEditorField::new('description')->onlyOnForms();
        yield ChoiceField::new('level')->setChoices([
            'Junior' => Level::JUNIOR,
            'Middle' => Level::MIDDLE,
            'Senior' => Level::SENIOR,
            'C-Level' => Level::C_LEVEL,
        ]);

        yield AssociationField::new('attributes', 'Position Attributes')
            ->autocomplete();
        yield AssociationField::new('tags', 'Project Tags')
            ->autocomplete()
            ->setFormTypeOptions([
                'attr' => [
                    'data-ea-autocomplete-allow-item-create' => 'true',
                ]
            ])
            ->setHelp('Start typing to select existing tags from the global library');

        yield IntegerField::new('maxProjects', 'Max Included Projects')
            ->setHelp('Maximum number of matching projects to attach');
    }

    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $builder = parent::createNewFormBuilder($entityDto, $formOptions, $context);
        $this->addTagsOnTheFlyListener($builder);
        return $builder;
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $builder = parent::createEditFormBuilder($entityDto, $formOptions, $context);
        $this->addTagsOnTheFlyListener($builder);
        return $builder;
    }
    private function addTagsOnTheFlyListener(FormBuilderInterface $builder): void
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            
            if (!isset($data['tags']) || !isset($data['tags']['autocomplete']) || !is_array($data['tags']['autocomplete'])) return;

            $processedTags = [];
            $tagRepository = $this->entityManager->getRepository(Tag::class);

            foreach ($data['tags']['autocomplete'] as $tagName) {
                $tag = $tagRepository->findOneBy(['name' => $tagName]);
                
                if (!$tag) {
                    $tag = new Tag();
                    $tag->setName($tagName);
                    
                    $this->entityManager->persist($tag);
                    $this->entityManager->flush();
                }
                $processedTags[] = $tag->getId();
            }
            $data['tags']['autocomplete'] = $processedTags;
            $event->setData($data);
        });
    }
}
