<?php

namespace Code202\Security\Serializer;

use Pagerfanta\Pagerfanta;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class PagerFantaNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function normalize($pager, $format = null, array $context = [])
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
