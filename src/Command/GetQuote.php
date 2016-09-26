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
        $shipperAddress->setPostalCode($origin->getPostalCode());

        $address = new Address();
        $address->setCountryCode($origin->getCountry());
        $address->setPostalCode($origin->getPostalCode());
        $address->setAddressLine1($origin->getAddress1());
        $address->setAddressLine2($origin->getAddress2());
        $address->setStateProvinceCode($origin->getState());

        $shipFrom = new ShipFrom();
        $shipFrom->setAddress($address);
        $shipFrom->setPhoneNumber($origin->getPhone());
        $shipFrom->setEmailAddress($origin->getEmail());
        $shipFrom->setCompanyName($origin->getBusiness());
        $shipFrom->setAttentionName($origin->getContact());

        $shipment->setShipFrom($shipFrom);

        $shipTo = $shipment->getShipTo();
        $shipTo->setPhoneNumber(array_get($this->parameters, 'phone'));
        $shipTo->setEmailAddress(array_get($this->parameters, 'email'));
        $shipTo->setCompanyName(array_get($this->parameters, 'business'));
        $shipTo->setAttentionName(array_get($this->parameters, 'contact'));

        $shipToAddress = $shipTo->getAddress();
        $shipToAddress->setCountryCode(array_get($this->parameters, 'country'));
        $shipToAddress->setAddressLine1(array_get($this->parameters, 'address1'));
        $shipToAddress->setAddressLine2(array_get($this->parameters, 'address2'));
        $shipToAddress->setPostalCode(array_get($this->parameters, 'postal_code'));
        $shipToAddress->setStateProvinceCode(array_get($this->parameters, 'state'));

        $package = new Package();
        $package->getPackagingType()->setCode(PackagingType::PT_PACKAGE);

        /**
         * Default package weight.
         * This is required.
         */
        $package->getPackageWeight()->setWeight(10);

        if ($this->shippable->itemHasDimensions()) {

            $dimensions = new Dimensions();
            $dimensions->setWidth($this->shippable->getItemWidth());
            $dimensions->setHeight($this->shippable->getItemHeight());
            $dimensions->setLength($this->shippable->getItemLength());

            $unit = new UnitOfMeasurement;
            $unit->setCode(
                $this->shippable->getItemUnitSystem() ? UnitOfMeasurement::UOM_IN : UnitOfMeasurement::UOM_CM
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
