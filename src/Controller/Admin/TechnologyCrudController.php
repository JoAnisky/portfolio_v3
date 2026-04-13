<?php

namespace App\Controller\Admin;

use App\Entity\Technology;
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

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Nom');
        yield TextField::new('icon', 'Slug Simple Icons')->setHelp('Ex : VueJS, Symfony, Kubernetes, Docker...');
        yield ColorField::new('color', 'Couleur')->setRequired(false);
        yield ChoiceField::new('category', 'Catégorie')->setChoices([
            'Frontend'  => 'frontend',
            'Backend'   => 'backend',
            'DevOps'    => 'devops',
            'Base de données' => 'database',
            'Autre'     => 'other',
        ]);
    }
}
