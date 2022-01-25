<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 */
class Category
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    public function getId(): ?int
    {
        return $this->id;
    }

     /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=Product::class, mappedBy="category_id")
     */
    private $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }


    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setCategory($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getCategory() === $this) {
                $product->setCategory(null);
            }
        }

        return $this;
    }

    /**
     * @var DateTime
     * @ORM\Column(name="created", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $created;

    /**
     * @var DateTime
     * @ORM\Column(name="updated", type="datetime" , nullable="true")
     * @Gedmo\Timestampable(on="update")
     */
    protected $updated;


    /**
     * Set created
     * @param DateTime $created
     * @return AbstractEntity
     */
    public function setCreated($created)
    {
        if(isset($created)){
            $this->created = $created;
        }else{
            $this->created = date("Y-m-d H:i:s");
        }
        return $this;
    }

    /**
     * Get created
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->created->format('Y-m-d');
    }

    /**
     * Set updated
     * @param DateTime $updated
     * @return AbstractEntity
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
        return $this;
    }

    /**
     * Get updated
     * @return DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }
}
