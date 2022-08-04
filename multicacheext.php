<?php
/**
 * @copyright	Copyright (c) 2016 OnlineMarketingConsultants.in. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * System - MulticacheExt Plugin
 *
 * @package		Joomla.Plugin
 * @subpakage	OnlineMarketingConsultants.in.MulticacheExt
 */
class plgSystemMulticacheExt extends JPlugin {
	var $_cache = null;
	
	var $_cache_key = null;
	/**
	 * Constructor.
	 *
	 * @param 	$subject
	 * @param	array $config
	 */
public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);

		// Set the language in the class.
		$options = array(
			'defaultgroup' => 'page',
			'browsercache' => $this->params->get('browsercache', false),
			'caching'      => false,
		);

		$this->_cache     = JCache::getInstance('page', $options);
		$this->_cache_key = JUri::getInstance()->toString();
	}
	
	public function onAfterRender()
	{
		if(!defined('MULTICACHEJOOMLAVERSION') || MULTICACHEJOOMLAVERSION <3.5)
		{
			return;
		}
		$app = JFactory::getApplication();
	
		if ($app->isAdmin())
		{
			return;
		}
	
		if (count($app->getMessageQueue()))
		{
			return;
		}
	
		$user = JFactory::getUser();
		
	
		if ($user->get('guest') && !$this->isExcluded()&& $app->input->getMethod() == 'GET')
		{
			$this->_cache->setCaching(true);
			!defined('MULTICACHEEXTSTORE') && define('MULTICACHEEXTSTORE' , true);
			// We need to check again here, because auto-login plugins have not been fired before the first aid check.
			$this->_cache->store(null, $this->_cache_key);
		}
	}

	/**
	 * Check if the page is excluded from the cache or not.
	 *
	 * @return   boolean  True if the page is excluded else false
	 *
	 * @since    3.5
	 */
	protected function isExcluded()
	{
		// Check if menu items have been excluded
		if ($exclusions = $this->params->get('exclude_menu_items', array()))
		{
			// Get the current menu item
			$active = JFactory::getApplication()->getMenu()->getActive();
				
			if ($active && $active->id && in_array($active->id, (array) $exclusions))
			{
				return true;
			}
		}
	
		// Check if regular expressions are being used
		if ($exclusions = $this->params->get('exclude', ''))
		{
			// Normalize line endings
			$exclusions = str_replace(array("\r\n", "\r"), "\n", $exclusions);
	
			// Split them
			$exclusions = explode("\n", $exclusions);
	
			// Get current path to match against
			$path = JUri::getInstance()->toString(array('path', 'query', 'fragment'));
	
			// Loop through each pattern
			if ($exclusions)
			{
				foreach ($exclusions as $exclusion)
				{
					// Make sure the exclusion has some content
					if (strlen($exclusion))
					{
						if (preg_match('/' . $exclusion . '/is', $path, $match))
						{
							return true;
						}
					}
				}
			}
		}
	
		return false;
	}
	
}