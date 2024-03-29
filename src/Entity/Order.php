<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 *
 * @ORM\Table(name="orders")
 * @ORM\Entity(repositoryClass="App\Repository\OrderRepository")
 */
class Order
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $order_date;

    /**
     * @ORM\Column(type="json")
     */
    private $discounts = [];

    /**
     * @ORM\Column(type="decimal", precision=10, scale=0, nullable=true)
     */
    private $shipping_price;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer", inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getOrderDate(): ?\DateTimeInterface
    {
        return $this->order_date;
    }

    public function setOrderDate(\DateTimeInterface $order_date): self
    {
        $this->order_date = $order_date;

        return $this;
    }

    public function getDiscounts(): ?array
    {
        return $this->discounts;
    }

    public function setDiscounts(array $discounts): self
    {
        $this->discounts = $discounts;

        return $this;
    }

    public function getShippingPrice()
    {
        return $this->shipping_price;
    }

    public function setShippingPrice($shipping_price): self
    {
        $this->shipping_price = $shipping_price;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }
}
