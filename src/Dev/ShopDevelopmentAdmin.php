<?php

namespace SilverShop\Dev;

use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Dev\DebugView;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;

/**
 * Provides a list of development tasks to perform.
 *
 * @package    shop
 * @subpackage dev
 */
class ShopDevelopmentAdmin extends Controller
{
    private static $url_segment = 'silvershop';

    private static $allowed_actions = [
        'index' => true
    ];

    public function init()
    {
        parent::init();

        // We allow access to this controller regardless of live-status or ADMIN permission only
        // if on CLI or with the database not ready. The latter makes it less errorprone to do an
        // initial schema build without requiring a default-admin login.
        // Access to this controller is always allowed in "dev-mode", or of the user is ADMIN.
        $canAccess = (
            Director::isDev()
            || !Security::database_is_ready()
            // We need to ensure that DevelopmentAdminTest can simulate permission failures when running
            // "dev/tests" from CLI.
            || (Director::is_cli() && Director::isTest())
            || Permission::check('ADMIN')
        );
        if (!$canAccess) {
            return Security::permissionFailure(
                $this,
                'This page is secured and you need administrator rights to access it. ' .
                'Enter your credentials below and we will send you right along.'
            );
        }

        //render the debug view
        $renderer = DebugView::create();
        $renderer->renderHeader();
        $renderer->renderInfo(_t('SilverShop\Generic.DevToolsTitle', 'Shop Development Tools'), Director::absoluteBaseURL());
    }
}
