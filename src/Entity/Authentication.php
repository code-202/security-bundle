<?php

namespace Code202\Security\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
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
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;

    #[ORM\Column(type: 'guid')]
    #[Groups(['list'])]
    protected string $uuid;

    #[ORM\ManyToOne(targetEntity: Account::class, inversedBy: 'authentications')]
    protected Account $account;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['list'])]
    protected bool $enabled;

    #[ORM\Column(type: 'string', length: 20, enumType: AuthenticationType::class)]
    #[Assert\NotBlank]
    #[Groups(['list'])]
    protected AuthenticationType $type;

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    #[Groups(['list'])]
    protected string $key;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['list'])]
    protected bool $verified;

    #[ORM\Column(type: 'json')]
    protected array $datas;

    #[ORM\OneToMany(targetEntity: 'Session', mappedBy: 'authentication')]
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

    public function getDatas(): array
    {
        return $this->datas;
    }

    public function setDatas(array $datas): self
    {
        $this->datas = $datas;

        return $this;
    }

    public function getData(string $name): ?string
    {
        return isset($this->datas[$name]) ? $this->datas[$name] : null;
    }

    public function setData(string $name, string $value): self
    {
        $this->datas[$name] = $value;

        return $this;
    }
}
