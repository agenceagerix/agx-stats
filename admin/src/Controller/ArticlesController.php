<?php
/*-----------------------------------------------------------------------------------------------------/
	@version		1.1.0
	@build			22nd July, 2025
	@created		22nd July, 2025
	@package		JoomlaHits
	@subpackage		ArticlesController.php
	@author			Hugo Dantas - Agence Agerix <https://www.agerix.fr>
	@copyright		Copyright (C) 2025. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
	  __    ___  ____  __ _   ___  ____     __    ___  ____  ____  __  _  _
	 / _\  / __)(  __)(  ( \ / __)(  __)   / _\  / __)(  __)(  _ \(  )( \/ )
	/    \( (_ \ ) _) /    /( (__  ) _)   /    \( (_ \ ) _)  )   / )(  )  (
	\_/\_/ \___/(____)\_)__) \___)(____)  \_/\_/ \___/(____)(__\_)(__)(_/\_)
/------------------------------------------------------------------------------------------------------*/

namespace Piedpiper\Component\JoomlaHits\Administrator\Controller;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Articles list controller class.
 */
class ArticlesController extends AdminController
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  The model.
     */
    public function getModel($name = 'Article', $prefix = 'Administrator', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Method to publish a list of articles.
     *
     * @return  void
     */
    public function publish()
    {
        // Get the input
        $input = $this->app->getInput();
        $cid = $input->get('cid', array(), 'array');

        if (empty($cid))
        {
            $this->app->enqueueMessage('No articles selected.', 'warning');
        }
        else
        {
            // Get the model
            $model = $this->getModel('Article');

            // Publish the articles
            if ($model->publish($cid, 1))
            {
                $this->app->enqueueMessage(sprintf('%d article(s) published successfully.', count($cid)), 'message');
            }
            else
            {
                $this->app->enqueueMessage('Error publishing articles.', 'error');
            }
        }

        // Redirect back to the list view
        $this->setRedirect('index.php?option=com_joomlahits&view=cpanel');
    }

    /**
     * Method to unpublish a list of articles.
     *
     * @return  void
     */
    public function unpublish()
    {
        // Get the input
        $input = $this->app->getInput();
        $cid = $input->get('cid', array(), 'array');

        if (empty($cid))
        {
            $this->app->enqueueMessage('No articles selected.', 'warning');
        }
        else
        {
            // Get the model
            $model = $this->getModel('Article');

            // Unpublish the articles
            if ($model->publish($cid, 0))
            {
                $this->app->enqueueMessage(sprintf('%d article(s) unpublished successfully.', count($cid)), 'message');
            }
            else
            {
                $this->app->enqueueMessage('Error unpublishing articles.', 'error');
            }
        }

        // Redirect back to the list view
        $this->setRedirect('index.php?option=com_joomlahits&view=cpanel');
    }
}