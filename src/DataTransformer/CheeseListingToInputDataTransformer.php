<?php

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Dto\CheeseListingInput;
use App\Entity\CheeseListing;

class CheeseListingToInputDataTransformer implements DataTransformerInterface
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param CheeseListingInput $input
     */
    public function transform($input, string $to, array $context = [])
    {
        $this->validator->validate($input);

        if (isset($context[AbstractItemNormalizer::OBJECT_TO_POPULATE])) {
            $cheeseListing = $context[AbstractItemNormalizer::OBJECT_TO_POPULATE];
        } else {
            $cheeseListing = new CheeseListing($input->title);
        }

        $cheeseListing->setDescription($input->description);
        $cheeseListing->setPrice($input->price);
        if ($input->owner !== null) {
            $cheeseListing->setOwner($input->owner);
        }

        return $cheeseListing;
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        if ($data instanceof CheeseListing) {
            // already transformed
            return false;
        }

        return $to === CheeseListing::class && ($context['input']['class'] ?? null) === CheeseListingInput::class;
    }
}
