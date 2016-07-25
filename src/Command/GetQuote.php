<?php namespace Anomaly\UpsShippingMethodExtension\Command;

use Anomaly\ConfigurationModule\Configuration\Contract\ConfigurationRepositoryInterface;
use Anomaly\OrdersModule\Order\Contract\OrderInterface;
use Anomaly\ShippingModule\Method\Extension\MethodExtension;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Ups\Entity\Address;
use Ups\Entity\Dimensions;
use Ups\Entity\Package;
use Ups\Entity\PackagingType;
use Ups\Entity\RatedShipment;
use Ups\Entity\RateResponse;
use Ups\Entity\Service;
use Ups\Entity\ShipFrom;
use Ups\Entity\Shipment;
use Ups\Entity\UnitOfMeasurement;
use Ups\Rate;

/**
 * Class GetQuote
 *
 * @link          http://pyrocms.com/
 * @author        PyroCMS, Inc. <support@pyrocms.com>
 * @author        Ryan Thompson <ryan@pyrocms.com>
 * @package       Anomaly\UpsShippingMethodExtension\Command
 */
class GetQuote implements SelfHandling
{

    use DispatchesJobs;

    /**
     * The order interface.
     *
     * @var OrderInterface
     */
    protected $order;

    /**
     * The shipping extension.
     *
     * @var MethodExtension
     */
    protected $extension;

    /**
     * Create a new GetQuote instance.
     *
     * @param MethodExtension $extension
     * @param OrderInterface  $order
     */
    public function __construct(MethodExtension $extension, OrderInterface $order)
    {
        $this->order     = $order;
        $this->extension = $extension;
    }

    /**
     * Handle the command.
     *
     * @param ConfigurationRepositoryInterface $configuration
     */
    public function handle(ConfigurationRepositoryInterface $configuration)
    {
        $method = $this->order->getShippingMethod();

        /* @var Rate $rate */
        $rate = $this->dispatch(new GetRate($method));

        $code = $configuration->value('anomaly.extension.ups_shipping_method::service', $method->getSlug());

        $shipment = new Shipment();

        /**
         * Set the shipping service.
         */
        $service = new Service();
        $service->setCode($code);

        $shipment->setService($service);

        /**
         * Set the shipper's information.
         */
        $shipperAddress = $shipment
            ->getShipper()
            ->getAddress();
        $shipperAddress->setPostalCode('61241');

        /**
         * Set the origin information.
         */
        $fromAddress = new Address();
        $fromAddress->setPostalCode('61241');
        $shipFrom = new ShipFrom();
        $shipFrom->setAddress($fromAddress);

        $shipment->setShipFrom($shipFrom);

        /**
         * Set the destination information.
         */
        $shipTo = $shipment->getShipTo();
        $shipTo->setCompanyName('Test Ship To');
        $shipToAddress = $shipTo->getAddress();
        $shipToAddress->setPostalCode('99205');

        /**
         * Add a package to the shipment.
         */
        $package = new Package();
        $package->getPackagingType()->setCode(PackagingType::PT_PACKAGE);
        $package->getPackageWeight()->setWeight(10);

        $dimensions = new Dimensions();
        $dimensions->setHeight(10);
        $dimensions->setWidth(10);
        $dimensions->setLength(10);

        $unit = new UnitOfMeasurement();
        $unit->setCode(UnitOfMeasurement::UOM_IN);

        $dimensions->setUnitOfMeasurement($unit);
        $package->setDimensions($dimensions);

        $shipment->addPackage($package);

        /* @var RateResponse $response */
        $response = $rate->getRate($shipment);

        /* @var RatedShipment $quote */
        $quote = $response->RatedShipment[0];

        return $quote->TotalCharges->MonetaryValue;
    }
}
