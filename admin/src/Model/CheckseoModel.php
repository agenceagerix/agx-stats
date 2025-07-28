<?php
/*-----------------------------------------------------------------------------------------------------/
	@version		1.1.0
	@build			28th July, 2025
	@created		28th July, 2025
	@package		JoomlaHits
	@subpackage		CheckSeoModel.php
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

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Exception;

class CheckSeoModel extends BaseDatabaseModel
{
    /**
     * Get articles with missing meta descriptions
     *
     * @return  array  Array of article objects
     */
    public function getItems()
    {
        $app = Factory::getApplication();
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select([
            $db->quoteName('a.id'),
            $db->quoteName('a.title'),
            $db->quoteName('a.alias'),
            $db->quoteName('a.metadesc'),
            $db->quoteName('a.hits'),
            $db->quoteName('a.state'),
            $db->quoteName('a.created'),
            $db->quoteName('a.language'),
            $db->quoteName('c.title', 'category_title'),
            $db->quoteName('a.catid')
        ])
        ->from($db->quoteName('#__content', 'a'))
        ->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON ' . $db->quoteName('a.catid') . ' = ' . $db->quoteName('c.id'))
        ->where('(' . $db->quoteName('a.metadesc') . ' IS NULL OR ' . $db->quoteName('a.metadesc') . ' = \'\')')
        ->where($db->quoteName('a.state') . ' != -2'); // Exclude trashed articles

        // Apply search filter
        $search = $app->input->getString('filter_search', '');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where($db->quoteName('a.id') . ' = ' . (int) substr($search, 3));
            } else {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
                $query->where('(' . $db->quoteName('a.title') . ' LIKE ' . $search . ' OR ' . $db->quoteName('c.title') . ' LIKE ' . $search . ')');
            }
        }

        // Apply category filter
        $categoryId = $app->input->getString('filter_category_id', '');
        if (is_numeric($categoryId)) {
            $query->where($db->quoteName('a.catid') . ' = ' . (int) $categoryId);
        }

        // Apply language filter
        $language = $app->input->getString('filter_language', '');
        if (!empty($language)) {
            $query->where($db->quoteName('a.language') . ' = ' . $db->quote($language));
        }

        // Apply published filter
        $published = $app->input->getString('filter_published', '');
        if (is_numeric($published)) {
            $query->where($db->quoteName('a.state') . ' = ' . (int) $published);
        }

        // Apply ordering
        $orderCol = $app->input->getString('filter_order', 'a.title');
        $orderDirn = $app->input->getString('filter_order_Dir', 'ASC');
        
        // Validate ordering columns
        $allowedColumns = ['a.title', 'a.hits', 'a.id', 'category_title', 'a.language', 'a.state'];
        if (!in_array($orderCol, $allowedColumns)) {
            $orderCol = 'a.title';
        }
        
        // Validate direction
        if (!in_array(strtoupper($orderDirn), ['ASC', 'DESC'])) {
            $orderDirn = 'ASC';
        }
        
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        $db->setQuery($query);
        return $db->loadObjectList();
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
        ->where($db->quoteName('a.state') . ' != -2') // Exclude trashed articles
        ->where('(' . $db->quoteName('a.metadesc') . ' IS NULL OR ' . $db->quoteName('a.metadesc') . ' = \'\')') // Only articles without metadesc
        ->group($db->quoteName('c.id'))
        ->having('article_count > 0')
        ->order($db->quoteName('c.title'));

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * Get all languages used by published articles.
     * Returns languages with localized names and article count for filter dropdown.
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
        ->where($db->quoteName('a.state') . ' != -2') // Exclude trashed articles
        ->where('(' . $db->quoteName('a.metadesc') . ' IS NULL OR ' . $db->quoteName('a.metadesc') . ' = \'\')') // Only articles without metadesc
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

    /**
     * Get article content for AI meta description generation
     *
     * @param   int  $articleId  The article ID
     * @return  object|null  Object with title and introtext or null if not found
     */
    public function getArticleContent($articleId)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select([
            $db->quoteName('id'),
            $db->quoteName('title'),
            $db->quoteName('introtext'),
            $db->quoteName('fulltext'),
            $db->quoteName('metadesc'),
            $db->quoteName('language')
        ])
        ->from($db->quoteName('#__content'))
        ->where($db->quoteName('id') . ' = ' . (int) $articleId)
        ->where($db->quoteName('state') . ' != -2'); // Exclude trashed articles

        $db->setQuery($query);
        return $db->loadObject();
    }

    /**
     * Update article meta description
     *
     * @param   int     $articleId   The article ID
     * @param   string  $metadesc    The new meta description
     * @return  bool    True on success, false on failure
     */
    public function updateMetaDescription($articleId, $metadesc)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->update($db->quoteName('#__content'))
            ->set($db->quoteName('metadesc') . ' = ' . $db->quote($metadesc))
            ->where($db->quoteName('id') . ' = ' . (int) $articleId);

        $db->setQuery($query);
        
        try {
            return $db->execute();
        } catch (Exception $e) {
            Factory::getApplication()->enqueueMessage('Database error: ' . $e->getMessage(), 'error');
            return false;
        }
    }

}