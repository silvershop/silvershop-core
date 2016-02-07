<?php

/**
 * Base class for building steps for checkout processing
 */
class CheckoutStep extends Extension
{
    /**
     * Get the next step action
     *
     * @return string|NULL
     */
    private function nextstep()
    {
        $steps = $this->owner->getSteps();
        $found = false;
        foreach ($steps as $step => $class) {
            //determine if this is the current step
            if (method_exists($this, $step)) {
                $found = true;
            } elseif ($found) {
                return $step;
            }
        }
        return null;
    }

    public function NextStepLink($nextstep = null)
    {
        if (!$nextstep) {
            $nextstep = $this->nextstep();
        }
        $anchor = Config::inst()->get("SteppedCheckout", "continue_anchor");
        $anchor = $anchor ? "#" . $anchor : "";
        return $this->owner->Link($nextstep) . $anchor;
    }
}
