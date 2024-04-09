<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['group:product-all','group:order-product'])]

    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['group:product-all','group:order-product'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['group:product-all','group:order-product'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['group:product-all','group:order-product'])]
    private ?float $price = null;

    /**
     * @var Collection<int, OrderProduct>
     */
    #[ORM\OneToMany(targetEntity: OrderProduct::class, mappedBy: 'product')]
    private Collection $orderProduct;

    /**
     * @var Collection<int, OrderProduct>
     */


    public function __construct()
    {
        $this->orderProduct = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection<int, OrderProduct>
     */
    public function getOrderProduct(): Collection
    {
        return $this->orderProduct;
    }

    public function addOrderProduct(OrderProduct $orderProduct): static
    {
        if (!$this->orderProduct->contains($orderProduct)) {
            $this->orderProduct->add($orderProduct);
            $orderProduct->setProduct($this);
        }

        return $this;
    }

    public function removeOrderProduct(OrderProduct $orderProduct): static
    {
        if ($this->orderProduct->removeElement($orderProduct)) {
            // set the owning side to null (unless already changed)
            if ($orderProduct->getProduct() === $this) {
                $orderProduct->setProduct(null);
            }
        }

        return $this;
    }


}
