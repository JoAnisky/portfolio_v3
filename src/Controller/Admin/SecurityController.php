<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class SecurityController extends AbstractController
{
    #[Route('/admin/login', name: 'admin_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('@EasyAdmin/page/login.html.twig', [
            'error'               => $authenticationUtils->getLastAuthenticationError(),
            'last_username'       => $authenticationUtils->getLastUsername(),
            'csrf_token_intention' => 'authenticate',
            'target_path'         => $this->generateUrl('admin'),
            'page_title'          => 'Portfolio — Admin',
            'username_label'      => 'Email',
            'password_label'      => 'Mot de passe',
            'sign_in_label'       => 'Se connecter',
        ]);
    }

    #[Route('/admin/logout', name: 'admin_logout')]
    public function logout(): void {}
}
