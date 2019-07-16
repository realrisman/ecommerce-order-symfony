<?php


namespace App\Service;


use Carbon\Carbon;
use Rs\JsonLines\JsonLines;
use SoapBox\Formatter\Formatter;
use Symfony\Component\Filesystem\Filesystem;

class ExportService
{
    protected $jsonLUrl;

    public function __construct($url = null)
    {
        if (!$url) {
            $this->jsonLUrl = $url;
        }

        $this->jsonLUrl = "https://s3-ap-southeast-2.amazonaws.com/catch-code-challenge/challenge-1-in.jsonl";
    }

    public function run($filename, $format)
    {
        $summaries = $this->getSummaries();

        $data = Formatter::make($summaries, Formatter::ARR);
        $formatedFile = $this->formatFile($format, $data);

        $filename = 'exportedFiles/'.$filename.'.'.$format;

        $fs = new Filesystem();
        if (!$fs->exists($filename)) {
            $fs->mkdir('exportedFiles');
            $fs->dumpFile($filename, $formatedFile);
        }

        return 'Export successfully! Please check your file under folder '.$filename;
    }

    private function formatFile($format, $data)
    {
        $formatedFile = $data;
        switch ($format) {
            case 'csv':
                $formatedFile = $data->toCsv();
                break;
            case 'json':
                $formatedFile = $data->toJson();
                break;
            case 'yaml':
                $formatedFile = $data->toYaml();
                break;
            case 'xml':
                $formatedFile = $data->toXml();
                break;
            default:
                $formatedFile = $data->toCsv();
                break;
        }

        return $formatedFile;
    }

    private function getSummaries()
    {
        $jsonLFile = \file_get_contents($this->jsonLUrl);
        $jsonFile = (new JsonLines())->deline($jsonLFile);
        $dataOrders = \json_decode($jsonFile, true);
        $summaries = [];

        foreach ($dataOrders as $order) {
            $data = [];
            $data['order_id'] = $order['order_id'];
            $data['order_datetime'] = $this->getOrderDateTime($order['order_date']);
            $data['total_order_value'] = $this->getTotalOrderValue($order['items'], $order['discounts']);
            $data['average_unit_price'] = $this->getAvgUnitPrice($order['items']);
            $data['distinct_unit_count'] = $this->getDistinctUnitCount($order['items']);
            $data['total_units_count'] = $this->getTotalUnitsCount($order['items']);
            $data['customer_state'] = $this->getCustomerState($order['customer']);

            array_push($summaries, $data);
        }

        return $summaries;
    }

    private function getOrderDateTime($date)
    {
        return Carbon::parse($date)->setTimezone('UTC')->toIso8601String();
    }

    private function getTotalOrderValue($items, $discounts)
    {
        $totalOrderValue = 0;
        foreach ($items as $item) {
            $unit_price = $item['unit_price'];
            $quantity = $item['quantity'];

            $totalPrice = $unit_price * $quantity;
            $totalOrderValue += $totalPrice;
        }

        if (!empty($discounts)) {
            foreach ($discounts as $discount) {
                $type = strtoupper($discount['type']);
                $value = $discount['value'];

                if ($type === 'DOLLAR') {
                    $totalOrderValue -= $value;
                }
                if ($type === 'PERCENTAGE') {
                    $totalDiscount = ($value * $totalOrderValue) / 100;
                    $totalOrderValue -= $totalDiscount;
                }
            }
        }

        return money_format('%.2n', $totalOrderValue);
    }

    private function getAvgUnitPrice($items)
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['unit_price'];
        }
        $avg = $total / count($items);

        return money_format('%.2n', $avg);
    }

    private function getDistinctUnitCount($items)
    {
        $productIds = [];
        foreach ($items as $item) {
            $productId = $item['product']['product_id'];
            if (!in_array($productId, $productIds, true)) {
                array_push($productIds, $productId);
            }
        }

        return count($productIds);
    }

    private function getTotalUnitsCount($items)
    {
        $totalUnits = 0;
        foreach ($items as $item) {
            $totalUnits += $item['quantity'];
        }

        return $totalUnits;
    }

    private function getCustomerState($customer)
    {
        return $customer['shipping_address']['state'];
    }

}