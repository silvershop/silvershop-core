<?php

namespace SilverShop\Forms;

use SilverShop\Checkout\CheckoutComponentConfig;
use SilverShop\Extension\ShopConfigExtension;
use SilverStripe\Core\Config\Config;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Security\Security;

class CheckoutForm extends Form
{
    /**
     * @var CheckoutComponentConfig
     */
    protected $config;

    protected $redirectlink;

    private static $submit_button_text;

    public function __construct(RequestHandler $controller, $name, CheckoutComponentConfig $config)
    {
        $this->config = $config;
        $fields = $config->getFormFields();

        if ($text = $this->config()->get('submit_button_text')) {
            $submitBtnText = $text;
        } else {
            $submitBtnText = _t('SilverShop\Page\CheckoutPage.ProceedToPayment', 'Proceed to payment');
        }

        $actions = FieldList::create(
            FormAction::create(
                'checkoutSubmit',
                $submitBtnText
            )->setUseButtonTag(Config::inst()->get(ShopConfigExtension::class, 'forms_use_button_tag'))
        );
        $validator = CheckoutComponentValidator::create($this->config);

        parent::__construct($controller, $name, $fields, $actions, $validator);
        //load data from various sources
        $this->loadDataFrom($this->config->getData(), Form::MERGE_IGNORE_FALSEISH);
        if ($member = Security::getCurrentUser()) {
            $this->loadDataFrom($member, Form::MERGE_IGNORE_FALSEISH);
        }
        if ($controller && ($session = $controller->getRequest()->getSession())) {
            if ($sessiondata = $session->get("FormInfo.{$this->FormName()}.data")) {
                $this->loadDataFrom($sessiondata, Form::MERGE_IGNORE_FALSEISH);
            }
        }
    }

    public function setRedirectLink($link)
    {
        $this->redirectlink = $link;
    }

    public function checkoutSubmit($data, $form)
    {
        //form validation has passed by this point, so we can save data
        $this->config->setData($form->getData());
        if ($this->redirectlink) {
            return $this->controller->redirect($this->redirectlink);
        }

        return $this->controller->redirectBack();
    }

    public function getConfig()
    {
        return $this->config;
    }
}
