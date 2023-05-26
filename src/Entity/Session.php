<?php

namespace Code202\Security\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(schema: 'security')]
#[ORM\UniqueConstraint(name: 'session_uuid_idx', columns: ['uuid'])]
#[ORM\HasLifecycleCallbacks]
class Session implements Activity\TargetReference, Activity\TriggerReference
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;

    #[ORM\ManyToOne(targetEntity: Authentication::class, inversedBy: 'sessions')]
    protected Authentication $authentication;

    #[ORM\Column(type: 'guid')]
    #[Groups(['list'])]
    protected string $uuid;

    #[ORM\Column(type: 'json')]
    #[Groups(['list'])]
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
    protected array $datas;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['session.info'])]
    protected ?\DateTimeImmutable $expiredAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['session.info'])]
    protected ?\DateTimeImmutable $trustUntil = null;

    protected bool $created = false;

    public function __construct(string $uuid, Authentication $authentication)
    {
        $this->uuid = $uuid;
        $this->authentication = $authentication;
        $this->datas = [];
        $this->expiredAt = (new \DateTimeImmutable())->modify('+60 seconds');
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


    public function setExpiredAt(\DateTimeInterface $expiredAt = null): self
    {
        $this->expiredAt = $expiredAt !== null ? \DateTimeImmutable::createFromInterface($expiredAt) : null;

        return $this;
    }

    public function getExpiredAt(): ?\DateTimeInterface
    {
        return $this->expiredAt;
    }

    public function setTrustUntil(\DateTimeInterface $trustUntil = null): self
    {
        $this->trustUntil = $trustUntil !== null ? \DateTimeImmutable::createFromInterface($trustUntil) : null;

        return $this;
    }

    public function getTrustUntil(): ?\DateTimeInterface
    {
        return $this->trustUntil;
    }
}
