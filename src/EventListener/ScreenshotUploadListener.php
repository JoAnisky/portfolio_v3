<?php

namespace App\EventListener;

use App\Entity\Screenshot;
use App\Service\ScreenshotProcessor;
use Intervention\Image\Exceptions\InvalidArgumentException;
use Random\RandomException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;

#[AsEventListener(event: Events::POST_UPLOAD, method: 'onPostUpload')]
class ScreenshotUploadListener
{
    public function __construct(
        private ScreenshotProcessor $screenshotProcessor,
        private string $uploadDir,
    ) {}

    /**
     * @throws RandomException
     * @throws InvalidArgumentException
     */
    public function onPostUpload(Event $event): void
    {
        $object = $event->getObject();

        if (!$object instanceof Screenshot) {
            return;
        }

        $currentPath = $object->getPath();

        if (!$currentPath) {
            return;
        }

        $fullPath = $this->uploadDir . '/' . $currentPath;

        if (!file_exists($fullPath)) {
            return;
        }

        $file = new File($fullPath);
        $filename = $this->screenshotProcessor->process($file);

        unlink($fullPath);

        $object->setPath($filename);
    }
}
