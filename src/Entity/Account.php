<?php

namespace Code202\Security\Entity;

use Code202\Security\Repository\AccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AccountRepository::class)]
#[ORM\Table(schema: 'security')]
#[ORM\UniqueConstraint(name: 'account_uuid_idx', columns: ['uuid'])]
#[ORM\UniqueConstraint(name: 'account_name_idx', columns: ['name'])]
#[UniqueEntity('uuid')]
#[UniqueEntity('name')]
#[ORM\HasLifecycleCallbacks]
class Account implements Activity\TargetReference
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;

    #[ORM\Column(type: Types::GUID, unique: true)]
    #[Groups(['list'])]
    protected string $uuid;

    #[ORM\Column(type: Types::STRING, length: 50, unique: true)]
    #[Assert\NotBlank]
    #[Groups(['list'])]
    protected string $name;

    #[ORM\Column(type: Types::JSON)]
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
    private array $roles = [];

    #[ORM\Column]
    #[Groups(['list'])]
    protected bool $enabled;

    #[ORM\OneToMany(targetEntity: Authentication::class, mappedBy: 'account')]
    protected Collection $authentications;

    public function __construct(string $uuid, string $name)
    {
        $this->uuid = $uuid;
        $this->name = $name;
        $this->enabled = true;
        $this->authentications = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function enable(): self
    {
        $this->enabled = true;

        return $this;
    }

    public function disable(): self
    {
        $this->enabled = false;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection<int, \Code202\Security\Entity\Authentication>
     */
    public function getAuthentications(): Collection
    {
        return $this->authentications;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }
}
