<?php

namespace App\Controller\Admin;

use App\Entity\Screenshot;
use App\Service\ScreenshotProcessor;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Intervention\Image\Exceptions\InvalidArgumentException;
use Random\RandomException;
use Symfony\Component\HttpFoundation\File\File;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;

class ScreenshotCrudController extends AbstractCrudController
{
    public function __construct(
        private ScreenshotProcessor $screenshotProcessor,
        private string $projectDir,
        private AdminContextProvider $adminContextProvider,
    ) {}

    public static function getEntityFqcn(): string
    {
        return Screenshot::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('project', 'Projet');

        // Création uniquement — requis
        yield ImageField::new('path', 'Image')
            ->setUploadDir('public/uploads/screenshots_tmp')
            ->setBasePath('/')
            ->setRequired(true)
            ->onlyWhenCreating();
        // Vue index/detail uniquement
        yield ImageField::new('path', 'Aperçu')
            ->setBasePath('/')
            ->hideOnForm();
        // Édition — avec miniature via setHelp
        if ($pageName === Crud::PAGE_EDIT) {

            $entity = $this->adminContextProvider->getContext()?->getEntity()->getInstance();
            $path = $entity instanceof Screenshot ? $entity->getPath() : null;
            $label = $path
                ? sprintf('<div><img src="/%s" style="max-width:200px;border-radius:4px;display:block;margin-bottom:8px;" alt="Image actuelle"><span>Remplacer l\'image</span></div>', htmlspecialchars($path))
                : 'Remplacer l\'image';

            yield ImageField::new('path', $label)
                ->setUploadDir('public/uploads/screenshots_tmp')
                ->setBasePath('/')
                ->setRequired(false);
        }

        yield TextField::new('alt', 'Texte alternatif');
        yield BooleanField::new('isCover', 'Cover');
        yield IntegerField::new('position', 'Position');
    }

    /**
     * @throws RandomException
     * @throws InvalidArgumentException
     */
    public function persistEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->handleImageUpload($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    /**
     * @throws RandomException
     * @throws InvalidArgumentException
     */
    public function updateEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->handleImageUpload($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    /**
     * @throws InvalidArgumentException
     * @throws RandomException
     */
    private function handleImageUpload(mixed $entityInstance): void
    {
        if (!$entityInstance instanceof Screenshot) {
            return;
        }

        $currentPath = $entityInstance->getPath();

        if (!$currentPath) {
            return;
        }

        $tmpPath = $this->projectDir . '/public/uploads/screenshots_tmp/' . $currentPath;

        if (!file_exists($tmpPath)) {
            return;
        }

        $tmpFile = new File($tmpPath);
        $path = $this->screenshotProcessor->process($tmpFile);
        $entityInstance->setPath($path);

        // Supprimer le fichier temporaire
        unlink($tmpPath);
    }

//    #[NoReturn]
//    public function createEditFormBuilder(\EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto $entityDto, \EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection|\EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore $formFields, \EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore|\EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext $formOptions): \Symfony\Component\Form\FormBuilderInterface
//    {
//        $builder = parent::createEditFormBuilder($entityDto, $formFields, $formOptions);
//        dd($builder->get('path')->getData());
//    }
}
