<?php

/**
 * Provides a list of development tasks to perform.
 *
 * @package    shop
 * @subpackage dev
 */
class ShopDevelopmentAdmin extends Controller
{
    public static $url_handlers    = array();

    public static $allowed_actions = array(
        'index',
    );

    public function init()
    {
        parent::init();

        // We allow access to this controller regardless of live-status or ADMIN permission only
        // if on CLI or with the database not ready. The latter makes it less errorprone to do an
        // initial schema build without requiring a default-admin login.
        // Access to this controller is always allowed in "dev-mode", or of the user is ADMIN.
        $isRunningTests = (class_exists('SapphireTest', false) && SapphireTest::is_running_test());
        $canAccess = (
            Director::isDev()
            || !Security::database_is_ready()
            // We need to ensure that DevelopmentAdminTest can simulate permission failures when running
            // "dev/tests" from CLI.
            || (Director::is_cli() && !$isRunningTests)
            || Permission::check("ADMIN")
        );
        if (!$canAccess) {
            return Security::permissionFailure(
                $this,
                "This page is secured and you need administrator rights to access it. " .
                "Enter your credentials below and we will send you right along."
            );
        }

        //render the debug view
        $renderer = Object::create('DebugView');
        $renderer->writeHeader();
        $renderer->writeInfo(_t("Shop.DEVTOOLSTITLE", "Shop Development Tools"), Director::absoluteBaseURL());
    }

    public function ShopFolder()
    {
        return SHOP_DIR;
    }

    public function Link($action = null)
    {
        $action = ($action) ? $action : "";
        return Controller::join_links(Director::absoluteBaseURL(), 'dev/' . $this->ShopFolder() . '/' . $action);
    }
}
