<?php

namespace App\Entity;

use App\Enum\ProjectContext;
use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
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
class Project
{
    const string ALL_GROUP = 'project';
    const string READ_GROUP = self::ALL_GROUP . ':read';
    const string WRITE_GROUP = self::ALL_GROUP . ':write';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    #[Groups([Project::READ_GROUP])]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Groups([Project::READ_GROUP])]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([Project::READ_GROUP])]
    private ?string $githubUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([Project::READ_GROUP])]
    private ?string $siteUrl = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups([Project::READ_GROUP])]
    private ?string $description = null;

    #[ORM\Column(length: 20, enumType: ProjectContext::class)]
    #[Groups([Project::READ_GROUP])]
    private ?ProjectContext $context = null;

    /**
     * @var Collection<int, Screenshot>
     */
    #[ORM\OneToMany(targetEntity: Screenshot::class, mappedBy: 'project', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[Groups([Project::READ_GROUP])]
    private Collection $screenshots;

    /**
     * @var Collection<int, ProjectFeature>
     */
    #[ORM\OneToMany(targetEntity: ProjectFeature::class, mappedBy: 'project', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[Groups([Project::READ_GROUP])]
    private Collection $features;

    /**
     * @var Collection<int, ProjectHighlight>
     */
    #[ORM\OneToMany(targetEntity: ProjectHighlight::class, mappedBy: 'project', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[Groups([Project::READ_GROUP])]
    private Collection $highlights;

    /**
     * @var Collection<int, Client>
     */
    #[ORM\ManyToMany(targetEntity: Client::class, inversedBy: 'projects')]
    #[ORM\JoinTable(name: 'project_client')]
    #[Groups([Project::READ_GROUP])]
    private Collection $clients;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'projects')]
    #[ORM\JoinTable(name: 'project_tag')]
    #[Groups([Project::READ_GROUP])]
    private Collection $tags;

    /**
     * @var Collection<int, Technology>
     */
    #[ORM\ManyToMany(targetEntity: Technology::class, inversedBy: 'projects')]
    #[ORM\JoinTable(name: 'project_technology')]
    #[Groups([Project::READ_GROUP])]
    private Collection $technologies;

    public function __construct()
    {
        $this->screenshots = new ArrayCollection();
        $this->features = new ArrayCollection();
        $this->highlights = new ArrayCollection();
        $this->clients = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->technologies = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name ?? '';
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

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getGithubUrl(): ?string
    {
        return $this->githubUrl;
    }

    public function setGithubUrl(?string $githubUrl): static
    {
        $this->githubUrl = $githubUrl;

        return $this;
    }

    public function getSiteUrl(): ?string
    {
        return $this->siteUrl;
    }

    public function setSiteUrl(?string $siteUrl): static
    {
        $this->siteUrl = $siteUrl;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getContext(): ?ProjectContext
    {
        return $this->context;
    }

    public function setContext(ProjectContext $context): static
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return Collection<int, Screenshot>
     */
    public function getScreenshots(): Collection
    {
        return $this->screenshots;
    }

    public function addScreenshot(Screenshot $screenshot): static
    {
        if (!$this->screenshots->contains($screenshot)) {
            $this->screenshots->add($screenshot);
            $screenshot->setProject($this);
        }

        return $this;
    }

    public function removeScreenshot(Screenshot $screenshot): static
    {
        if ($this->screenshots->removeElement($screenshot)) {
            // set the owning side to null (unless already changed)
            if ($screenshot->getProject() === $this) {
                $screenshot->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProjectFeature>
     */
    public function getFeatures(): Collection
    {
        return $this->features;
    }

    public function addFeature(ProjectFeature $feature): static
    {
        if (!$this->features->contains($feature)) {
            $this->features->add($feature);
            $feature->setProject($this);
        }

        return $this;
    }

    public function removeFeature(ProjectFeature $feature): static
    {
        if ($this->features->removeElement($feature)) {
            // set the owning side to null (unless already changed)
            if ($feature->getProject() === $this) {
                $feature->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProjectHighlight>
     */
    public function getHighlights(): Collection
    {
        return $this->highlights;
    }

    public function addHighlight(ProjectHighlight $highlight): static
    {
        if (!$this->highlights->contains($highlight)) {
            $this->highlights->add($highlight);
            $highlight->setProject($this);
        }

        return $this;
    }

    public function removeHighlight(ProjectHighlight $highlight): static
    {
        if ($this->highlights->removeElement($highlight)) {
            // set the owning side to null (unless already changed)
            if ($highlight->getProject() === $this) {
                $highlight->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Client>
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(Client $client): static
    {
        if (!$this->clients->contains($client)) {
            $this->clients->add($client);
        }

        return $this;
    }

    public function removeClient(Client $client): static
    {
        $this->clients->removeElement($client);

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * @return Collection<int, Technology>
     */
    public function getTechnologies(): Collection
    {
        return $this->technologies;
    }

    public function addTechnology(Technology $technology): static
    {
        if (!$this->technologies->contains($technology)) {
            $this->technologies->add($technology);
        }

        return $this;
    }

    public function removeTechnology(Technology $technology): static
    {
        $this->technologies->removeElement($technology);

        return $this;
    }
}
