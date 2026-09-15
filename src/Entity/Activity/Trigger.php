<?php

namespace Code202\Security\Entity\Activity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(schema: 'security')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
abstract class Trigger
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;

    /**
     * @var Collection<int, Activity>
     */
    #[ORM\OneToMany(targetEntity: Activity::class, mappedBy: 'trigger')]
    protected Collection $activities;

    /**
     * @var array<string, mixed>
     */
    protected array $datas = [];

    public function __construct()
    {
        $this->activities = new ArrayCollection();
    }

    #[Groups(['list'])]
    public function getType(): string
    {
        return static::class;
    }

    #[Groups(['list'])]
    abstract public function getReference(): TriggerReference;

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

    /**
     * @return null|array<string>|string
     */
    public function getData(string $name): array|string|null
    {
        return $this->datas[$name] ?? null;
    }

    /**
     * @param array<string>|string $value
     */
    public function setData(string $name, array|string $value): self
    {
        $this->datas[$name] = $value;

        return $this;
    }
}
