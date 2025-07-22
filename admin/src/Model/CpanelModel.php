<?php

namespace Piedpiper\Component\JoomlaHits\Administrator\Model;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;

class CpanelModel extends ListModel
{
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

        parent::populateState($ordering, $direction);
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.category_id');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.language');

        return parent::getStoreId($id);
    }

    protected function getListQuery()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select([
            $db->quoteName('a.id'),
            $db->quoteName('a.title'),
            $db->quoteName('a.alias'),
            $db->quoteName('a.hits'),
            $db->quoteName('a.state'),
            $db->quoteName('a.created'),
            $db->quoteName('a.featured'),
            $db->quoteName('a.language'),
            $db->quoteName('c.title', 'category_title')
        ])
        ->from($db->quoteName('#__content', 'a'))
        ->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON ' . $db->quoteName('a.catid') . ' = ' . $db->quoteName('c.id'));

        // Filters
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

        // Tri
        $orderCol = $this->state->get('list.ordering', 'a.hits');
        $orderDirn = $this->state->get('list.direction', 'DESC');
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }

    public function getCategories()
    {
        $db = Factory::getDbo();
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

    public function getLanguages()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select([
            $db->quoteName('a.language', 'value'),
            'CASE 
                WHEN ' . $db->quoteName('a.language') . ' = ' . $db->quote('fr-FR') . ' THEN ' . $db->quote('Français') . '
                WHEN ' . $db->quoteName('a.language') . ' = ' . $db->quote('en-GB') . ' THEN ' . $db->quote('English') . '
                WHEN ' . $db->quoteName('a.language') . ' = ' . $db->quote('*') . ' THEN ' . $db->quote('Toutes les langues') . '
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

    public function getForm($data = [], $loadData = true)
    {
        return false;
    }

    public function getArticlesHits()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select([
            $db->quoteName('a.id'),
            $db->quoteName('a.title'),
            $db->quoteName('a.alias'),
            $db->quoteName('a.hits'),
            $db->quoteName('a.state'),
            $db->quoteName('a.created'),
            $db->quoteName('a.featured'),
            $db->quoteName('a.language'),
            $db->quoteName('c.title', 'category_title')
        ])
        ->from($db->quoteName('#__content', 'a'))
        ->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON ' . $db->quoteName('a.catid') . ' = ' . $db->quoteName('c.id'))
        ->where($db->quoteName('a.state') . ' = 1')
        ->order($db->quoteName('a.hits') . ' DESC');

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function getTopHitsArticles($limit = 10)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select([
            $db->quoteName('a.id'),
            $db->quoteName('a.title'),
            $db->quoteName('a.alias'),
            $db->quoteName('a.hits'),
            $db->quoteName('a.created'),
            $db->quoteName('c.title', 'category_title')
        ])
        ->from($db->quoteName('#__content', 'a'))
        ->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON ' . $db->quoteName('a.catid') . ' = ' . $db->quoteName('c.id'))
        ->where($db->quoteName('a.state') . ' = 1')
        ->where($db->quoteName('a.hits') . ' > 0')
        ->order($db->quoteName('a.hits') . ' DESC')
        ->setLimit($limit);

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function getTotalHits()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('SUM(' . $db->quoteName('hits') . ')')
        ->from($db->quoteName('#__content'))
        ->where($db->quoteName('state') . ' = 1');

        $db->setQuery($query);
        return (int) $db->loadResult();
    }

    public function getHitsStatistics()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select([
            'COUNT(*) as total_articles',
            'SUM(' . $db->quoteName('hits') . ') as total_hits',
            'AVG(' . $db->quoteName('hits') . ') as average_hits',
            'MAX(' . $db->quoteName('hits') . ') as max_hits',
            'COUNT(CASE WHEN ' . $db->quoteName('hits') . ' > 0 THEN 1 END) as articles_with_hits'
        ])
        ->from($db->quoteName('#__content'))
        ->where($db->quoteName('state') . ' = 1');

        $db->setQuery($query);
        return $db->loadObject();
    }
}