<?php
/*-----------------------------------------------------------------------------------------------------/
	@version		1.1.0
	@build			22nd July, 2025
	@created		22nd July, 2025
	@package		JoomlaHits
	@subpackage		ArticleModel.php
	@author			Hugo Dantas - Agence Agerix <https://www.agerix.fr>
	@copyright		Copyright (C) 2025. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
	  __    ___  ____  __ _   ___  ____     __    ___  ____  ____  __  _  _
	 / _\  / __)(  __)(  ( \ / __)(  __)   / _\  / __)(  __)(  _ \(  )( \/ )
	/    \( (_ \ ) _) /    /( (__  ) _)   /    \( (_ \ ) _)  )   / )(  )  (
	\_/\_/ \___/(____)\_)__) \___)(____)  \_/\_/ \___/(____)(__\_)(__)(_/\_)
/------------------------------------------------------------------------------------------------------*/

namespace Piedpiper\Component\JoomlaHits\Administrator\Model;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * Article model for handling individual article operations.
 */
class ArticleModel extends AdminModel
{
    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  \JForm|boolean  A \JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        return false;
    }

    /**
     * Method to change the published state of one or more records.
     *
     * @param   array    $pks    A list of the primary keys to change.
     * @param   integer  $value  The value of the published state.
     *
     * @return  boolean  True on success.
     */
    public function publish(&$pks, $value = 1)
    {
        // Sanitize the ids.
        $pks = (array) $pks;
        $pks = array_unique($pks);
        \Joomla\Utilities\ArrayHelper::toInteger($pks);

        if (empty($pks))
        {
            return false;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        try
        {
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__content'))
                ->set($db->quoteName('state') . ' = ' . (int) $value)
                ->where($db->quoteName('id') . ' IN (' . implode(',', $pks) . ')');

            $db->setQuery($query);
            $db->execute();

            return true;
        }
        catch (\Exception $e)
        {
            $this->setError($e->getMessage());
            return false;
        }
    }
}