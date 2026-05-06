<?php

namespace App\Enum;

enum ProjectType: string
{
    case Website = 'Site web';
    case WebApp = 'Application web';
    case Ecommerce = 'E-commerce';
    case Api = 'API / Backend';
    case DevOps = 'DevOps / Infrastructure';

    public function icon(): string
    {
        return match($this) {
            self::Website  => 'globe.svg',
            self::WebApp   => 'app.svg',
            self::Ecommerce => 'cart.svg',
            self::Api      => 'server.svg',
            self::DevOps   => 'infrastructure.svg',
        };
    }
}
