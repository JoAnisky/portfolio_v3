<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\ScreenshotRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ScreenshotRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    normalizationContext: [
        'groups' => [
            self::READ_GROUP,
            self::WRITE_GROUP,
        ]
    ],
    paginationItemsPerPage: 12,
)]
class Screenshot
{
    const string ALL_GROUP = 'screenshot';
    const string READ_GROUP = self::ALL_GROUP . ':read';
    const string WRITE_GROUP = self::ALL_GROUP . ':write';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private Uuid $id;

    #[Vich\UploadableField(mapping: 'screenshots', fileNameProperty: 'path')]
    private ?File $imageFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([Project::READ_GROUP])]
    private ?string $path = null;

    #[ORM\Column(length: 255)]
    #[Groups([Project::READ_GROUP])]
    private ?string $alt = null;

    #[ORM\Column]
    #[Groups([Project::READ_GROUP])]
    private bool $isCover = false;

    #[ORM\Column]
    #[Groups([Project::READ_GROUP])]
    private int $position = 0;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'screenshots')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Project $project;

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function enforceSingleCover(): void
    {
        if ($this->isCover && $this->project !== null) {
            foreach ($this->project->getScreenshots() as $screenshot) {
                if ($screenshot !== $this && $screenshot->isCover()) {
                    $screenshot->setIsCover(false);
                }
            }
        }
    }

    public function isCover(): ?bool { return $this->isCover; }

    public function setIsCover(bool $isCover): static
    {
        $this->isCover = $isCover;
        return $this;
    }

    public function getId(): Uuid { return $this->id; }

    public function getPath(): ?string { return $this->path; }

    public function setPath(?string $path): static
    {
        $this->path = $path;
        return $this;
    }

    public function getAlt(): ?string { return $this->alt; }

    public function setAlt(string $alt): static
    {
        $this->alt = $alt;
        return $this;
    }

    public function getPosition(): ?int { return $this->position; }

    public function setPosition(int $position): static
    {
        $this->position = $position;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    public function getProject(): Project { return $this->project; }

    public function setProject(Project $project): static
    {
        $this->project = $project;
        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile): static
    {
        $this->imageFile = $imageFile;
        if ($imageFile !== null) {
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
    }
}
