<?php namespace Anomaly\UpsShippingMethodExtension\Command;

use Anomaly\ConfigurationModule\Configuration\Contract\ConfigurationRepositoryInterface;
use Anomaly\ShippingModule\Method\Extension\MethodExtension;
use Anomaly\ShippingModule\Shippable\Contract\ShippableInterface;
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
 * @link   http://pyrocms.com/
 * @author PyroCMS, Inc. <support@pyrocms.com>
 * @author Ryan Thompson <ryan@pyrocms.com>
 */
class GetQuote
{

    use DispatchesJobs;

    /**
     * The shippable interface.
     *
     * @var ShippableInterface
     */
    protected $shippable;

    /**
     * The shipping extension.
     *
     * @var MethodExtension
     */
    protected $extension;

    /**
     * The parameter array.
     *
     * @var array
     */
    protected $parameters;

    /**
     * Create a new GetQuote instance.
     *
     * @param MethodExtension    $extension
     * @param ShippableInterface $shippable
     * @param array              $parameters
     */
    public function __construct(MethodExtension $extension, ShippableInterface $shippable, array $parameters = [])
    {
        $this->shippable  = $shippable;
        $this->extension  = $extension;
        $this->parameters = $parameters;
    }

    /**
     * Handle the command.
     *
     * @param ConfigurationRepositoryInterface $configuration
     */
    public function handle(ConfigurationRepositoryInterface $configuration)
    {
        $origin = $this->shippable->getOrigin();
        $method = $this->extension->getMethod();

        /* @var Rate $rate */
        $rate = $this->dispatch(new GetRate($method));

        $code = $configuration->value('anomaly.extension.ups_shipping_method::service', $method->getId());

        $shipment = new Shipment();

        $shipment->setService((new Service())->setCode($code));

        $shipperAddress = $shipment->getShipper()->getAddress();
        $shipperAddress->setPostalCode('99205');

        $address = new Address();
        $address->setPostalCode('99205');

        $shipFrom = new ShipFrom();
        $shipFrom->setAddress($address);

        $shipment->setShipFrom($shipFrom);

        $shipTo = $shipment->getShipTo();
        $shipTo->setCompanyName('Test Ship To');
        $shipToAddress = $shipTo->getAddress();
        $shipToAddress->setPostalCode('99205');

        $package = new Package();
        $package->getPackagingType()->setCode(PackagingType::PT_PACKAGE);
        $package->getPackageWeight()->setWeight(10);

        $dimensions = new Dimensions();
        $dimensions->setHeight(10);
        $dimensions->setWidth(10);
        $dimensions->setLength(10);

        $unit = new UnitOfMeasurement;
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
