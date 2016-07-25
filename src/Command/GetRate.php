<?php namespace Anomaly\UpsShippingMethodExtension\Command;

use Anomaly\ShippingModule\Method\Contract\MethodInterface;
use Anomaly\ShippingModule\Method\Extension\MethodExtension;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Config\Repository;
use Ups\Rate;

/**
 * Class GetRate
 *
 * @link          http://pyrocms.com/
 * @author        PyroCMS, Inc. <support@pyrocms.com>
 * @author        Ryan Thompson <ryan@pyrocms.com>
 * @package       Anomaly\UpsShippingMethodExtension\Command
 */
class GetRate implements SelfHandling
{

    /**
     * The method interface.
     *
     * @var MethodInterface
     */
    protected $method;

    /**
     * The method extension.
     *
     * @var MethodExtension
     */
    protected $extension;

    /**
     * Handle the command.
     *
     * @param Repository $config
     * @return Rate
     */
    public function handle(Repository $config)
    {
        return new Rate(
            $config->get('anomaly.extension.ups_shipping_method::config.access_key'),
            $config->get('anomaly.extension.ups_shipping_method::config.username'),
            $config->get('anomaly.extension.ups_shipping_method::config.password')
        );
    }
}
