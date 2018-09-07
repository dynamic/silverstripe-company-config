<?php

namespace Dynamic\CompanyConfig\Model;

use Dynamic\CompanyConfig\Admin\CompanyConfigAdmin;
use Dynamic\Locator\Location;
use SilverStripe\Core\Manifest\ModuleResourceLoader;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;
use SilverStripe\Subsites\Model\Subsite;
use SilverStripe\View\Requirements;
use SilverStripe\View\TemplateGlobalProvider;
use TractorCow\Colorpicker\Forms\ColorField;

class CompanyConfigSetting extends DataObject implements PermissionProvider, TemplateGlobalProvider
{
    /**
     * @var string
     */
    private static $singular_name = 'Company Information';

    /**
     * @var string
     */
    private static $plural_name = 'Company Information';
    /**
     *
     * @var string
     */
    private static $description = 'Settings to customize a company';

    /**
     * @var string
     */
    private static $table_name = 'CompanyConfigSetting';



    /**
     * Default permission to check for 'LoggedInUsers' to create or edit pages.
     *
     * @var array
     * @config
     */
    private static $required_permission = ['CMS_ACCESS_CMSMain', 'COMPANY_CONFIG_PERMISSION'];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->fieldByName('Root')->fieldByName('Main')
            ->setTitle('Info');

        return $fields;
    }

    /**
     * Get the actions that are sent to the CMS. In
     * your extensions: updateEditFormActions($actions).
     *
     * @return FieldList
     */
    public function getCMSActions()
    {
        if (Permission::check('ADMIN') || Permission::check('COMPANY_CONFIG_PERMISSION')) {
            $actions = new FieldList(
                FormAction::create('save_companyconfig', _t('CompanyConfig.SAVE', 'Save'))
                    ->addExtraClass('btn-primary font-icon-save')
            );
        } else {
            $actions = FieldList::create();
        }
        $this->extend('updateCMSActions', $actions);

        return $actions;
    }

    /**
     * Finds the primary {@see SubsiteDomain} object for this subsite
     *
     * @return SubsiteDomain
     */
    public function getPrimaryLocation()
    {
        return Location::get()
            ->filter('SubsiteID', Subsite::currentSubsite())
            ->sort('"IsPrimary" DESC')
            ->first();
    }

    /**
     * @throws ValidationException
     * @throws null
     */
    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        $config = self::current_company_config();
        if (!$config) {
            self::make_company_config();
            DB::alteration_message('Added default company config', 'created');
        }
    }

    /**
     * @return string
     */
    public function CMSEditLink()
    {
        return CompanyConfigAdmin::singleton()->Link();
    }

    /**
     * @param null $member
     *
     * @return bool|int|null
     */
    public function canEdit($member = null)
    {
        if (!$member) {
            $member = Security::getCurrentUser();
        }

        $extended = $this->extendedCan('canEdit', $member);
        if ($extended !== null) {
            return $extended;
        }

        return Permission::checkMember($member, 'COMPANY_CONFIG_PERMISSION');
    }

    /**
     * @return array
     */
    public function providePermissions()
    {
        return [
            'COMPANY_CONFIG_PERMISSION' => [
                'name' => _t(
                    'Dynamic\\CompanyConfig\\Model\\CompanyConfig.COMPANY_CONFIG_PERMISSION',
                    "Access to '{title}' section",
                    ['title' => CompanyConfigAdmin::menu_title()]
                ),
                'category' => _t(
                    'SilverStripe\\Security\\Permission.CMS_ACCESS_CATEGORY',
                    'CMS Access'
                ),
                'help' => _t(
                    'Dynamic\\CompanyConfig\\Model\\CompanyConfig.COMPANY_CONFIG_PERMISSION_HELP',
                    'Ability to edit company colors.'
                ),
                'sort' => 400,
            ],
        ];
    }

    /**
     * Get the current sites {@link GlobalSiteSetting}, and creates a new one
     * through {@link make_company_config()} if none is found.
     *
     * @return GlobalSiteSetting|DataObject
     * @throws ValidationException
     */
    public static function current_company_config()
    {
        if ($config = self::get()->first()) {
            return $config;
        }

        return self::make_company_config();
    }

    /**
     * Create {@link GlobalSiteSetting} with defaults from language file.
     *
     * @return static
     * @throws ValidationException
     */
    public static function make_company_config()
    {
        $config = self::create();
        $config->write();

        return $config;
    }

    /**
     * Add $CompanyConfig to all SSViewers.
     */
    public static function get_template_global_variables()
    {
        return [
            'CompanyConfig' => 'current_company_config',
        ];
    }
}
