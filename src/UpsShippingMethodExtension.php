<?php namespace Anomaly\UpsShippingMethodExtension;

use Anomaly\ShippingModule\Method\Extension\MethodExtension;
use Anomaly\StoreModule\Contract\AddressInterface;
use Anomaly\StoreModule\Contract\ShippableInterface;
use Anomaly\UpsShippingMethodExtension\Command\GetQuote;

/**
 * Class UpsShippingMethodExtension
 *
 * @link          http://pyrocms.com/
 * @author        PyroCMS, Inc. <support@pyrocms.com>
 * @author        Ryan Thompson <ryan@pyrocms.com>
 * @package       Anomaly\UpsShippingMethodExtension
 */
class UpsShippingMethodExtension extends MethodExtension
{

    /**
     * This extension provides the UPS shipping
     * type for the shipping module.
     *
     * @var null|string
     */
    protected $provides = 'anomaly.module.shipping::method.ups';

    /**
     * Get a shipping quote.
     *
     * @param ShippableInterface $shippable
     * @param AddressInterface $address
     * @return float
     */
    public function quote(ShippableInterface $shippable, AddressInterface $address)
    {
        return $this->dispatch(new GetQuote($this, $shippable, $address));
    }
}
