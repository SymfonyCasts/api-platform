<?php

namespace App\Serializer\Normalizer;

use App\Entity\User;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class UserNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private $normalizer;

    public function __construct(ObjectNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * @param User $object
     */
    public function normalize($object, $format = null, array $context = array()): array
    {
        if ($this->userIsOwner($object)) {
            $context['groups'][] = 'owner:read';
        }

        $data = $this->normalizer->normalize($object, $format, $context);

        // Here: add, edit, or delete some data

        return $data;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof User;
    }

    private function userIsOwner(User $user): bool
    {
        return mt_rand(0, 10) > 5;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
