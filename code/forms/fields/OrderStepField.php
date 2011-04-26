<?php
/**
 * This field shows the user where the Order is at (which orderstep)
 *
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: forms
 *
 **/

class OrderStepField extends DatalessField {

	/**
	 * @var string $content
	 */
	protected $content;

	function __construct($name, $order, $member = null) {
		$where = "\"HideStepFromCustomer\" = 0";
		$currentStep = $order->CurrentStepVisibleToCustomer();
		if(!$member) {
			$member = Member::currentMember();
		}
		if($member) {
			if($member->IsShopAdmin()) {
				$where = "";
				$currentStep = $order->MyStep();
			}
		}
		$orderSteps = DataObject::get("OrderStep", $where);
		$passedStep = false;
		$html = "<ul>";
		foreach($orderSteps as $orderStep) {
			$class = "";
			if($orderStep->ID == $currentStep->ID) {
				$passed = true;
				$class .= " current";
			}
			elseif($passed) {
				$class .= " passed";
			}
			else {
				$class .= " before";
			}
			$html .= '<li class="'.$class.'">'.$orderStep->Title.'<li>';
		}
		$html .= "</ul>";

		$this->content = $html;
		parent::__construct($name);
	}

	function FieldHolder() {
		return is_object($this->content) ? $this->content->forTemplate() : $this->content;
	}

	function Field() {
		return $this->FieldHolder();
	}

	/**
	 * Sets the content of this field to a new value
	 * @param string $content
	 */
	function setContent($content) {
		$this->content = $content;
	}

	/**
	 * @return string
	 */
	function getContent() {
		return $this->content;
	}

	/**
	 * Synonym of {@link setContent()} so that LiteralField is more compatible with other field types.
	 */
	function setValue($value) {
		return $this->setContent($value);
	}

	function performReadonlyTransformation() {
		$clone = clone $this;
		$clone->setReadonly(true);
		return $clone;
	}
}


