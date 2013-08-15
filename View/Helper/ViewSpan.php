<?php

namespace Techfever\View\View\Helper;

use Techfever\View\ElementInterface;
use Techfever\View\Exception;

class ViewSpan extends AbstractHelper {
	const APPEND = 'append';
	const PREPEND = 'prepend';

	/**
	 * Attributes valid for the label tag
	 *
	 * @var array
	 */
	protected $validTagAttributes = array();

	/**
	 * Generate a view Span, optionally with content
	 *
	 * Always generates a "for" statement, as we cannot assume the view input
	 * will be provided in the $labelContent.
	 *
	 * @param  ElementInterface $element
	 * @param  boolean      $label
	 * @param  string           $position
	 * @throws Exception\DomainException
	 * @return string|ViewSpan
	 */
	public function __invoke(ElementInterface $element = null, $label = false, $position = null) {
		if (!$element) {
			return $this;
		}
		$isPassword = false;
		if ($label) {
			$element->setAttribute('class', 'value');
			$label = $element->getLabel();
			$isPassword = $element->isPassword();
		} else {
			$element->setAttribute('class', 'span');
			$label = $element->getTitle();
		}

		if (null !== ($translator = $this->getTranslator())) {
			$label = $translator->translate($label, $this->getTranslatorTextDomain());
		}
		$openTag = $this->openTag($element);
		$closeTag = $this->closeTag($element);
		return $openTag . ($isPassword ? '******' : $label) . $closeTag;
	}

	/**
	 * Generate an opening Span tag
	 *
	 * @param  null|array|ElementInterface $attributesOrElement
	 * @throws Exception\InvalidArgumentException
	 * @throws Exception\DomainException
	 * @return string
	 */
	public function openTag($attributesOrElement = null) {
		if (null === $attributesOrElement) {
			return '<span>';
		}

		if (is_array($attributesOrElement)) {
			$attributes = $this->createAttributesString($attributesOrElement);
			return sprintf('<span %s>', $attributes);
		}

		if (!$attributesOrElement instanceof ElementInterface) {
			throw new Exception\InvalidArgumentException(sprintf('%s expects an array or Techfever\View\ElementInterface instance; received "%s"', __METHOD__, (is_object($attributesOrElement) ? get_class($attributesOrElement) : gettype($attributesOrElement))));
		}

		$id = $this->getId($attributesOrElement);
		if (null === $id) {
			throw new Exception\DomainException(sprintf('%s expects the Element provided to have either a name or an id present; neither found', __METHOD__));
		}

		$labelAttributes = $attributesOrElement->getAttributes();
		$attributes = array(
				'for' => $id
		);

		if (!empty($labelAttributes)) {
			$attributes = array_merge($labelAttributes, $attributes);
		}

		$attributes = $this->createAttributesString($attributes);
		return sprintf('<span %s>', $attributes);
	}

	/**
	 * Return a closing Span tag
	 *
	 * @return string
	 */
	public function closeTag() {
		return '</span>';
	}
}
