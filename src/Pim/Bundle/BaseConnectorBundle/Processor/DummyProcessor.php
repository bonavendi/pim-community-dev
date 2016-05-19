<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;

/**
 * Dummy step, can be use to do nothing until you'll have concrete implementation
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated please use Pim\Component\Connector\Processor\DummyItemProcessor will be removed in 1.6
 */
class DummyProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }
}