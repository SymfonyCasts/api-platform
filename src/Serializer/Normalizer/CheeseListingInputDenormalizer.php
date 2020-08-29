<?php

namespace App\Serializer\Normalizer;

use App\Dto\CheeseListingInput;
use App\Entity\CheeseListing;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CheeseListingInputDenormalizer implements DenormalizerInterface, CacheableSupportsMethodInterface
{
    private $objectNormalizer;

    public function __construct(ObjectNormalizer $objectNormalizer)
    {
        $this->objectNormalizer = $objectNormalizer;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $dto = new CheeseListingInput();
        $dto->title = 'I am set in the denormalizer!';

        $context[AbstractNormalizer::OBJECT_TO_POPULATE] = $dto;

        return $this->objectNormalizer->denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return $type === CheeseListingInput::class;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    private function createDto(array $context): ?CheeseListingInput
    {
        $entity = $context['object_to_populate'] ?? false;

        // not an edit, so just return an empty DTO
        if (!$entity) {

        }

        if (!$entity instanceof CheeseListing) {
            throw new \Exception(sprintf('Unexpected resource class "%s"', get_class($entity)));
        }

        $dto = new CheeseListingInput();
        $dto->title = $entity->getTitle();
        $dto->price = $entity->getPrice();
        $dto->description = $entity->getDescription();
        $dto->owner = $entity->getOwner();

        return $dto;
    }
}
