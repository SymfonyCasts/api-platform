<?php

namespace App\Dto;

use App\Entity\CheeseListing;
use App\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

class CheeseListingInput
{
    /**
     * @var string
     * @Groups({"cheese:write", "user:write"})
     */
    public $title;

    /**
     * @var int
     * @Groups({"cheese:write", "user:write"})
     */
    public $price;

    /**
     * @var User
     * @Groups({"cheese:collection:post"})
     */
    public $owner;

    public $description;

    public function createOrUpdateEntity(?CheeseListing $cheeseListing): CheeseListing
    {
        if (!$cheeseListing) {
            $cheeseListing = new CheeseListing($this->title);
        }

        $cheeseListing->setDescription((string) $this->description);
        $cheeseListing->setPrice((int) $this->price);
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
