<?php
namespace App\Api\Model;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource(
 *     collectionOperations={"get"},
 *     itemOperations={"get"}
 * )
 */
class CheeseTypeReview {

    /**
     * @ApiProperty(identifier=true)
     */
    private $id;

    private $cheeseType;

    private $review;

    public function __construct($id, $cheeseType, $review)
    {
        $this->id = $id;
        $this->cheeseType = $cheeseType;
        $this->review = $review;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getCheeseType(): ?string
    {
        return $this->cheeseType;
    }

    public function setCheeseType(string $cheeseType): self
    {
        $this->cheeseType = $cheeseType;

        return $this;
    }

    public function getReview(): ?string
    {
        return $this->review;
    }

    public function setReview(string $review): self
    {
        $this->review = $review;

        return $this;
    }
}
