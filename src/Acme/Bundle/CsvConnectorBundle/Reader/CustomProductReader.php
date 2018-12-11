<?php

namespace Acme\Bundle\CsvConnectorBundle\Reader;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\Reader\File\Csv\Reader;
use Pim\Component\Connector\Reader\File\FileIteratorFactory;
use Pim\Component\Connector\Reader\File\FileReaderInterface;
use Pim\Component\Connector\Reader\File\MediaPathTransformer;

/**
 * Product csv reader
 *
 * This specialized csv reader exists to replace relative media path to absolute path, in order for later process to
 * know where to find the files.
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CustomProductReader extends Reader implements FileReaderInterface
{
    /** @var MediaPathTransformer */
    protected $mediaPathTransformer;

    /**
     * @param FileIteratorFactory     $fileIteratorFactory
     * @param ArrayConverterInterface $converter
     * @param MediaPathTransformer    $mediaPathTransformer
     * @param array                   $options
     */
    public function __construct(
        FileIteratorFactory $fileIteratorFactory,
        ArrayConverterInterface $converter,
        MediaPathTransformer $mediaPathTransformer,
        array $options = []
    ) {
        parent::__construct($fileIteratorFactory, $converter, $options);

        $this->mediaPathTransformer = $mediaPathTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $data = parent::read();

        if (!is_array($data) || !isset($data['values'])) {
            return $data;
        }

        $data['values'] = $this->mediaPathTransformer
            ->transform($data['values'], $this->fileIterator->getDirectoryPath());

        $jobParameters = $this->stepExecution->getJobParameters();
        $categoriesValue = $jobParameters->get('categoriesValue');
        $familyValue = $jobParameters->get('familyValue');

        if (!empty($categoriesValue)){
            $data['categories'] = explode(',', $categoriesValue);
        }
        if (!empty($familyValue)) {
            $data['family'] = $familyValue;
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function getArrayConverterOptions()
    {
        $jobParameters = $this->stepExecution->getJobParameters();

        return [
            // for the array converters
            'mapping'           => [
                $jobParameters->get('familyColumn')     => 'family',
                $jobParameters->get('categoriesColumn') => 'categories',
                $jobParameters->get('groupsColumn')     => 'groups',
                $jobParameters->get('skuColumn') => 'sku',
                $jobParameters->get('priceColumn') => 'price_value-EUR',
                $jobParameters->get('eanColumn') => 'price_ean',
                $jobParameters->get('isbnColumn') => 'price_isbn',
                $jobParameters->get('deeplinkColumn') => 'price_deeplink',
                $jobParameters->get('priceConditionColumn') => 'price_condition',
            ],
            'with_associations' => false,

            // for the delocalization
            'decimal_separator' => $jobParameters->get('decimalSeparator'),
            'date_format'       => $jobParameters->get('dateFormat')
        ];
    }
}