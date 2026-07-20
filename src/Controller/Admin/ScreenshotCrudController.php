<?php

namespace App\Controller\Admin;

use App\Entity\Screenshot;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ScreenshotCrudController extends AbstractCrudController
{

    public static function getEntityFqcn(): string
    {
        return Screenshot::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->overrideTemplate('crud/index', 'admin/screenshot/index.html.twig')
            ->setPaginatorPageSize(100);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm()->hideOnIndex();
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

        // La position se gère par glisser-déposer sur la liste, plus de saisie manuelle.
        // On utilise le champ générique (et non IntegerField/TextField) car leurs configurateurs
        // essaient de transformer la valeur brute (un int) en chaîne avant d'appliquer formatValue().
        yield Field::new('position', 'Ordre')
            ->onlyOnIndex()
            ->setTemplateName('crud/field/text')
            ->formatValue(fn () => '<i class="fa fa-grip-vertical sortable-handle" title="Glisser pour réordonner"></i>');
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Screenshot) {
            $maxPosition = $entityManager->getRepository(Screenshot::class)
                ->createQueryBuilder('s')
                ->select('MAX(s.position)')
                ->where('s.project = :project')
                ->setParameter('project', $entityInstance->getProject())
                ->getQuery()
                ->getSingleScalarResult();

            $entityInstance->setPosition(($maxPosition ?? -1) + 1);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    #[Route('/admin/screenshots/reorder', name: 'admin_screenshot_reorder', methods: ['POST'])]
    public function reorder(Request $request, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        $token = $request->headers->get('X-CSRF-Token', '');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('screenshot_reorder', $token))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        $ids = json_decode($request->getContent(), true)['ids'] ?? null;
        if (!\is_array($ids) || [] === $ids) {
            return new JsonResponse(['error' => 'Missing ids'], 400);
        }

        $screenshots = $entityManager->getRepository(Screenshot::class)->findBy(['id' => $ids]);
        if (\count($screenshots) !== \count($ids)) {
            return new JsonResponse(['error' => 'Invalid ids'], 400);
        }

        $byId = [];
        foreach ($screenshots as $screenshot) {
            $byId[(string) $screenshot->getId()] = $screenshot;
        }

        $projectIds = array_unique(array_map(
            fn (Screenshot $screenshot) => (string) $screenshot->getProject()->getId(),
            $screenshots
        ));
        if (\count($projectIds) > 1) {
            return new JsonResponse(['error' => 'Screenshots must belong to the same project'], 400);
        }

        foreach (array_values($ids) as $position => $id) {
            $byId[(string) $id]?->setPosition($position);
        }

        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}
