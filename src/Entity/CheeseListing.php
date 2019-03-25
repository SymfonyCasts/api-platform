<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use App\Controller\PublishCheeseListing;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CheeseListingRepository")
 * @ApiResource(
 *     shortName="cheeses",
 *     normalizationContext={"groups"={"read"}},
 *     denormalizationContext={"groups"={"write"}},
 *     collectionOperations={"get", "post"},
 *     itemOperations={
 *         "get",
 *         "put",
 *         "publish"={
 *              "method"="POST",
 *              "path"="/cheeses/{id}/publish",
 *              "controller"=PublishCheeseListing::class,
 *              "swagger_context"={
 *                  "summary" = "Publish the CheeseListing swagger",
 *                  "description" =
 *                      "# Sell the cheese on the market",
 *
 *                  "parameters"={
 *                      {
 *                          "name" = "id",
 *                          "in" = "path",
 *                          "required" = "true",
 *                          "type" = "string",
 *                      }
 *                  },
 *                  "responses"={
 *                      "201"={
 *                          "description" = "The CheeseListing has been published"
 *                      }
 *                  }
 *              },
 *              "openapi_context"={
 *                  "summary" = "Publish the CheeseListing openapi",
 *                  "description" =
 *                      "# Sell the cheese on the market",
 *
 *                  "parameters" = {
 *                      {
 *                          "name" = "id",
 *                          "in" = "path",
 *                          "description": "CheeseListing ID",
 *                          "required" = "true",
 *                          "schema"={
 *                              "type"="string"
 *                          }
 *
 *                      }
 *                  },
 *                  "responses"={
 *                      "201"={
 *                          "description" = "The CheeseListing has been published"
 *                      }
 *                  }
 *              }
 *          }
 *     },
 *
 *     iri="https://schema.org/Offer"
 * )
 * @ApiFilter(BooleanFilter::class, properties={"isPublished"})
 * @ApiFilter(DateFilter::class, properties={"createdAt"})
 * @ApiFilter(SearchFilter::class, properties={"user": "exact"})
 */
class CheeseListing
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     */
    private $id;

    /**
     * @Assert\NotBlank
     * @Assert\Length(
     *     min = 2,
     *     max = 50
     * )
     * @ORM\Column(type="string", length=255)
     * @ApiProperty(
     *     iri="http://schema.org/itemOffered",
     *     attributes={
     *          "swagger_context"={
     *             "example"="Firm Round Gouda Cheese v2"
     *         },
     *         "openapi_context"={
     *             "example"="Firm Round Gouda Cheese v3"
     *         }
     *     }
     * )
     * @Groups({"read", "write"})
     */
    private $title;

    /**
     * @Assert\NotBlank
     * @Assert\Length(
     *     max = 500
     * )
     * @ORM\Column(type="text")
     * @ApiProperty(iri="https://schema.org/description")
     * @Groups({"read", "write"})
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     * @ApiProperty(iri="https://schema.org/validFrom")
     * @Groups({"admin:output"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"admin:output"})
     */
    private $isPublished;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="cheeseListings")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read", "write"})
     * @ApiProperty(readableLink=false)
     * @ApiSubresource(maxDepth=1)
     */
    private $user;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->isPublished = false;
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
        $this->description = '<p>'.$description.'</p>';

        return $this;
    }


    /**
     * @Groups({"admin:input"})
     */
    public function setRawDescription(string $rawDescription): self
    {
        $this->description = $rawDescription;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @Groups({"read"})
     */
    public function getCreatedAgo()
    {
        return Carbon::instance($this->getCreatedAt())->diffForHumans();
    }

    public function getIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

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
}
