<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ContactInput
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $name = '';

    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 300)]
    public string $subject = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 2000)]
    public string $message = '';
}
