<?php
namespace App\Entity;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ApiResource(
 *     collectionOperations={"get"},
 *     itemOperations={"get"}
 * )
 * @ApiFilter(SearchFilter::class, properties={"category": "exact"})
 * @ORM\Entity(repositoryClass="App\Repository\CheeseTypeRepository")
 */
class CheeseType
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
    private $category;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CheeseListing", mappedBy="cheeseType")
     */
    private $cheeseListing;
    public function __construct()
    {
        $this->cheeseListing = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getCategory(): ?string
    {
        return $this->category;
    }
    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }
    /**
     * @return Collection|CheeseListing[]
     */
    public function getCheeseListing(): Collection
    {
        return $this->cheeseListing;
    }
    public function addCheeseListing(CheeseListing $cheeseListing): self
    {
        if (!$this->cheeseListing->contains($cheeseListing)) {
            $this->cheeseListing[] = $cheeseListing;
            $cheeseListing->setCheeseType($this);
        }
        return $this;
    }
    public function removeCheeseListing(CheeseListing $cheeseListing): self
    {
        if ($this->cheeseListing->contains($cheeseListing)) {
            $this->cheeseListing->removeElement($cheeseListing);
            // set the owning side to null (unless already changed)
            if ($cheeseListing->getCheeseType() === $this) {
                $cheeseListing->setCheeseType(null);
            }
        }
        return $this;
    }
}