<?php

namespace App\Enum;

enum TechnologyCategory: string
{
    case Languages = 'Languages';
    case Frameworks = 'Frameworks / Librairies, CMS';
    case DevOps = 'DevOps';
    case Tools = 'Outils et logiciels';

    public function toArray(): array
    {
        return [
            'key'   => $this->name,  // 'Website'
            'label' => $this->value, // 'Site web'
        ];
    }
}
