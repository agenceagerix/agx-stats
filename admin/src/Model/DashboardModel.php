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
namespace Joomla\Component\JoomlaHits\Administrator\Model;

defined('_JEXEC') or die;

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
    /**
     * Get top articles by language
     * 
     * @param string $language Language code (e.g., 'fr-FR', 'en-GB')
     * @param int $limit Number of articles to return
     * @return array Array of top articles for the specified language
     */
    public function getTopArticlesByLanguage($language, $limit = 10)
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
        ->where($db->quoteName('a.language') . ' = ' . $db->quote($language))
        ->order($db->quoteName('a.hits') . ' DESC')
        ->setLimit($limit);

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * Get available languages with articles
     * 
     * @return array Array of available languages with their display names
     */
    public function getAvailableLanguages()
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select([
            $db->quoteName('a.language'),
            'CASE 
                WHEN ' . $db->quoteName('a.language') . ' = ' . $db->quote('fr-FR') . ' THEN ' . $db->quote('French') . '
                WHEN ' . $db->quoteName('a.language') . ' = ' . $db->quote('en-GB') . ' THEN ' . $db->quote('English') . '
                WHEN ' . $db->quoteName('a.language') . ' = ' . $db->quote('*') . ' THEN ' . $db->quote('All Languages') . '
                ELSE ' . $db->quoteName('a.language') . '
            END as language_name',
            'COUNT(' . $db->quoteName('a.id') . ') as article_count'
        ])
        ->from($db->quoteName('#__content', 'a'))
        ->where($db->quoteName('a.state') . ' = 1')
        ->where($db->quoteName('a.language') . ' != ' . $db->quote('*')) // Exclude "All Languages"
        ->group($db->quoteName('a.language'))
        ->having('article_count > 0')
        ->order('article_count DESC');

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * Get articles without clicks rate
     * 
     * @return \stdClass Object with articles without clicks statistics
     */
    public function getArticlesWithoutClicksRate()
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select([
            'COUNT(*) as total_articles',
            'COUNT(CASE WHEN ' . $db->quoteName('hits') . ' = 0 THEN 1 END) as articles_without_clicks',
            'ROUND((COUNT(CASE WHEN ' . $db->quoteName('hits') . ' = 0 THEN 1 END) / COUNT(*)) * 100, 2) as no_clicks_percentage'
        ])
        ->from($db->quoteName('#__content'))
        ->where($db->quoteName('state') . ' = 1');

        $db->setQuery($query);
        return $db->loadObject();
    }
    /**
     * Get enhanced category statistics with rankings
     * 
     * @return array Array of categories with enhanced statistics
     */
    public function getEnhancedCategoryStats()
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select([
            $db->quoteName('c.id', 'category_id'),
            $db->quoteName('c.title', 'category_name'),
            'COUNT(' . $db->quoteName('a.id') . ') as article_count',
            'SUM(' . $db->quoteName('a.hits') . ') as total_hits',
            'AVG(' . $db->quoteName('a.hits') . ') as average_hits',
            'MAX(' . $db->quoteName('a.hits') . ') as max_hits',
            'ROUND((SUM(' . $db->quoteName('a.hits') . ') / (SELECT SUM(hits) FROM ' . $db->quoteName('#__content') . ' WHERE state = 1)) * 100, 2) as hits_percentage'
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
     * Get top articles by category
     * 
     * @param int $categoryId Category ID
     * @param int $limit Number of articles to return
     * @return array Array of top articles in category
     */
    public function getTopArticlesByCategory($categoryId, $limit = 5)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select([
            $db->quoteName('a.id'),
            $db->quoteName('a.title'),
            $db->quoteName('a.hits'),
            $db->quoteName('a.created')
        ])
        ->from($db->quoteName('#__content', 'a'))
        ->where($db->quoteName('a.state') . ' = 1')
        ->where($db->quoteName('a.catid') . ' = ' . (int) $categoryId)
        ->order($db->quoteName('a.hits') . ' DESC')
        ->setLimit($limit);

        $db->setQuery($query);
        return $db->loadObjectList();
    }
    /**
     * Compare current period with previous period
     * 
     * @param string $period Period type: 'month', 'week', 'year'
     * @return \stdClass Object with comparison data
     */
    public function getPeriodComparison($period = 'month')
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        
        $currentDate = new \DateTime();
        $previousDate = new \DateTime();
        
        switch($period) {
            case 'week':
                $currentStart = $currentDate->format('Y-m-d', strtotime('monday this week'));
                $currentEnd = $currentDate->format('Y-m-d', strtotime('sunday this week'));
                $previousStart = $previousDate->modify('-1 week')->format('Y-m-d', strtotime('monday this week'));
                $previousEnd = $previousDate->format('Y-m-d', strtotime('sunday this week'));
                break;
            case 'year':
                $currentStart = $currentDate->format('Y-01-01');
                $currentEnd = $currentDate->format('Y-12-31');
                $previousStart = $previousDate->modify('-1 year')->format('Y-01-01');
                $previousEnd = $previousDate->format('Y-12-31');
                break;
            default: // month
                $currentStart = $currentDate->format('Y-m-01');
                $currentEnd = $currentDate->format('Y-m-t');
                $previousStart = $previousDate->modify('-1 month')->format('Y-m-01');
                $previousEnd = $previousDate->format('Y-m-t');
        }

        // Current period stats
        $query = $db->getQuery(true);
        $query->select([
            'COUNT(' . $db->quoteName('id') . ') as articles_created',
            'SUM(' . $db->quoteName('hits') . ') as total_hits'
        ])
        ->from($db->quoteName('#__content'))
        ->where($db->quoteName('state') . ' = 1')
        ->where($db->quoteName('created') . ' >= ' . $db->quote($currentStart))
        ->where($db->quoteName('created') . ' <= ' . $db->quote($currentEnd . ' 23:59:59'));

        $db->setQuery($query);
        $currentStats = $db->loadObject();

        // Previous period stats
        $query = $db->getQuery(true);
        $query->select([
            'COUNT(' . $db->quoteName('id') . ') as articles_created',
            'SUM(' . $db->quoteName('hits') . ') as total_hits'
        ])
        ->from($db->quoteName('#__content'))
        ->where($db->quoteName('state') . ' = 1')
        ->where($db->quoteName('created') . ' >= ' . $db->quote($previousStart))
        ->where($db->quoteName('created') . ' <= ' . $db->quote($previousEnd . ' 23:59:59'));

        $db->setQuery($query);
        $previousStats = $db->loadObject();

        // Calculate changes
        $result = new \stdClass();
        $result->current_period = $currentStats;
        $result->previous_period = $previousStats;
        $result->articles_change = $currentStats->articles_created - $previousStats->articles_created;
        $result->hits_change = $currentStats->total_hits - $previousStats->total_hits;
        $result->articles_change_percent = $previousStats->articles_created > 0 ? 
            round((($currentStats->articles_created - $previousStats->articles_created) / $previousStats->articles_created) * 100, 1) : 0;
        $result->hits_change_percent = $previousStats->total_hits > 0 ? 
            round((($currentStats->total_hits - $previousStats->total_hits) / $previousStats->total_hits) * 100, 1) : 0;
        $result->period_name = ucfirst($period);

        return $result;
    }

    /**
     * Get available years with articles
     * 
     * @return array Array of years with article counts
     */
    public function getAvailableYears()
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select([
            'YEAR(' . $db->quoteName('created') . ') as year',
            'COUNT(' . $db->quoteName('id') . ') as article_count'
        ])
        ->from($db->quoteName('#__content'))
        ->where($db->quoteName('state') . ' = 1')
        ->group('YEAR(' . $db->quoteName('created') . ')')
        ->order('year DESC');

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * Get monthly statistics for a specific year
     * 
     * @param int $year The year to get statistics for
     * @return array Array of monthly statistics
     */
    public function getMonthlyStats($year)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        // Create a query for each month (1-12)
        $monthlyStats = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $query->clear();
            $query->select([
                'COUNT(' . $db->quoteName('id') . ') as articles_created',
                'SUM(' . $db->quoteName('hits') . ') as total_views',
                'AVG(' . $db->quoteName('hits') . ') as average_views'
            ])
            ->from($db->quoteName('#__content'))
            ->where($db->quoteName('state') . ' = 1')
            ->where('YEAR(' . $db->quoteName('created') . ') = ' . (int) $year)
            ->where('MONTH(' . $db->quoteName('created') . ') = ' . $month);

            $db->setQuery($query);
            $result = $db->loadObject();
            
            $monthlyStats[] = (object) [
                'month' => $month,
                'month_name' => $this->getMonthName($month),
                'articles_created' => (int) $result->articles_created,
                'total_views' => (int) ($result->total_views ?: 0),
                'average_views' => round($result->average_views ?: 0, 1)
            ];
        }

        return $monthlyStats;
    }

    /**
     * Get month name in French
     * 
     * @param int $month Month number (1-12)
     * @return string Month name in French
     */
    private function getMonthName($month)
    {
        $months = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];
        
        return $months[$month] ?? '';
    }
}