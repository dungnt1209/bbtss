<?php
/**
 * @version     1.0.1
 * @package     com_bts
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Chuyen Trung Tran <chuyentt@gmail.com> - http://www.geomatics.com.vn
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Station controller class.
 */
class BtsControllerDuplicate extends JControllerForm
{

    function run() {
		
		$model = $this->getModel('duplicate');
		if ($result = $model->run())
		{
			echo json_encode(array(1));
		} else {
			echo json_encode(array(0));
		}
		
		jexit();
	}

}