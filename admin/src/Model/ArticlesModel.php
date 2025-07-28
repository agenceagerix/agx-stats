<?php
/*-----------------------------------------------------------------------------------------------------/
	@version		1.2.0
	@build			22nd July, 2025
	@created		21st July, 2025
	@package		JoomlaHits
	@subpackage		ArticlesModel.php
	@author			Hugo Dantas - Agence Agerix <https://www.agerix.fr>
	@copyright		Copyright (C) 2025. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
	  __    ___  ____  __ _   ___  ____     __    ___  ____  ____  __  _  _
	 / _\  / __)(  __)(  ( \ / __)(  __)   / _\  / __)(  __)(  _ \(  )( \/ )
	/    \( (_ \ ) _) /    /( (__  ) _)   /    \( (_ \ ) _)  )   / )(  )  (
	\_/\_/ \___/(____)\_)__) \___)(____)  \_/\_/ \___/(____)(__\_)(__)(_/\_)
/------------------------------------------------------------------------------------------------------*/
namespace Joomla\Component\JoomlaHits\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

class ArticlesModel extends ListModel
{
    /**
     * Constructor
     * Initializes the model with filter fields for sorting and filtering
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.title',
                'alias', 'a.alias',
                'hits', 'a.hits',
                'state', 'a.state',
                'created', 'a.created',
                'featured', 'a.featured',
                'language', 'a.language',
                'category_title', 'c.title',
                'catid', 'a.catid'
            );
        }

        parent::__construct($config);
    }
    /**
     * Method to auto-populate the model state.
     * Sets up filter states for search, category, publication status and language.
     * Default ordering is by hits count in descending order.
     *
     * @param   string  $ordering   The field to order on
     * @param   string  $direction  The direction to order the results
     *
     * @return  void
     */
    protected function populateState($ordering = 'a.hits', $direction = 'DESC')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
        $this->setState('filter.search', $search);

        $categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id', '', 'string');
        $this->setState('filter.category_id', $categoryId);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '', 'string');
        $this->setState('filter.published', $published);

        $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '', 'string');
        $this->setState('filter.language', $language);

        // Get ordering parameters
        $orderCol = $this->getUserStateFromRequest($this->context . '.list.ordering', 'filter_order', $ordering);
        $this->setState('list.ordering', $orderCol);

        $orderDirn = $this->getUserStateFromRequest($this->context . '.list.direction', 'filter_order_Dir', $direction);
        $this->setState('list.direction', $orderDirn);

        // Set pagination limit
        $limit = $this->getUserStateFromRequest($this->context . '.list.limit', 'limit', 20, 'uint');
        $this->setState('list.limit', $limit);

        // Set pagination start
        $start = $this->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0, 'uint');
        $this->setState('list.start', $start);

        parent::populateState($ordering, $direction);
    }

    /**
     * Method to get a store id based on the model configuration state.
     * This is necessary because the model is used by the component and different modules that might need different sets of data.
     *
     * @param   string  $id    An identifier string to generate the store id
     *
     * @return  string  A store id
     */
    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.category_id');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.language');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load articles data with hits information.
     * Includes support for search, category, publication status and language filtering.
     * Joins with categories table to get category names.
     *
     * @return  \JDatabaseQuery  A JDatabaseQuery object to retrieve the data set
     */
    protected function getListQuery()
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select([
            $db->quoteName('a.id'),
            $db->quoteName('a.title'),
            $db->quoteName('a.alias'),
            $db->quoteName('a.introtext'),
            $db->quoteName('a.hits'),
            $db->quoteName('a.state'),
            $db->quoteName('a.created'),
            $db->quoteName('a.featured'),
            $db->quoteName('a.language'),
            $db->quoteName('c.title', 'category_title'),
            $db->quoteName('a.catid')
        ])
        ->from($db->quoteName('#__content', 'a'))
        ->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON ' . $db->quoteName('a.catid') . ' = ' . $db->quoteName('c.id'));

        // Apply filters

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where($db->quoteName('a.id') . ' = ' . (int) substr($search, 3));
            } else {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
                $query->where('(' . $db->quoteName('a.title') . ' LIKE ' . $search . ' OR ' . $db->quoteName('c.title') . ' LIKE ' . $search . ')');
            }
        }

        $categoryId = $this->getState('filter.category_id');
        if (is_numeric($categoryId)) {
            $query->where($db->quoteName('a.catid') . ' = ' . (int) $categoryId);
        }

        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where($db->quoteName('a.state') . ' = ' . (int) $published);
        }

        $language = $this->getState('filter.language');
        if (!empty($language)) {
            $query->where($db->quoteName('a.language') . ' = ' . $db->quote($language));
        }

        $orderCol = $this->state->get('list.ordering', 'a.hits');
        $orderDirn = $this->state->get('list.direction', 'DESC');
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }

    /**
     * Get all content categories that contain published articles.
     * Returns categories with their article count for filter dropdown.
     *
     * @return  array  Array of category objects with id, title and article count
     */
    public function getCategories()
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select([
            $db->quoteName('c.id', 'value'),
            $db->quoteName('c.title', 'text'),
            'COUNT(' . $db->quoteName('a.id') . ') as article_count'
        ])
        ->from($db->quoteName('#__categories', 'c'))
        ->join('LEFT', $db->quoteName('#__content', 'a') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
        ->where($db->quoteName('c.extension') . ' = ' . $db->quote('com_content'))
        ->where($db->quoteName('c.published') . ' = 1')
        ->group($db->quoteName('c.id'))
        ->having('article_count > 0')
        ->order($db->quoteName('c.title'));

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * Get all languages used by published articles.
     * Returns languages with localized names and article count for filter dropdown.
     * Languages are ordered: French, English, All languages, then others.
     *
     * @return  array  Array of language objects with language code, localized name and article count
     */
    public function getLanguages()
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select([
            $db->quoteName('a.language', 'value'),
            'CASE 
                WHEN ' . $db->quoteName('a.language') . ' = ' . $db->quote('fr-FR') . ' THEN ' . $db->quote('French') . '
                WHEN ' . $db->quoteName('a.language') . ' = ' . $db->quote('en-GB') . ' THEN ' . $db->quote('English') . '
                WHEN ' . $db->quoteName('a.language') . ' = ' . $db->quote('*') . ' THEN ' . $db->quote('All Languages') . '
                ELSE ' . $db->quoteName('a.language') . '
            END as text',
            'COUNT(' . $db->quoteName('a.id') . ') as article_count'
        ])
        ->from($db->quoteName('#__content', 'a'))
        ->where($db->quoteName('a.state') . ' = 1')
        ->group($db->quoteName('a.language'))
        ->having('article_count > 0')
        ->order('CASE 
            WHEN ' . $db->quoteName('a.language') . ' = ' . $db->quote('fr-FR') . ' THEN 1
            WHEN ' . $db->quoteName('a.language') . ' = ' . $db->quote('en-GB') . ' THEN 2
            WHEN ' . $db->quoteName('a.language') . ' = ' . $db->quote('*') . ' THEN 3
            ELSE 4
        END');

        $db->setQuery($query);
        return $db->loadObjectList();
    }
}