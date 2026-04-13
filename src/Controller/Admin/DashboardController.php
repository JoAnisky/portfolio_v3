<?php

namespace App\Controller\Admin;

use App\Entity\Client;
use App\Entity\Project;
use App\Entity\Tag;
use App\Entity\Technology;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('@EasyAdmin/page/content.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Portfolio JL v3');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::section('Contenu');
        //yield MenuItem::linkTo(ProjectCrudController::class, 'Projets', 'fa fa-code');
        yield MenuItem::linkTo(ClientCrudController::class, 'Clients', 'fa fa-building');
        yield MenuItem::section('Référentiels');
        yield MenuItem::linkTo(TagCrudController::class, 'Tags', 'fa fa-tag');
        yield MenuItem::linkTo(TechnologyCrudController::class, 'Technologies', 'fa fa-microchip');
    }
}
