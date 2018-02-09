<?php

namespace SilverShop\Extension;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Checkout\Step\Address;
use SilverShop\Checkout\Step\ContactDetails;
use SilverShop\Checkout\Step\Membership;
use SilverShop\Checkout\Step\PaymentMethod;
use SilverShop\Checkout\Step\Summary;
use SilverShop\Page\CheckoutPage;
use SilverShop\Page\CheckoutPageController;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Extension;

/**
 * Stepped checkout provides multiple forms and actions for placing an order
 *
 * @package    shop
 * @subpackage steppedcheckout
 */
class SteppedCheckoutExtension extends Extension
{
    /**
     * Anchor string to add to continue links
     *
     * @config
     * @var    string
     */
    private static $continue_anchor;

    /**
     * @var CheckoutPageController
     */
    protected $owner;

    /**
     * Set up CheckoutPage_Controller decorators for managing steps
     */
    public static function setupSteps($steps = null)
    {
        if (!is_array($steps)) {
            //default steps
            $steps =[
                'membership'      => Membership::class,
                'contactdetails'  => ContactDetails::class,
                'shippingaddress' => Address::class,
                'billingaddress'  => Address::class,
                'paymentmethod'   => PaymentMethod::class,
                'summary'         => Summary::class,
            ];
        }

        CheckoutPage::config()->steps = $steps;

        if (!CheckoutPage::config()->first_step) {
            reset($steps);
            CheckoutPage::config()->first_step = key($steps);
        }
        //initiate extensions
        CheckoutPageController::add_extension(SteppedCheckoutExtension::class);
        foreach ($steps as $action => $classname) {
            CheckoutPageController::add_extension($classname);
        }
    }

    public function getSteps()
    {
        return CheckoutPage::config()->steps;
    }

    /**
     * Redirect back to start of checkout if no cart started
     */
    public function onAfterInit()
    {
        $action = $this->owner->getRequest()->param('Action');
        $steps = $this->getSteps();
        if (!ShoppingCart::curr() && !empty($action) && isset($steps[$action])) {
            Controller::curr()->redirect($this->owner->Link());
            return;
        }
    }

    /**
     * Check if passed action is the same as the current step
     */
    public function IsCurrentStep($name)
    {
        if ($this->owner->getAction() === $name) {
            return true;
        } elseif (!$this->owner->getAction() || $this->owner->getAction() === 'index') {
            return $this->actionPos($name) === 0;
        }
        return false;
    }

    public function StepExists($name)
    {
        $steps = $this->getSteps();
        return isset($steps[$name]);
    }

    /**
     * Check if passed action is for a step before current
     */
    public function IsPastStep($name)
    {
        return $this->compareActions($name, $this->owner->getAction()) < 0;
    }

    /**
     * Check if passed action is for a step after current
     */
    public function IsFutureStep($name)
    {
        return $this->compareActions($name, $this->owner->getAction()) > 0;
    }

    /**
     * Get first step from stored steps
     */
    public function index()
    {
        if (CheckoutPage::config()->first_step) {
            return $this->owner->{CheckoutPage::config()->first_step}();
        }
        return array();
    }

    /**
     * Check if one step comes before or after the another
     */
    private function compareActions($action1, $action2)
    {
        return $this->actionPos($action1) - $this->actionPos($action2);
    }

    /**
     * Get the numerical position of a step
     */
    private function actionPos($incoming)
    {
        $count = 0;
        foreach ($this->getSteps() as $action => $step) {
            if ($action == $incoming) {
                return $count;
            }
            $count++;
        }
    }
}
