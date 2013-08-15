<?php
namespace Techfever\View\View\Helper;

use Techfever\View\ElementInterface;
use Techfever\View\Exception;

class ViewSeperator extends AbstractHelper {
	/**
	 * Attributes valid for the input tag
	 *
	 * @var array
	 */
	protected $validTagAttributes = array(
			'form' => true,
			'name' => true,
			'id' => true,
			'class' => true
	);

	/**
	 * Render a form <textarea> element from the provided $element
	 *
	 * @param ElementInterface $element        	
	 * @throws Exception\DomainException
	 * @return string
	 */
	public function render(ElementInterface $element) {
		$name = $element->getName();
		if (empty($name) && $name !== 0) {
			throw new Exception\DomainException(sprintf('%s requires that the element has an assigned name; none discovered', __METHOD__));
		}
		$attributes = $element->getAttributes();
		$attributes['name'] = $name;
		if (!isset($attributes['class'])) {
			$attributes['class'] = 'seperator';
		}
		$escapeHtml = $this->getEscapeHtmlHelper();

		return sprintf('<div %s></div>', $this->createAttributesString($attributes));
	}

	/**
	 * Invoke helper as functor
	 *
	 * Proxies to {@link render()}.
	 *
	 * @param ElementInterface|null $element        	
	 * @return string FormTextarea
	 */
	public function __invoke(ElementInterface $element = null) {
		if (!$element) {
			return $this;
		}

		return $this->render($element);
	}
}
