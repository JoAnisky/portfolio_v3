<?php

namespace App\Service;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Exceptions\InvalidArgumentException;
use Intervention\Image\ImageManager;
use Random\RandomException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ScreenshotProcessor
{
    private string $uploadDir;

    public function __construct(string $projectDir)
    {
        $this->uploadDir = $projectDir . '/public/uploads/screenshots';
    }

    /**
     * @throws RandomException
     * @throws InvalidArgumentException
     */
    public function process(UploadedFile $file): string
    {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        $manager = ImageManager::usingDriver(Driver::class);

        $image = $manager->decodeSplFileInfo($file);
        $image->scaleDown(width: 1400);

        $filename = bin2hex(random_bytes(8)) . '.webp';
        $fullPath = $this->uploadDir . '/' . $filename;

        $image->encode(new WebpEncoder(quality: 82))->save($fullPath);

        return 'uploads/screenshots/' . $filename;
    }
}
