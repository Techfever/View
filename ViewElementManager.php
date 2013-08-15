<?php

namespace Techfever\View;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ConfigInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\InitializableInterface;

/**
 * Plugin manager implementation for View elements.
 *
 * Enforces that elements retrieved are instances of ElementInterface.
 */
class ViewElementManager extends AbstractPluginManager {
	/**
	 * Default set of helpers
	 *
	 * @var array
	 */
	protected $invokableClasses = array(
			'div' => 'Techfever\View\Element\Div',
			'fieldset' => 'Techfever\View\Element\Fieldset',
			'label' => 'Techfever\View\Element\Label',
			'paragraph' => 'Techfever\View\Element\Paragraph',
			'span' => 'Techfever\View\Element\Span',
			'button' => 'Techfever\View\Element\Button',
			'seperator' => 'Techfever\View\Element\Seperator',
	);

	/**
	 * Don't share View elements by default
	 *
	 * @var bool
	 */
	protected $shareByDefault = false;

	/**
	 * @param ConfigInterface $configuration
	 */
	public function __construct(ConfigInterface $configuration = null) {
		parent::__construct($configuration);

		$this->addInitializer(array(
						$this,
						'injectFactory'
				));
	}

	/**
	 * Inject the factory to any element that implements ViewFactoryAwareInterface
	 *
	 * @param $element
	 */
	public function injectFactory($element) {
		if ($element instanceof ViewFactoryAwareInterface) {
			$factory = $element->getViewFactory();
			$factory->setViewElementManager($this);
		}
	}

	/**
	 * Validate the plugin
	 *
	 * Checks that the element is an instance of ElementInterface
	 *
	 * @param  mixed $plugin
	 * @throws Exception\InvalidElementException
	 * @return void
	 */
	public function validatePlugin($plugin) {
		// Hook to perform various initialization, when the element is not created through the factory
		if ($plugin instanceof InitializableInterface) {
			$plugin->init();
		}

		if ($plugin instanceof ElementInterface) {
			return; // we're okay
		}

		throw new Exception\InvalidElementException(sprintf('Plugin of type %s is invalid; must implement Techfever\View\ElementInterface', (is_object($plugin) ? get_class($plugin) : gettype($plugin))));
	}
}
