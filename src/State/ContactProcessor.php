<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\ContactInput;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ContactProcessor implements ProcessorInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private string          $contactRecipient,
    ) {}

    /**
     * @throws TransportExceptionInterface
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        /** @var ContactInput $data */
        try {
            $email = (new Email())
                ->from('noreply@jonathanlore.fr')
                ->replyTo($data->email)
                ->to($this->contactRecipient)
                ->subject("Contact portfolio — {$data->name}")
                ->text("De : {$data->name} <{$data->email}>\n\n{$data->message}");

            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            dump($e->getMessage());
            throw $e;
        }
    }
}
