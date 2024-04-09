<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['group:order-all'])]

    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[Groups(['group:order-all'])]

    private ?Profile $profile = null;

    /**
     * @var Collection<int, OrderProduct>
     */
    #[ORM\OneToMany(targetEntity: OrderProduct::class, mappedBy: 'orders')]
    #[Groups(['group:order-all'])]
    private Collection $orderProduct;

    #[ORM\Column]
    #[Groups(['group:order-all'])]
    private ?float $total = null;

    public function __construct()
    {
        $this->orderProduct = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(?Profile $profile): static
    {
        $this->profile = $profile;

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
            $orderProduct->setOrders($this);
        }

        return $this;
    }

    public function removeOrderProduct(OrderProduct $orderProduct): static
    {
        if ($this->orderProduct->removeElement($orderProduct)) {
            // set the owning side to null (unless already changed)
            if ($orderProduct->getOrders() === $this) {
                $orderProduct->setOrders(null);
            }
        }

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): static
    {
        $this->total = $total;

        return $this;
    }
}
