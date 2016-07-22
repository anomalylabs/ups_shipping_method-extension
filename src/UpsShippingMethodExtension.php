<?php namespace Anomaly\UpsShippingMethodExtension;

use Anomaly\OrdersModule\Order\Contract\OrderInterface;
use Anomaly\ShippingModule\Method\Extension\MethodExtension;

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
     * Return a quote for an order.
     *
     * @param OrderInterface $order
     * @throws \Exception
     * @return float
     */
    public function quote(OrderInterface $order)
    {
        return 10;
    }
}
