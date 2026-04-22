<?php

namespace App\Entity;

use App\Repository\TechnologyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;

#[ORM\Entity(repositoryClass: TechnologyRepository::class)]
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
class Technology
{
    const string ALL_GROUP = 'technology';
    const string READ_GROUP = self::ALL_GROUP . ':read';
    const string WRITE_GROUP = self::ALL_GROUP . ':write';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    #[Groups([Project::READ_GROUP, Technology::READ_GROUP])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups([Project::READ_GROUP, Technology::READ_GROUP])]
    private ?string $icon = null;

    #[ORM\Column(length: 7, nullable: true)]
    #[Groups([Project::READ_GROUP, Technology::READ_GROUP])]
    private ?string $color = null;

    #[ORM\Column(length: 50)]
    private ?string $category = null;

    /**
     * @var Collection<int, Project>
     */
    #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'technologies')]
    private Collection $projects;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): static
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->addTechnology($this);
        }

        return $this;
    }

    public function removeProject(Project $project): static
    {
        if ($this->projects->removeElement($project)) {
            $project->removeTechnology($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
