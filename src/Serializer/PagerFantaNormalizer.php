<?php

namespace Code202\Security\Serializer;

use Pagerfanta\Pagerfanta;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PagerFantaNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    public function normalize($pager, string $format = null, array $context = []): array
    {
        $results = [];
        foreach ($pager->getCurrentPageResults()->getArrayCopy() as $res) {
            $results[] = $this->normalizer->normalize($res, $format, $context);
        }

        $data = [
            'nbResults' => $pager->getNbResults(),
            'currentPage' => $pager->getCurrentPage(),
            'maxPerPage' => $pager->getMaxPerPage(),
            'nbPages' => $pager->getNbPages(),
            'haveToPaginate' => $pager->haveToPaginate(),
            'hasPreviousPage' => $pager->hasPreviousPage(),
            'previousPage' => $pager->hasPreviousPage() ? $pager->getPreviousPage() : null,
            'hasNextPage' => $pager->hasNextPage(),
            'nextPage' => $pager->hasNextPage() ? $pager->getNextPage() : null,
            'currentPageOffsetStart' => $pager->getCurrentPageOffsetStart(),
            'currentPageOffsetEnd' => $pager->getCurrentPageOffsetEnd(),
            'results' => $results,
        ];

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof Pagerfanta;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Pagerfanta::class => true,
        ];
    }
}
