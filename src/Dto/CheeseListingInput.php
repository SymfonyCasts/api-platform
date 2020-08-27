<?php

namespace App\Dto;

use App\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

class CheeseListingInput
{
    /**
     * @Groups({"cheese:write", "user:write"})
     */
    private $title;

    /**
     * @Groups({"cheese:write", "user:write"})
     */
    private $price;

    /**
     * @var User
     * @Groups({"cheese:write"})
     */
    private $owner;

    private $description;

    public function __construct($title, User $owner)
    {
        $this->title = $title;
        $this->owner = $owner;
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
