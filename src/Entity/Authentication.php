<?php

namespace Code202\Security\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(schema: 'security')]
#[ORM\UniqueConstraint(name: 'type_key', columns: ['type', 'key'])]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['key', 'type'])]
class Authentication implements Activity\TargetReference
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;

    #[ORM\Column(type: Types::GUID)]
    #[Groups(['list'])]
    protected string $uuid;

    #[ORM\ManyToOne(targetEntity: Account::class, inversedBy: 'authentications')]
    #[ORM\JoinColumn(nullable: false)]
    protected Account $account;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups(['list'])]
    protected bool $enabled;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: AuthenticationType::class)]
    #[Assert\NotBlank]
    #[Groups(['list'])]
    protected AuthenticationType $type;

    #[ORM\Column(type: Types::STRING)]
    #[Assert\NotBlank]
    #[Groups(['list'])]
    protected string $key;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups(['list'])]
    protected bool $verified;

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(type: Types::JSON)]
    protected array $datas;

    /**
     * @var Collection<int, Session>
     */
    #[ORM\OneToMany(targetEntity: Session::class, mappedBy: 'authentication')]
    protected Collection $sessions;

    public function __construct(string $uuid, AuthenticationType $type, Account $account)
    {
        $this->uuid = $uuid;
        $this->type = $type;
        $this->account = $account;
        $this->datas = [];
        $this->enabled = true;
        $this->verified = false;
        $this->sessions = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getAccount(): Account
    {
        return $this->account;
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

    public function getType(): AuthenticationType
    {
        return $this->type;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }

    public function setVerified(bool $isVerified): self
    {
        $this->verified = $isVerified;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDatas(): array
    {
        return $this->datas;
    }

    /**
     * @param array<string, mixed> $datas
     */
    public function setDatas(array $datas): self
    {
        $this->datas = $datas;

        return $this;
    }

    public function getData(string $name): ?string
    {
        return $this->datas[$name] ?? null;
    }

    public function setData(string $name, string $value): self
    {
        $this->datas[$name] = $value;

        return $this;
    }
}
