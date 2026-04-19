<?php

namespace App\Controller\Admin;

use App\Entity\Screenshot;
use App\Service\ScreenshotProcessor;
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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;

class ScreenshotCrudController extends AbstractCrudController
{
    public function __construct(
        private ScreenshotProcessor $screenshotProcessor,
        private RequestStack $requestStack,
        private string $projectDir,
    ) {}

    public static function getEntityFqcn(): string
    {
        return Screenshot::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('project', 'Projet');
        yield ImageField::new('path', 'Image')
            ->setUploadDir('public/uploads/screenshots_tmp')
            ->setBasePath('')
            ->setRequired(true)
            ->onlyOnForms();
        yield ImageField::new('path', 'Aperçu')
            ->setBasePath('')
            ->hideOnForm();
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

//    /**
//     * @throws InvalidArgumentException
//     * @throws RandomException
//     */
//    protected function processUploadedFiles(FormInterface $form): void
//    {
//        dd($form->get('path')->getData());
//
//
//        /** @var UploadedFile|null $file */
//        $file = $form->get('path')->getData();
//
//        if ($file instanceof UploadedFile) {
//            $path = $this->screenshotProcessor->process($file);
//            $form->getData()->setPath($path);
//
//            // On empêche EasyAdmin de déplacer le fichier lui-même
//            return;
//        }
//
//        parent::processUploadedFiles($form);
//    }
}
