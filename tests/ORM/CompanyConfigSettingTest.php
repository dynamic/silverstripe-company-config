<?php

namespace Dynamic\CompanyConfig\Test;

use Dynamic\CompanyConfig\Model\CompanyConfigSetting;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;

class CompanyConfigSettingTest extends SapphireTest
{
    /**
     * @var array
     */
    protected static $fixture_file = array(
        '../fixtures.yml',
    );

    /**
     *
     */
    public function testGetCMSFields()
    {
        $object = $this->objFromFixture(CompanyConfigSetting::class, 'default');
        $fields = $object->getCMSFields();

        $this->assertInstanceOf(FieldList::class, $fields);
        $this->assertNotNull($fields->dataFieldByName('CompanyName'));
    }
}