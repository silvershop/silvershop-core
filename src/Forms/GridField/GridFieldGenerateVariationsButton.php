<?php

declare(strict_types=1);

namespace SilverShop\Forms\GridField;

use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\ORM\DataObject;

/**
 * A grid-field button that fills in the missing variations for a product's selected
 * attribute types — the cartesian product of their values. Idempotent and
 * non-destructive: existing variations are kept, only missing combinations are added.
 *
 * Placed on a product's Variations tab; it calls the owner record's generateVariations().
 */
class GridFieldGenerateVariationsButton implements GridField_HTMLProvider, GridField_ActionProvider
{
    private string $targetFragment;

    public function __construct(string $targetFragment = 'buttons-before-left')
    {
        $this->targetFragment = $targetFragment;
    }

    private function getProduct(GridField $gridField): ?DataObject
    {
        $form = $gridField->getForm();
        $record = $form ? $form->getRecord() : null;

        return ($record instanceof DataObject && $record->exists() && $record->hasMethod('generateVariations'))
            ? $record
            : null;
    }

    /**
     * @return array<string, string>
     */
    public function getHTMLFragments($gridField): array
    {
        if (!$this->getProduct($gridField)) {
            return [];
        }

        $button = GridField_FormAction::create(
            $gridField,
            'generatevariations',
            _t(self::class . '.GENERATE', 'Generate variations'),
            'generatevariations',
            []
        );
        $button->addExtraClass('btn btn-outline-primary font-icon-plus-circled')
            ->setForm($gridField->getForm());

        return [$this->targetFragment => $button->Field()];
    }

    /**
     * @return array<int, string>
     */
    public function getActions($gridField): array
    {
        return ['generatevariations'];
    }

    public function handleAction(GridField $gridField, $actionName, $arguments, $data)
    {
        if ($actionName !== 'generatevariations') {
            return;
        }

        $product = $this->getProduct($gridField);
        if (!$product) {
            return;
        }

        $created = (int) $product->generateVariations();

        $gridField->getForm()->sessionMessage(
            $created > 0
                ? _t(self::class . '.GENERATED', '{count} new variation(s) generated.', ['count' => $created])
                : _t(self::class . '.NONE', 'No new variations to generate.'),
            'good'
        );
    }
}
