<?php

namespace Dynamic\CompanyConfig\ORM;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;

class LocationDataExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $db = [
        'IsPrimary' => 'Boolean',
    ];

    /**
     * @config
     * @var array
     */
    private static $summary_fields = [
        'IsPrimary.Nice' => [
            'title' => 'Main'
        ],
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $primary = $fields->dataFieldByName('IsPrimary')
            ->setTitle('Main Location')
            ->setDescription("Mark this as the main location for this company");

        $fields->insertAfter('Title', $primary);
    }
}
