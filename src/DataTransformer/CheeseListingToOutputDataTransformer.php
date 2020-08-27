<?php

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Dto\CheeseListingOutput;
use App\Entity\CheeseListing;

class CheeseListingToOutputDataTransformer implements DataTransformerInterface
{
    public function transform($object, string $to, array $context = [])
    {

    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {

    }
}
