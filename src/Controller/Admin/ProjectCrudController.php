<?php

namespace App\Controller\Admin;

use App\Entity\Project;
use App\Enum\ProjectContext;
use App\Form\ProjectFeatureType;
use App\Form\ProjectHighlightType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class ProjectCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Project::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('Projet')
            ->setEntityLabelInPlural('Projets');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Nom');
        yield DateField::new('date', 'Date');
        yield ChoiceField::new('context', 'Contexte')
            ->setChoices([
                'Personnel' => ProjectContext::PERSONAL,
                'Professionnel' => ProjectContext::PROFESSIONAL,
            ]);
        yield TextEditorField::new('description', 'Description')
            ->hideOnIndex();
        yield UrlField::new('githubUrl', 'GitHub')->setRequired(false)->hideOnIndex();
        yield UrlField::new('siteUrl', 'Site')->setRequired(false)->hideOnIndex();

        yield AssociationField::new('clients', 'Clients')->setRequired(false);
        yield AssociationField::new('tags', 'Tags')->setRequired(false);
        yield AssociationField::new('technologies', 'Technologies')->setRequired(false);

        yield CollectionField::new('features', 'Fonctionnalités')
            ->setEntryType(ProjectFeatureType::class)
            ->allowAdd()
            ->allowDelete()
            ->hideOnIndex();

        yield CollectionField::new('highlights', 'Points forts')
            ->setEntryType(ProjectHighlightType::class)
            ->allowAdd()
            ->allowDelete()
            ->hideOnIndex();
    }
}
