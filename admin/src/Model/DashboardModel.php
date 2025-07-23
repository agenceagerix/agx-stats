<?php
/*-----------------------------------------------------------------------------------------------------/
	@version		1.1.0
	@build			22nd July, 2025
	@created		21st July, 2025
	@package		JoomlaHits
	@subpackage		DashboardModel.php
	@author			Hugo Dantas - Agence Agerix <https://www.agerix.fr>
	@copyright		Copyright (C) 2025. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
	  __    ___  ____  __ _   ___  ____     __    ___  ____  ____  __  _  _
	 / _\  / __)(  __)(  ( \ / __)(  __)   / _\  / __)(  __)(  _ \(  )( \/ )
	/    \( (_ \ ) _) /    /( (__  ) _)   /    \( (_ \ ) _)  )   / )(  )  (
	\_/\_/ \___/(____)\_)__) \___)(____)  \_/\_/ \___/(____)(__\_)(__)(_/\_)
/------------------------------------------------------------------------------------------------------*/
namespace Piedpiper\Component\JoomlaHits\Administrator\Model;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

class DashboardModel extends BaseDatabaseModel
{
    /**
     * Get comprehensive dashboard statistics
     * 
     * @return \stdClass Object containing all dashboard statistics
     */
    public function getDashboardStats()
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select([
            'COUNT(*) as total_articles',
            'SUM(' . $db->quoteName('hits') . ') as total_hits',
            'AVG(' . $db->quoteName('hits') . ') as average_hits',
            'MAX(' . $db->quoteName('hits') . ') as max_hits',
            'COUNT(CASE WHEN ' . $db->quoteName('hits') . ' > 0 THEN 1 END) as articles_with_hits',
            'COUNT(CASE WHEN ' . $db->quoteName('state') . ' = 1 THEN 1 END) as published_articles',
            'COUNT(CASE WHEN ' . $db->quoteName('state') . ' = 0 THEN 1 END) as unpublished_articles'
        ])
        ->from($db->quoteName('#__content'));

        $db->setQuery($query);
        return $db->loadObject();
    }

    /**
     * Get top performing articles
     * 
     * @param int $limit Number of articles to return
     * @return array Array of top articles with their hit counts
     */
    public function getTopArticles($limit = 10)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select([
            $db->quoteName('a.id'),
            $db->quoteName('a.title'),
            $db->quoteName('a.hits'),
            $db->quoteName('a.created'),
            $db->quoteName('c.title', 'category_title')
        ])
        ->from($db->quoteName('#__content', 'a'))
        ->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON ' . $db->quoteName('a.catid') . ' = ' . $db->quoteName('c.id'))
        ->where($db->quoteName('a.state') . ' = 1')
        ->order($db->quoteName('a.hits') . ' DESC')
        ->setLimit($limit);

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * Get statistics by category
     * 
     * @return array Array of categories with their statistics
     */
    public function getCategoryStats()
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select([
            $db->quoteName('c.title', 'category_name'),
            'COUNT(' . $db->quoteName('a.id') . ') as article_count',
            'SUM(' . $db->quoteName('a.hits') . ') as total_hits',
            'AVG(' . $db->quoteName('a.hits') . ') as average_hits'
        ])
        ->from($db->quoteName('#__categories', 'c'))
        ->join('LEFT', $db->quoteName('#__content', 'a') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
        ->where($db->quoteName('c.extension') . ' = ' . $db->quote('com_content'))
        ->where($db->quoteName('c.published') . ' = 1')
        ->where($db->quoteName('a.state') . ' = 1')
        ->group($db->quoteName('c.id'))
        ->having('article_count > 0')
        ->order('total_hits DESC');

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * Get statistics by language
     * 
     * @return array Array of languages with their statistics
     */
    public function getLanguageStats()
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select([
            'CASE 
                WHEN ' . $db->quoteName('a.language') . ' = ' . $db->quote('fr-FR') . ' THEN ' . $db->quote('French') . '
                WHEN ' . $db->quoteName('a.language') . ' = ' . $db->quote('en-GB') . ' THEN ' . $db->quote('English') . '
                WHEN ' . $db->quoteName('a.language') . ' = ' . $db->quote('*') . ' THEN ' . $db->quote('All Languages') . '
                ELSE ' . $db->quoteName('a.language') . '
            END as language_name',
            'COUNT(' . $db->quoteName('a.id') . ') as article_count',
            'SUM(' . $db->quoteName('a.hits') . ') as total_hits',
            'AVG(' . $db->quoteName('a.hits') . ') as average_hits'
        ])
        ->from($db->quoteName('#__content', 'a'))
        ->where($db->quoteName('a.state') . ' = 1')
        ->group($db->quoteName('a.language'))
        ->having('article_count > 0')
        ->order('total_hits DESC');

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * Get recent articles activity (created in last 30 days)
     * 
     * @return array Array of recent articles
     */
    public function getRecentActivity($days = 30)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $date = new \DateTime();
        $date->modify("-{$days} days");
        $pastDate = $date->format('Y-m-d H:i:s');

        $query->select([
            $db->quoteName('a.id'),
            $db->quoteName('a.title'),
            $db->quoteName('a.hits'),
            $db->quoteName('a.created'),
            $db->quoteName('c.title', 'category_title')
        ])
        ->from($db->quoteName('#__content', 'a'))
        ->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON ' . $db->quoteName('a.catid') . ' = ' . $db->quoteName('c.id'))
        ->where($db->quoteName('a.state') . ' = 1')
        ->where($db->quoteName('a.created') . ' >= ' . $db->quote($pastDate))
        ->order($db->quoteName('a.created') . ' DESC')
        ->setLimit(10);

        $db->setQuery($query);
        return $db->loadObjectList();
    }
}