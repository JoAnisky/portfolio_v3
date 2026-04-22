<?php

namespace App\Controller\Admin;

use App\Entity\Screenshot;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ScreenshotCrudController extends AbstractCrudController
{

    public static function getEntityFqcn(): string
    {
        return Screenshot::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('project', 'Projet');

        // Vue index/detail
        yield ImageField::new('path', 'Aperçu')
            ->setBasePath('/uploads/screenshots')
            ->hideOnForm();

        // Formulaire création
        yield Field::new('imageFile', 'Image')
            ->setFormType(VichImageType::class)
            ->onlyWhenCreating();

        // Formulaire édition — avec miniature
        if ($pageName === Crud::PAGE_EDIT) {
            yield Field::new('imageFile', 'Remplacer l\'image')
                ->setFormType(VichImageType::class)
                ->setRequired(false);
        }

        yield TextField::new('alt', 'Texte alternatif');
        yield BooleanField::new('isCover', 'Cover');
        yield IntegerField::new('position', 'Position');
    }
}
