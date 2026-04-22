<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Dto\ContactInput;
use App\State\ContactProcessor;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/contact',
            status: 202,
            input: ContactInput::class,
            output: false,
            processor: ContactProcessor::class,
        ),
    ]
)]
class ContactResource {}
