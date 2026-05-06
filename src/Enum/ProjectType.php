<?php

namespace App\Enum;

enum ProjectType: string
{
    case Website = 'Site web';
    case WebApp = 'Application web';
    case Ecommerce = 'E-commerce';
    case Api = 'API / Backend';
    case DevOps = 'DevOps / Infrastructure';

    public function toArray(): array
    {
        return [
            'key'   => $this->name,  // 'Website'
            'label' => $this->value, // 'Site web'
        ];
    }

}
