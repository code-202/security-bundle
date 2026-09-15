<?php

namespace Code202\Security\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(schema: 'security')]
#[ORM\UniqueConstraint(name: 'session_uuid_idx', columns: ['uuid'])]
#[ORM\HasLifecycleCallbacks]
class Session implements Activity\TargetReference, Activity\TriggerReference
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;

    #[ORM\ManyToOne(targetEntity: Authentication::class, inversedBy: 'sessions')]
    #[ORM\JoinColumn(nullable: false)]
    protected Authentication $authentication;

    #[ORM\Column(type: Types::GUID)]
    #[Groups(['list'])]
    protected string $uuid;

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(type: Types::JSON)]
    #[Groups(['list'])]
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
    protected array $datas;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['session.info'])]
    protected ?DateTimeImmutable $expiredAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['session.info'])]
    protected ?DateTimeImmutable $trustUntil = null;

    protected bool $created = false;

    public function __construct(string $uuid, Authentication $authentication)
    {
        $this->uuid = $uuid;
        $this->authentication = $authentication;
        $this->datas = [];
        $this->expiredAt = (new DateTimeImmutable())->modify('+60 seconds');
        $this->created = true;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function isCreated(): bool
    {
        return $this->created;
    }

    public function getAuthentication(): Authentication
    {
        return $this->authentication;
    }

    public function getUuid(): string
    {
        return $this->uuid;
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

    public function setExpiredAt(?DateTimeInterface $expiredAt = null): self
    {
        $this->expiredAt = $expiredAt instanceof DateTimeInterface ? DateTimeImmutable::createFromInterface($expiredAt) : null;

        return $this;
    }

    public function getExpiredAt(): ?DateTimeInterface
    {
        return $this->expiredAt;
    }

    public function setTrustUntil(?DateTimeInterface $trustUntil = null): self
    {
        $this->trustUntil = $trustUntil instanceof DateTimeInterface ? DateTimeImmutable::createFromInterface($trustUntil) : null;

        return $this;
    }

    public function getTrustUntil(): ?DateTimeInterface
    {
        return $this->trustUntil;
    }
}
