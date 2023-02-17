<?php

namespace Code202\Security\Entity\Activity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(schema: 'security')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
abstract class Trigger
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;

    #[ORM\OneToMany(targetEntity: Activity::class, mappedBy: 'trigger')]
    protected Collection $activities;

    protected array $datas = [];

    public function __construct()
    {
        $this->activities = new ArrayCollection();
    }

    #[Groups(['list'])]
    public function getType(): string
    {
        return get_class($this);
    }

    #[Groups(['list'])]
    abstract public function getReference(): TriggerReference;

    public function getDatas(): array
    {
        return $this->datas;
    }

    public function setDatas(array $datas): self
    {
        $this->datas = $datas;

        return $this;
    }

    public function getData(string $name): string|array|null
    {
        return isset($this->datas[$name]) ? $this->datas[$name] : null;
    }

    public function setData(string $name, string|array $value): self
    {
        $this->datas[$name] = $value;

        return $this;
    }
}
