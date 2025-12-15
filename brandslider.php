<?php
/**
 * Brand Slider Module for PrestaShop
 * Displays selected categories as a responsive slider
 *
 * @author Your Name
 * @copyright Your Company
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class BrandSlider extends Module
{
    public function __construct()
    {
        $this->name = 'brandslider';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Merlin';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => _PS_VERSION_,
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Brand Slider');
        $this->description = $this->l('Display selected categories as a beautiful sliding carousel on homepage and other pages.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
    }

    /**
     * Module installation
     */
    public function install()
    {
        return parent::install()
            && $this->registerHook('displayHome')
            && $this->registerHook('displayFooterBefore')
            && $this->registerHook('displayHeader')
            && $this->registerHook('actionFrontControllerSetMedia')
            && Configuration::updateValue('BRANDSLIDER_CATEGORIES', '')
            && Configuration::updateValue('BRANDSLIDER_ITEMS_VISIBLE', 5)
            && Configuration::updateValue('BRANDSLIDER_SPEED', 500)
            && Configuration::updateValue('BRANDSLIDER_AUTOPLAY', 1)
            && Configuration::updateValue('BRANDSLIDER_AUTOPLAY_SPEED', 3000)
            && Configuration::updateValue('BRANDSLIDER_SHOW_NAV', 1)
            && Configuration::updateValue('BRANDSLIDER_SHOW_DOTS', 1)
            && Configuration::updateValue('BRANDSLIDER_SHOW_TITLE', 1)
            && Configuration::updateValue('BRANDSLIDER_TITLE', 'Our Brands')
            && Configuration::updateValue('BRANDSLIDER_HOOK_HOME', 1)
            && Configuration::updateValue('BRANDSLIDER_HOOK_FOOTER', 0);
    }

    /**
     * Module uninstallation
     */
    public function uninstall()
    {
        return parent::uninstall()
            && Configuration::deleteByName('BRANDSLIDER_CATEGORIES')
            && Configuration::deleteByName('BRANDSLIDER_ITEMS_VISIBLE')
            && Configuration::deleteByName('BRANDSLIDER_SPEED')
            && Configuration::deleteByName('BRANDSLIDER_AUTOPLAY')
            && Configuration::deleteByName('BRANDSLIDER_AUTOPLAY_SPEED')
            && Configuration::deleteByName('BRANDSLIDER_SHOW_NAV')
            && Configuration::deleteByName('BRANDSLIDER_SHOW_DOTS')
            && Configuration::deleteByName('BRANDSLIDER_SHOW_TITLE')
            && Configuration::deleteByName('BRANDSLIDER_TITLE')
            && Configuration::deleteByName('BRANDSLIDER_HOOK_HOME')
            && Configuration::deleteByName('BRANDSLIDER_HOOK_FOOTER');
    }

    /**
     * Module configuration page
     */
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitBrandSliderModule')) {
            $categories = Tools::getValue('BRANDSLIDER_CATEGORIES');
            if (is_array($categories)) {
                $categories = implode(',', $categories);
            }

            Configuration::updateValue('BRANDSLIDER_CATEGORIES', $categories);
            Configuration::updateValue('BRANDSLIDER_ITEMS_VISIBLE', (int) Tools::getValue('BRANDSLIDER_ITEMS_VISIBLE'));
            Configuration::updateValue('BRANDSLIDER_SPEED', (int) Tools::getValue('BRANDSLIDER_SPEED'));
            Configuration::updateValue('BRANDSLIDER_AUTOPLAY', (int) Tools::getValue('BRANDSLIDER_AUTOPLAY'));
            Configuration::updateValue('BRANDSLIDER_AUTOPLAY_SPEED', (int) Tools::getValue('BRANDSLIDER_AUTOPLAY_SPEED'));
            Configuration::updateValue('BRANDSLIDER_SHOW_NAV', (int) Tools::getValue('BRANDSLIDER_SHOW_NAV'));
            Configuration::updateValue('BRANDSLIDER_SHOW_DOTS', (int) Tools::getValue('BRANDSLIDER_SHOW_DOTS'));
            Configuration::updateValue('BRANDSLIDER_SHOW_TITLE', (int) Tools::getValue('BRANDSLIDER_SHOW_TITLE'));
            Configuration::updateValue('BRANDSLIDER_TITLE', Tools::getValue('BRANDSLIDER_TITLE'));
            Configuration::updateValue('BRANDSLIDER_HOOK_HOME', (int) Tools::getValue('BRANDSLIDER_HOOK_HOME'));
            Configuration::updateValue('BRANDSLIDER_HOOK_FOOTER', (int) Tools::getValue('BRANDSLIDER_HOOK_FOOTER'));

            $output .= $this->displayConfirmation($this->l('Settings updated successfully.'));
        }

        return $output . $this->renderForm();
    }

    /**
     * Render configuration form
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitBrandSliderModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    /**
     * Get configuration form structure
     */
    protected function getConfigForm()
    {
        // Get all active categories - getCategories returns nested array by parent
        $allCategories = Category::getCategories($this->context->language->id, true, false);
        $categoryOptions = [];

        // Flatten the nested category structure
        foreach ($allCategories as $parentCategories) {
            foreach ($parentCategories as $category) {
                if (isset($category['infos']) && $category['infos']['id_category'] > 2) {
                    $cat = $category['infos'];
                    $depth = isset($cat['level_depth']) ? (int) $cat['level_depth'] : 2;
                    $categoryOptions[] = [
                        'id' => $cat['id_category'],
                        'name' => str_repeat('— ', max(0, $depth - 2)) . $cat['name'],
                    ];
                }
            }
        }

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Brand Slider Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'select',
                        'label' => $this->l('Categories to Display'),
                        'name' => 'BRANDSLIDER_CATEGORIES[]',
                        'multiple' => true,
                        'options' => [
                            'query' => $categoryOptions,
                            'id' => 'id',
                            'name' => 'name',
                        ],
                        'desc' => $this->l('Select which categories to show in the slider. Hold Ctrl/Cmd to select multiple.'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Slider Title'),
                        'name' => 'BRANDSLIDER_TITLE',
                        'desc' => $this->l('Title displayed above the slider.'),
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Show Title'),
                        'name' => 'BRANDSLIDER_SHOW_TITLE',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'active_on', 'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'active_off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Items Visible at Once'),
                        'name' => 'BRANDSLIDER_ITEMS_VISIBLE',
                        'options' => [
                            'query' => [
                                ['id' => 2, 'name' => '2'],
                                ['id' => 3, 'name' => '3'],
                                ['id' => 4, 'name' => '4'],
                                ['id' => 5, 'name' => '5'],
                                ['id' => 6, 'name' => '6'],
                                ['id' => 7, 'name' => '7'],
                                ['id' => 8, 'name' => '8'],
                            ],
                            'id' => 'id',
                            'name' => 'name',
                        ],
                        'desc' => $this->l('Number of category items to show at once.'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Slide Transition Speed (ms)'),
                        'name' => 'BRANDSLIDER_SPEED',
                        'desc' => $this->l('Animation speed in milliseconds (e.g., 500).'),
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable Autoplay'),
                        'name' => 'BRANDSLIDER_AUTOPLAY',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'autoplay_on', 'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'autoplay_off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Autoplay Interval (ms)'),
                        'name' => 'BRANDSLIDER_AUTOPLAY_SPEED',
                        'desc' => $this->l('Time between auto-slides in milliseconds (e.g., 3000 = 3 seconds).'),
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Show Navigation Arrows'),
                        'name' => 'BRANDSLIDER_SHOW_NAV',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'nav_on', 'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'nav_off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Show Dots Pagination'),
                        'name' => 'BRANDSLIDER_SHOW_DOTS',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'dots_on', 'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'dots_off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Display on Homepage'),
                        'name' => 'BRANDSLIDER_HOOK_HOME',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'home_on', 'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'home_off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Display Above Footer (All Pages)'),
                        'name' => 'BRANDSLIDER_HOOK_FOOTER',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'footer_on', 'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'footer_off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * Get current configuration values
     */
    protected function getConfigFormValues()
    {
        $selectedCategories = Configuration::get('BRANDSLIDER_CATEGORIES');
        $selectedCategoriesArray = $selectedCategories ? explode(',', $selectedCategories) : [];

        return [
            'BRANDSLIDER_CATEGORIES[]' => $selectedCategoriesArray,
            'BRANDSLIDER_ITEMS_VISIBLE' => Configuration::get('BRANDSLIDER_ITEMS_VISIBLE'),
            'BRANDSLIDER_SPEED' => Configuration::get('BRANDSLIDER_SPEED'),
            'BRANDSLIDER_AUTOPLAY' => Configuration::get('BRANDSLIDER_AUTOPLAY'),
            'BRANDSLIDER_AUTOPLAY_SPEED' => Configuration::get('BRANDSLIDER_AUTOPLAY_SPEED'),
            'BRANDSLIDER_SHOW_NAV' => Configuration::get('BRANDSLIDER_SHOW_NAV'),
            'BRANDSLIDER_SHOW_DOTS' => Configuration::get('BRANDSLIDER_SHOW_DOTS'),
            'BRANDSLIDER_SHOW_TITLE' => Configuration::get('BRANDSLIDER_SHOW_TITLE'),
            'BRANDSLIDER_TITLE' => Configuration::get('BRANDSLIDER_TITLE'),
            'BRANDSLIDER_HOOK_HOME' => Configuration::get('BRANDSLIDER_HOOK_HOME'),
            'BRANDSLIDER_HOOK_FOOTER' => Configuration::get('BRANDSLIDER_HOOK_FOOTER'),
        ];
    }

    /**
     * Get selected categories with their data
     */
    protected function getSelectedCategories()
    {
        $selectedIds = Configuration::get('BRANDSLIDER_CATEGORIES');

        if (empty($selectedIds)) {
            return [];
        }

        $categoryIds = explode(',', $selectedIds);
        $categories = [];
        $langId = $this->context->language->id;

        foreach ($categoryIds as $id) {
            $id = (int) $id;
            $category = new Category($id, $langId);

            if (Validate::isLoadedObject($category) && $category->active) {
                // Get category image URL
                $imageUrl = '';
                if (file_exists(_PS_CAT_IMG_DIR_ . $id . '.jpg')) {
                    $imageUrl = _PS_BASE_URL_ . __PS_BASE_URI__ . 'img/c/' . $id . '.jpg';
                } elseif (file_exists(_PS_CAT_IMG_DIR_ . $id . '.png')) {
                    $imageUrl = _PS_BASE_URL_ . __PS_BASE_URI__ . 'img/c/' . $id . '.png';
                } elseif (file_exists(_PS_CAT_IMG_DIR_ . $id . '.webp')) {
                    $imageUrl = _PS_BASE_URL_ . __PS_BASE_URI__ . 'img/c/' . $id . '.webp';
                }

                $categories[] = [
                    'id' => $id,
                    'name' => $category->name,
                    'link' => $this->context->link->getCategoryLink($id),
                    'image' => $imageUrl,
                    'description' => $category->description,
                ];
            }
        }

        return $categories;
    }

    /**
     * Add CSS and JS to front office
     */
    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'module-brandslider-style',
            'modules/' . $this->name . '/views/css/brandslider.css',
            ['media' => 'all', 'priority' => 200]
        );

        $this->context->controller->registerJavascript(
            'module-brandslider-script',
            'modules/' . $this->name . '/views/js/brandslider.js',
            ['position' => 'bottom', 'priority' => 200]
        );
    }

    /**
     * Hook for displayHeader (legacy support)
     */
    public function hookDisplayHeader()
    {
        // Assets are now loaded via actionFrontControllerSetMedia
    }

    /**
     * Display on homepage
     */
    public function hookDisplayHome($params)
    {
        if (!Configuration::get('BRANDSLIDER_HOOK_HOME')) {
            return '';
        }

        return $this->renderSlider();
    }

    /**
     * Display above footer (all pages)
     */
    public function hookDisplayFooterBefore($params)
    {
        if (!Configuration::get('BRANDSLIDER_HOOK_FOOTER')) {
            return '';
        }

        return $this->renderSlider();
    }

    /**
     * Render the slider template
     */
    protected function renderSlider()
    {
        $categories = $this->getSelectedCategories();

        if (empty($categories)) {
            return '';
        }

        $this->context->smarty->assign([
            'brandslider_categories' => $categories,
            'brandslider_items_visible' => (int) Configuration::get('BRANDSLIDER_ITEMS_VISIBLE'),
            'brandslider_speed' => (int) Configuration::get('BRANDSLIDER_SPEED'),
            'brandslider_autoplay' => (bool) Configuration::get('BRANDSLIDER_AUTOPLAY'),
            'brandslider_autoplay_speed' => (int) Configuration::get('BRANDSLIDER_AUTOPLAY_SPEED'),
            'brandslider_show_nav' => (bool) Configuration::get('BRANDSLIDER_SHOW_NAV'),
            'brandslider_show_dots' => (bool) Configuration::get('BRANDSLIDER_SHOW_DOTS'),
            'brandslider_show_title' => (bool) Configuration::get('BRANDSLIDER_SHOW_TITLE'),
            'brandslider_title' => Configuration::get('BRANDSLIDER_TITLE'),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/displayHome.tpl');
    }
}
