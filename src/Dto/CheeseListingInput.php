<?php

namespace App\Dto;

use App\Entity\CheeseListing;
use App\Entity\User;
use App\Validator\IsValidOwner;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class CheeseListingInput
{
    /**
     * @var string
     * @Groups({"cheese:write", "user:write"})
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     maxMessage="Describe your cheese in 50 chars or less"
     * )
     */
    public $title;

    /**
     * @var int
     * @Groups({"cheese:write", "user:write"})
     * @Assert\NotBlank()
     */
    public $price;

    /**
     * @var User
     * @Groups({"cheese:collection:post"})
     * @IsValidOwner()
     */
    public $owner;

    /**
     * @Assert\NotBlank()
     */
    public $description;

    public function createOrUpdateEntity(?CheeseListing $cheeseListing): CheeseListing
    {
        if (!$cheeseListing) {
            $cheeseListing = new CheeseListing($this->title);
        }

        $cheeseListing->setDescription($this->description);
        $cheeseListing->setPrice($this->price);
        $cheeseListing->setOwner($this->owner);

        return $cheeseListing;
    }

    /**
     * The description of the cheese as raw text.
     *
     * @Groups({"cheese:write", "user:write"})
     * @SerializedName("description")
     */
    public function setTextDescription(string $description): self
    {
        $this->description = nl2br($description);

        return $this;
    }
}
