<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Faqcosis
 * @author     Clayton Alves Rodrigues <clayton.rodrigues@tesouro.gov.br>
 * @copyright  © 2016 Secretaria do Tesouro Nacional. Todos os direitos reservados.
 * @license    GNU General Public License versão 2 ou posterior; consulte o arquivo License. txt
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Faqcosis records.
 *
 * @since  1.6
 */
class FaqcosisModelPerguntasfrequentes extends JModelList {

    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see        JController
     * @since      1.6
     */
    public function __construct($config = array()) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'pergunta', 'a.pergunta',
                'manual', 'a.manual',
                'categoria', 'a.categoria',
                'resposta', 'a.resposta',
                'ordering', 'a.ordering',
                'state', 'a.state',
                'created_by', 'a.created_by',
                'modified_by', 'a.modified_by',
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   Elements order
     * @param   string  $direction  Order direction
     *
     * @return void
     *
     * @throws Exception
     *
     * @since    1.6
     */
    protected function populateState($ordering = null, $direction = null) {

        $app = JFactory::getApplication();
        $list = $app->getUserState($this->context . '.list');

        $ordering = isset($list['filter_order']) ? $list['filter_order'] : null;
        $direction = isset($list['filter_order_Dir']) ? $list['filter_order_Dir'] : null;

        $list['limit'] = (int) JFactory::getConfig()->get('list_limit', 20);
        $list['start'] = $app->input->getInt('start', 0);
        $list['ordering'] = $ordering;
        $list['direction'] = $direction;

        $app->setUserState($this->context . '.list', $list);
        $app->input->set('list', null);

        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return   JDatabaseQuery
     *
     * @since    1.6
     */
    protected function getListQuery() {

        $app = JFactory::getApplication();
        $params = $app->getParams();
        $categoryRaiz = $params->get('category');

        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query
                ->select(
                        $this->getState(
                                'list.select', 'DISTINCT a.*'
                        )
        );

        $query->from('`#__faqcosis` AS a');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');
        // Join over the category 'categoria'
        $query->select('categories_2416641.id AS categoria');
        $query->join('LEFT', '#__categories AS categories_2416641 ON categories_2416641.id = a.categoria');
        //$query->join('LEFT', '#__categories AS parent_cat ON categories_2416641.parent_id = parent_cat.id');
        // Join over the created by field 'created_by'
        $query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

        // Join over the created by field 'modified_by'
        $query->join('LEFT', '#__users AS modified_by ON modified_by.id = a.modified_by');

        if (!JFactory::getUser()->authorise('core.edit', 'com_faqcosis')) {
            $query->where('a.state = 1');
        }

        // Filter by search in title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('( a.pergunta LIKE ' . $search . ' )');
            }
        }


        // Filtering categoria
        $filter_categoria = $this->state->get("filter.categoria");
        if ($filter_categoria) {
            $query->where("a.categoria = '" . $db->escape($filter_categoria) . "'");
        }

        $query->where("categories_2416641.parent_id = '" . $db->escape($categoryRaiz) . "'");

        // Add the list ordering clause.
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');

        if ($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        return $query;
    }

    /**
     * Method to get an array of data items
     *
     * @return  mixed An array of data on success, false on failure.
     */
    public function getItems() {
        $items = parent::getItems();

        foreach ($items as $item) {


            if (isset($item->manual)) {

                // Get the title of that particular user group
                $title = FaqcosisHelpersFaqcosis::getGroupNameByGroupId($item->manual);
                $item->manual = !empty($title) ? $title : $item->manual;
            }

            if (isset($item->categoria)) {

                // Get the title of that particular template
                $title = FaqcosisHelpersFaqcosis::getCategoryNameByCategoryId($item->categoria);

                // Finally replace the data object with proper information
                $item->categoria = !empty($title) ? $title : $item->categoria;
            }
        }

        return $items;
    }

    /**
     * Overrides the default function to check Date fields format, identified by
     * "_dateformat" suffix, and erases the field if it's not correct.
     *
     * @return void
     */
    protected function loadFormData() {
        $app = JFactory::getApplication();
        $filters = $app->getUserState($this->context . '.filter', array());
        $error_dateformat = false;

        foreach ($filters as $key => $value) {
            if (strpos($key, '_dateformat') && !empty($value) && $this->isValidDate($value) == null) {
                $filters[$key] = '';
                $error_dateformat = true;
            }
        }

        if ($error_dateformat) {
            $app->enqueueMessage(JText::_("COM_FAQCOSIS_SEARCH_FILTER_DATE_FORMAT"), "warning");
            $app->setUserState($this->context . '.filter', $filters);
        }

        return parent::loadFormData();
    }

    /**
     * Checks if a given date is valid and in a specified format (YYYY-MM-DD)
     *
     * @param   string  $date  Date to be checked
     *
     * @return bool
     */
    private function isValidDate($date) {
        $date = str_replace('/', '-', $date);
        return (date_create($date)) ? JFactory::getDate($date)->format("Y-m-d") : null;
    }

}
