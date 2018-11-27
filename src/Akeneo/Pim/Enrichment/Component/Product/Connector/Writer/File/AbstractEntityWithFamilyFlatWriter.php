<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\Csv;

use Akeneo\Tool\Component\Connector\Writer\File\AbstractItemMediaWriter;

/**
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractEntityWithFamilyFlatWriter extends AbstractItemMediaWriter
{
    /** @var array */
    protected $familyCodes;

    /** @var array */
    protected $familyVariantCodes;

    /** @var FlatHeadersGenerator */
    protected $flatHeaderGenerator;

    public function __construct(
        ArrayConverterInterface $arrayConverter,
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher,
        AttributeRepositoryInterface $attributeRepository,
        FileExporterPathGeneratorInterface $fileExporterPath,
        FlatHeadersGenerator $flatHeadersGenerator,
        array $mediaAttributeTypes,
        string $jobParamFilePath = self::DEFAULT_FILE_PATH
    ) {
            parent::__construct($arrayConverter,
                $bufferFactory,
                $flusher,
                $attributeRepository,
                $fileExporterPath,
                $mediaAttributeTypes,
                $jobParamFilePath
            );

            $this->flatHeadersGenerator = $flatHeadersGenerator;
        }


    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $parameters = $this->stepExecution->getJobParameters();

        if (isset($parameters->get('filters')['structure']['attributes'])
            && !empty($parameters->get('filters')['structure']['attributes'])
            && (null !== $parameters->get('withHeader')) && $parameters->get('withHeader')) {

            if (isset($item['family'])) {
                if (!in_array($this->familyCodes, $item['family'])) {
                    $this->familyCodes[] = $item['family'];
                }
            } elseif (isset($item['family_variant'])) {
                if (!in_array($this->familyVariantCodes, $item['family_variant'])) {
                    $this->familyVariantCodes[] = $item['family_variant'];
                }
            }
        }

        parent::write($items);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $parameters = $this->stepExecution->getJobParameters();

        if (null !== $parameters->get('withHeader') && $parameters->get('withHeader')) {
            $familyCodes = $this->familyCodes;

            if (!empty($this->familyVariantCodes)) {
                $familyCodes = $familyCodes + $this->familyRepository->getCodesFromVariant($this->familyVariantCodes);

                $familyCodes = array_unique($familyCodes);
            }

            $headersFromFamilies = $this->getHeadersFromFamilyCodes($familyCodes, $parameters);
            $this->flatRowBuffer->addToHeaders($headersFromFamilies);
        }

        parent::flush();
    }

    /**
     * Return additional headers, either based on the requested attributes,
     * or from the families definition
     */
    protected function getHeadersFromFamilies(array $familyCodes, JobParameters $parameters): array
    {
        $filters = $parameters->get('filters');

        $localeCodes = $filters['structure']['locales'];

        $channelCode = $filters['structure']['scope'];

        $attributeCodesFilter = null;

        if (isset($filters['structure']['attributes'])
            && !empty($filters['structure']['attributes'])) {
            $attributeCodesFilter = $filters['structure']['attributes'];

        }

        return $this->flatHeadersGenerator->generateHeadersForFamilies(
            $familyCodes,
            $attributeCodesFilter,
            $localeCodes,
            $scopeCode
        );
    }

    /**
     * Generate all possible headers from the provided attribute codes
     *
     * TODO: to move to a specific service
     */
    protected function generateHeadersForAttributeCodes(
        array $familyCodes,
        ?array $attributeCodesFilter,
        array $localeCodes,
        string $scopeCode
    ) {
        if (null !== $attributeCodesFilter) {
            $attributesData = 
        // TODO: mysql> select a.code, a.is_localizable, a.is_scopable, group_concat(l.code) as locale_specific from pim_catalog_attribute a left join pim_catalog_attribute_locale al on al.attribute_id = a.id left join pim_catalog_locale l on l.id = al.locale_id where a.code in ($attributeCodesFilter) group by a.id;
        } else {
            $attributesData = 
        // TODO: mysql> 
        SELECT a.code,
               a.is_localizable,
               a.is_scopable,
               GROUP_CONCAT(l.code) AS locale_specific
        FROM pim_catalog_family f
          JOIN pim_catalog_family_attribute fa ON fa.family_id = f.id
          JOIN pim_catalog_attribute a ON a.id = fa.attribute_id
          LEFT JOIN pim_catalog_attribute_locale al ON al.attribute_id = a.id
          LEFT JOIN pim_catalog_locale l on l.id = al.locale_id
        WHERE f.code IN ($familyCode)
        GROUP BY a.id;
        }
        $attributesData = $this->attributeRepository->getScopableLocalizableFromCodes($attributeCodes);

        $headers = [];

        foreach($attributesData as $attributeData) {
            if (1 === $attributeData['is_localizable'] && 1 === $attributeData['is_scopable']) {
                foreach($localeCodes as $localeCode) {
                    $headers[] = sprintf('%s-%s-%s', $attributeData['code'], $localeCode, $scopeCode);
                }
            } elseif (1 === $attributeData['is_localizable']) {
                foreach($localeCodes as $localeCode) {
                    $headers[] = sprintf('%s-%s', $attributeData['code'], $localeCode);
                }
            } elseif (1 === $attributeData['is_scopable']) {
                $headers[] = sprintf('%s-%s', $attributeData['code'], $scopeCode);
            } elseif (!empty($attributeData['specific_to_locales'])) {
                    if (in_array($attributeData['specific_to_locales'], $localeCode)) {
                        $headers[] = $attributeData['code'];
                    }
            } else {
                $headers[] = $attributeData['code'];
            }
        }

        return $headers;
    }
}
