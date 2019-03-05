<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Annotation\ApiSubresource;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CheeseListingRepository")
 * @ApiFilter(BooleanFilter::class, properties={"isStinky"})
 * @ApiFilter(DateFilter::class, properties={"createdAt"})
 * @ApiResource(
 *     routePrefix="/market",
 *     collectionOperations={
 *         "get"={
 *               "path"="/cheeses"
 *          },
 *          "post"={
 *              "path"="/cheeses",
 *          }
 *     },
 *     itemOperations={
 *         "get"={
 *              "path"="/cheeses/{id}"
 *          },
 *          "put"={
 *              "path"="/cheeses/{id}",
 *          },
 *          "delete"={
 *              "path"="/cheeses/{id}"
 *          }
 *     },
 *     subresourceOperations={
 *          "user_get_subresource"= {
 *              "path"="/market/cheeses/{id}/user"
 *          }
 *     }
 * )
 */
class CheeseListing
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isStinky;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="cheeseListings")
     * @ORM\JoinColumn(nullable=false)
     * @ApiSubresource(maxDepth=1)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CheeseType", inversedBy="cheeseListing")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cheeseType;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getIsStinky(): ?bool
    {
        return $this->isStinky;
    }

    public function setIsStinky(bool $isStinky): self
    {
        $this->isStinky = $isStinky;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCheeseType(): ?CheeseType
    {
        return $this->cheeseType;
    }
    public function setCheeseType(?CheeseType $cheeseType): self
    {
        $this->cheeseType = $cheeseType;
        return $this;
    }
}
