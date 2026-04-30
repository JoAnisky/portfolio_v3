<?php

namespace App\Controller\Admin;

use App\Entity\Technology;
use App\Enum\TechnologyCategory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TechnologyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Technology::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('Technologie')
            ->setEntityLabelInPlural('Technologies');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm()->hideOnIndex();
        yield TextField::new('name', 'Nom');
        yield TextField::new('icon', 'Icon filename')->setHelp('Ex : VueJS, Symfony, Kubernetes, Docker...');
        yield ColorField::new('color', 'Couleur')->setRequired(false);
        yield ChoiceField::new('category')
            ->setChoices([
                'Languages' => TechnologyCategory::Languages,
                'Frameworks / Librairies, CMS' => TechnologyCategory::Frameworks,
                'DevOps' => TechnologyCategory::DevOps,
                'Outils et logiciels' => TechnologyCategory::Tools,
            ]);
    }
}
