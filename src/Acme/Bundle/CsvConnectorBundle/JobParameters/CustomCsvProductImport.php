<?php

namespace Acme\Bundle\CsvConnectorBundle\JobParameters;


use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Component\Localization\Localizer\LocalizerInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class CustomCsvProductImport implements
    ConstraintCollectionProviderInterface,
    DefaultValuesProviderInterface
{
    /** @var DefaultValuesProviderInterface */
    private $baseDefaultValuesProvider;
    /** @var ConstraintCollectionProviderInterface */
    private $baseConstraintCollectionProvider;
    /** @var string[] */
    private $supportedJobNames;

    /**
     * @param DefaultValuesProviderInterface $baseDefaultValuesProvider
     * @param ConstraintCollectionProviderInterface $baseConstraintCollectionProvider
     * @param string[] $supportedJobNames
     */
    public function __construct(
        DefaultValuesProviderInterface $baseDefaultValuesProvider,
        ConstraintCollectionProviderInterface $baseConstraintCollectionProvider,
        array $supportedJobNames
    )
    {
        $this->baseDefaultValuesProvider = $baseDefaultValuesProvider;
        $this->baseConstraintCollectionProvider = $baseConstraintCollectionProvider;
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValues()
    {
        return array_merge(
            $this->baseDefaultValuesProvider->getDefaultValues(),
            [
                'decimalSeparator' => LocalizerInterface::DEFAULT_DECIMAL_SEPARATOR,
                'dateFormat' => LocalizerInterface::DEFAULT_DATE_FORMAT,
                'enabled' => true,
                'categoriesColumn' => 'categories',
                'familyColumn' => 'family',
                'groupsColumn' => 'groups',
                'enabledComparison' => true,
                'realTimeVersioning' => true,
                'priceColumn' => 'price',
                'skuColumn' => 'sku',
                'eanColumn' => 'ean',
                'isbnColumn' => 'isbn',
                'deeplinkColumn' => 'deeplink',
                'priceConditionColumn' => 'price_condition',
                'categoriesValue' => '',
                'familyValue' => 'prices'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection()
    {
        $baseConstraints = $this->baseConstraintCollectionProvider->getConstraintCollection();
        $constraintFields = array_merge(
            $baseConstraints->fields,
            [
                'decimalSeparator' => new NotBlank(),
                'dateFormat' => new NotBlank(),
                'enabled' => [
                    new Type('bool')
                ],
                'categoriesColumn' => [
                    new NotBlank()
                ],
                'familyColumn' => [
                    new NotBlank()
                ],
                'groupsColumn' => [
                    new NotBlank()
                ],
                'enabledComparison' => [
                    new Type('bool')
                ],
                'realTimeVersioning' => [
                    new Type('bool')
                ],
                'priceColumn' => new Type('string'),
                'skuColumn' => new Type('string'),
                'eanColumn' => new Type('string'),
                'isbnColumn' => new Type('string'),
                'deeplinkColumn' => new Type('string'),
                'priceConditionColumn' => new Type('string'),
                'categoriesValue' => new Type('string'),
                'familyValue' => new Type('string')
            ]
        );
        return new Collection(['fields' => $constraintFields]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}