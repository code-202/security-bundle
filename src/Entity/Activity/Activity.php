<?php

namespace Code202\Security\Entity\Activity;

use OpenApi\Attributes as OA;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Code202\Security\Entity\Timestampable;

#[ORM\Entity]
#[ORM\Table(schema: 'security')]
#[ORM\HasLifecycleCallbacks]
class Activity
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;

    #[ORM\Column(type: 'string', length: 30)]
    #[Assert\NotBlank]
    #[Groups(['list'])]
    protected string $type;

    #[ORM\ManyToOne(targetEntity: Target::class, inversedBy: 'activities', cascade: ['persist'])]
    #[Groups(['list'])]
    protected Target $target;

    #[ORM\ManyToOne(targetEntity: Trigger::class, inversedBy: 'activities', cascade: ['persist'])]
    #[Groups(['list'])]
    protected Trigger $trigger;

    #[ORM\Column(type: 'json')]
    #[Groups(['list'])]
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
    protected array $datas;

    public function __construct(string $type, Target $target, Trigger $trigger)
    {
        $this->type = $type;
        $this->target = $target;
        $this->trigger = $trigger;
        $this->datas = [];

        $targetDatas = $this->target->getDatas();

        if ($targetDatas) {
            $this->datas['target'] = $targetDatas;
        }

        $triggerDatas = $this->trigger->getDatas();

        if ($triggerDatas) {
            $this->datas['trigger'] = $triggerDatas;
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTarget(): Target
    {
        return $this->target;
    }

    public function getTrigger(): Trigger
    {
        return $this->trigger;
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
