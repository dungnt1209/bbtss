<?php

/**
 * @version     1.0.1
 * @package     com_bts
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Chuyen Trung Tran <chuyentt@gmail.com> - http://www.geomatics.com.vn
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Bts records.
 */
class BtsModelWarnings extends JModelList {

    /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     */
    public function __construct($config = array()) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                                'id', 'a.id',
                'ordering', 'a.ordering',
                'state', 'a.state',
                'created_by', 'a.created_by',
                'station_id', 'a.station_id',
                'warning_description', 'a.warning_description',
                'device', 'a.device',
                'level', 'a.level',
                'warning_time', 'a.warning_time',
                'maintenance_by', 'a.maintenance_by',
                'maintenance_time', 'a.maintenance_time',
                'approve_by', 'a.approve_by',
                'approve_time', 'a.approve_time',
                'maintenance_state', 'a.maintenance_state',
                'approve_state', 'a.approve_state',
                'network', '#__bts_station_986074.network',
                'bsc_name', '#__bts_station_986074.bsc_name'

            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     */
    protected function populateState($ordering = null, $direction = null) {
        // Initialise variables.
        $app = JFactory::getApplication('administrator');

        // Load the filter state.
        $search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
        $this->setState('filter.state', $published);

        
		//Filtering station_id
		$this->setState('filter.station_id', $app->getUserStateFromRequest($this->context.'.filter.station_id', 'filter_station_id', '', 'string'));

		//Filtering maintenance_by
		$this->setState('filter.maintenance_by', $app->getUserStateFromRequest($this->context.'.filter.maintenance_by', 'filter_maintenance_by', '', 'string'));

		//Filtering maintenance_time
		$this->setState('filter.maintenance_time', $app->getUserStateFromRequest($this->context.'.filter.maintenance_time', 'filter_maintenance_time', '', 'string'));

		//Filtering approve_by
		$this->setState('filter.approve_by', $app->getUserStateFromRequest($this->context.'.filter.approve_by', 'filter_approve_by', '', 'string'));

		//Filtering approve_time
		$this->setState('filter.approve_time', $app->getUserStateFromRequest($this->context.'.filter.approve_time', 'filter_approve_time', '', 'string'));


        // Load the parameters.
        $params = JComponentHelper::getParams('com_bts');
        $this->setState('params', $params);

        // List state information.
        parent::populateState('a.maintenance_time', 'desc');
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param	string		$id	A prefix for the store id.
     * @return	string		A store id.
     * @since	1.6
     */
    protected function getStoreId($id = '') {
        // Compile the store id.
        $id.= ':' . $this->getState('filter.search');
        $id.= ':' . $this->getState('filter.state');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return	JDatabaseQuery
     * @since	1.6
     */
    protected function getListQuery() {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
                $this->getState(
                        'list.select', 'a.*'
                )
        );
        $query->from('`#__bts_warning` AS a');

        
    // Join over the users for the checked out user.
    $query->select('uc.name AS editor');
    $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');
    
		// Join over the user field 'created_by'
		$query->select('created_by.name AS created_by');
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');
		// Join over the foreign key 'station_id'
		$query->select('#__bts_station_986074.bts_name AS bts_name, #__bts_station_986074.network AS network, #__bts_station_986074.bsc_name AS bsc_name');
		$query->join('LEFT', '#__bts_station AS #__bts_station_986074 ON #__bts_station_986074.id = a.station_id');
		// Join over the user field 'maintenance_by'
		$query->select('maintenance_by.name AS maintenance_by');
		$query->join('LEFT', '#__users AS maintenance_by ON maintenance_by.id = a.maintenance_by');
		// Join over the user field 'approve_by'
		$query->select('approve_by.name AS approve_by');
		$query->join('LEFT', '#__users AS approve_by ON approve_by.id = a.approve_by');

        
    // Filter by published state
    $published = $this->getState('filter.state');
    if (is_numeric($published)) {
        $query->where('a.state = '.(int) $published);
    } else if ($published === '') {
        $query->where('(a.state IN (0, 1))');
    }
    

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('( a.station_id LIKE '.$search.'  OR  a.warning_description LIKE '.$search.' OR #__bts_station_986074.bsc_name LIKE '.$search.' )');
            }
        }

        

		//Filtering station_id
		$filter_station_id = $this->state->get("filter.station_id");
		if ($filter_station_id) {
			$query->where("a.station_id = '".$db->escape($filter_station_id)."'");
		}

		//Filtering maintenance_by
		$filter_maintenance_by = $this->state->get("filter.maintenance_by");
		if ($filter_maintenance_by) {
			$query->where("a.maintenance_by = '".$db->escape($filter_maintenance_by)."'");
		}

		//Filtering maintenance_time

		//Filtering approve_by
		$filter_approve_by = $this->state->get("filter.approve_by");
		if ($filter_approve_by) {
			$query->where("a.approve_by = '".$db->escape($filter_approve_by)."'");
		}

		//Filtering approve_time


        // Add the list ordering clause.
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        if ($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        return $query;
    }

    public function getItems() {
        $items = parent::getItems();
        
		foreach ($items as $oneItem) {

			if (isset($oneItem->station_id)) {
				$values = explode(',', $oneItem->station_id);

				$textValue = array();
				foreach ($values as $value){
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$query
							->select('bts_name')
							->from('`#__bts_station`')
							->where('id = ' . $db->quote($db->escape($value)));
					$db->setQuery($query);
					$results = $db->loadObject();
					if ($results) {
						$textValue[] = $results->bts_name;
					}
				}

			$oneItem->station_id = !empty($textValue) ? implode(', ', $textValue) : $oneItem->station_id;

			}
		}
        return $items;
    }

}