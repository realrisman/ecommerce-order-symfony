<?php


namespace App\Service;

use App\Entity\Brand;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\Product;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Rs\JsonLines\JsonLines;

class ImportService
{
    protected $jsonLUrl;

    private $entityManager;
    private $orderRepo;
    private $customerRepo;
    private $productRepo;
    private $brandRepo;

    public function __construct(EntityManagerInterface $entityManager, $url = null)
    {
        if (!$url) {
            $this->jsonLUrl = $url;
        }

        $this->jsonLUrl = "https://s3-ap-southeast-2.amazonaws.com/catch-code-challenge/challenge-1-in.jsonl";

        $this->entityManager = $entityManager;
        $this->orderRepo = $entityManager->getRepository(Order::class);
        $this->customerRepo = $entityManager->getRepository(Customer::class);
        $this->productRepo = $entityManager->getRepository(Product::class);
        $this->brandRepo = $entityManager->getRepository(Brand::class);
    }

    public function run()
    {
        $dataOrders = $this->getDataOrders();

        foreach ($dataOrders as $order) {
            $this->insertCustomerOrder($order);
            $this->insertBrandProduct($order);
        }

        return 'Import successfully!';
    }

    private function getDataOrders()
    {
        $jsonLFile = \file_get_contents($this->jsonLUrl);
        $jsonFile = (new JsonLines())->deline($jsonLFile);
        $dataOrders = \json_decode($jsonFile, true);

        return $dataOrders;
    }

    private function insertCustomerOrder($data)
    {
        $dataCustomer = $data['customer'];
        $orderDate = Carbon::parse($data['order_date']);

        $customer = $this->customerRepo->find($dataCustomer['customer_id']);
        $order = $this->orderRepo->find($data['order_id']);

        if (!$customer) {
            $customer = new Customer();
        }

        if (!$order) {
            $order = new Order();
        }

        $customer->setId($dataCustomer['customer_id']);
        $customer->setFirstName($dataCustomer['first_name']);
        $customer->setLastName($dataCustomer['last_name']);
        $customer->setPhone($dataCustomer['phone']);
        $customer->setEmail($dataCustomer['email']);
        $customer->setShippingAddress($dataCustomer['shipping_address']);

        $order->setId($data['order_id']);
        $order->setCustomer($customer);
        $order->setOrderDate($orderDate);
        $order->setDiscounts($data['discounts']);
        $order->setShippingPrice($data['shipping_price']);

        $this->entityManager->persist($customer);
        $this->entityManager->persist($order);
        $this->entityManager->flush();
    }

    private function insertBrandProduct($data)
    {
        $orderItems = $data['items'];

        foreach ($orderItems as $item) {
            $dataProduct = $item['product'];
            $dataBrand = $dataProduct['brand'];

            $brand = $this->brandRepo->find($dataBrand['id']);
            $product = $this->productRepo->find($dataProduct['product_id']);

            if (!$brand) {
                $brand = new Brand();
            }

            if (!$product) {
                $product = new Product();
            }

            $brand->setId($dataBrand['id']);
            $brand->setName($dataBrand['name']);

            $product->setId($dataProduct['product_id']);
            $product->setBrand($brand);
            $product->setTitle($dataProduct['title']);
            $product->setSubtitle($dataProduct['subtitle']);
            $product->setImage($dataProduct['image']);
            $product->setThumbnail($dataProduct['thumbnail']);
            $product->setUrl($dataProduct['url']);
            $product->setCategory($dataProduct['category']);
            $product->setUpc($dataProduct['upc']);
            $product->setGtin14($dataProduct['gtin14']);

            $this->entityManager->persist($brand);
            $this->entityManager->persist($product);
            $this->entityManager->flush();
        }
    }
}