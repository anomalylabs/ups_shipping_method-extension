<?php namespace Anomaly\UpsShippingMethodExtension\Command;

use Anomaly\ConfigurationModule\Configuration\Contract\ConfigurationRepositoryInterface;
use Anomaly\ShippingModule\Method\Extension\MethodExtension;
use Anomaly\StoreModule\Contract\AddressInterface;
use Anomaly\StoreModule\Contract\ShippableInterface;
use Illuminate\Contracts\Config\Repository;
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
    protected $address;

    /**
     * Create a new GetQuote instance.
     *
     * @param MethodExtension $extension
     * @param ShippableInterface $shippable
     * @param AddressInterface $address
     */
    public function __construct(MethodExtension $extension, ShippableInterface $shippable, AddressInterface $address)
    {
        $this->shippable = $shippable;
        $this->extension = $extension;
        $this->address   = $address;
    }

    /**
     * Handle the command.
     *
     * @param ConfigurationRepositoryInterface $configuration
     * @param Repository $config
     * @return float
     */
    public function handle(ConfigurationRepositoryInterface $configuration, Repository $config)
    {
        $method = $this->extension->getMethod();

        /* @var Rate $rate */
        $rate = $this->dispatch(new GetRate());

        $code = $configuration->value('anomaly.extension.ups_shipping_method::service', $method->getId());

        $shipment = new Shipment();

        $shipment->setService((new Service())->setCode($code));

        $shipperAddress = $shipment->getShipper()->getAddress();
        $shipperAddress->setPostalCode(61241); // @todo

        $address = new Address();
        $address->setCountryCode('US'); // @todo
        $address->setPostalCode(61241); // @todo
        $address->setAddressLine1('109 Hilltop St'); // @todo
        $address->setAddressLine2(''); // @todo
        $address->setStateProvinceCode('IL'); // @todo

        $shipFrom = new ShipFrom();
        $shipFrom->setAddress($address);
        $shipFrom->setPhoneNumber('3097528581'); // @todo
        $shipFrom->setEmailAddress('ryan@pyrocms.com'); // @todo
        $shipFrom->setCompanyName('PyroCMS'); // @todo
        $shipFrom->setAttentionName('Ryan Thompson'); // @todo

        $shipment->setShipFrom($shipFrom);

        $shipTo = $shipment->getShipTo();
        $shipTo->setPhoneNumber($this->address->getPhone());
        $shipTo->setEmailAddress($this->address->getEmail());
        $shipTo->setCompanyName($this->address->getCompany());
        $shipTo->setAttentionName($this->address->getName());

        $shipToAddress = $shipTo->getAddress();
        $shipToAddress->setCountryCode($this->address->getCountry());
        $shipToAddress->setAddressLine1($this->address->getStreetAddress());
        //$shipToAddress->setAddressLine2($this->address->getaddress2());
        $shipToAddress->setPostalCode($this->address->getPostalCode());
        $shipToAddress->setStateProvinceCode($this->address->getState());

        $package = new Package();
        $package->getPackagingType()->setCode(PackagingType::PT_PACKAGE);

        /**
         * Default package weight.
         * This is required.
         */
        $package->getPackageWeight()->setWeight($this->shippable->getShippableWeight());

        $unit = new UnitOfMeasurement;
        $unit->setCode(
            $config->get(
                'streams::system.unit_system'
            ) == 'imperial' ? UnitOfMeasurement::UOM_LBS : UnitOfMeasurement::UOM_KGS
        );

        $package->getPackageWeight()->setUnitOfMeasurement($unit);

        if ($this->shippable->getShippableWidth()) {

            $dimensions = new Dimensions();
            $dimensions->setWidth($this->shippable->getShippableWidth());
            $dimensions->setHeight($this->shippable->getShippableHeight());
            $dimensions->setLength($this->shippable->getShippableLength());

            $unit = new UnitOfMeasurement;
            $unit->setCode(
                $config->get(
                    'streams::system.unit_system'
                ) == 'imperial' ? UnitOfMeasurement::UOM_IN : UnitOfMeasurement::UOM_CM
            );

            $dimensions->setUnitOfMeasurement($unit);
            $package->setDimensions($dimensions);
        }

        $shipment->addPackage($package);

        /* @var RateResponse $response */
        $response = $rate->getRate($shipment);

        /* @var RatedShipment $quote */
        $quote = $response->RatedShipment[0];

        return $quote->TotalCharges->MonetaryValue;
    }
}
