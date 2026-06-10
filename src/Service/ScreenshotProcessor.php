<?php

namespace App\Service;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Exceptions\InvalidArgumentException;
use Intervention\Image\ImageManager;
use Random\RandomException;
use Symfony\Component\HttpFoundation\File\File;

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
    public function process(File $file, string $projectGuid): string
    {
        $subDir = $this->uploadDir . '/' . $projectGuid;

        if (!is_dir($subDir)) {
            mkdir($subDir, 0755, true);
        }

        $manager = ImageManager::usingDriver(Driver::class);
        $image = $manager->decodeSplFileInfo($file);
        $image->scaleDown(width: 1400);

        $filename = bin2hex(random_bytes(8)) . '.webp';
        $fullPath = $subDir . '/' . $filename;

        $image->encode(new WebpEncoder(quality: 82))->save($fullPath);

        return $projectGuid . '/' . $filename;
    }
}
