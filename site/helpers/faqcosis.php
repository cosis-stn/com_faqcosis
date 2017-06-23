<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Faqcosis
 * @author     Clayton Alves Rodrigues <clayton.rodrigues@tesouro.gov.br>
 * @copyright  © 2016 Secretaria do Tesouro Nacional. Todos os direitos reservados.
 * @license    GNU General Public License versão 2 ou posterior; consulte o arquivo License. txt
 */
defined('_JEXEC') or die;

/**
 * Class FaqcosisFrontendHelper
 *
 * @since  1.6
 */
class FaqcosisHelpersFaqcosis {

    /**
     * Get group name using group ID
     * @param integer $group_id Usergroup ID
     * @return mixed group name if the group was found, null otherwise
     */
    public static function getGroupNameByGroupId($group_id) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
                ->select('title')
                ->from('#__usergroups')
                ->where('id = ' . intval($group_id));

        $db->setQuery($query);
        return $db->loadResult();
    }

    /**
     * Get category name using category ID
     * @param integer $category_id Category ID
     * @return mixed category name if the category was found, null otherwise
     */
    public static function getCategoryNameByCategoryId($category_id) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
                ->select('title')
                ->from('#__categories')
                ->where('id = ' . intval($category_id));

        $db->setQuery($query);
        return $db->loadResult();
    }

    /**
     * Get an instance of the named model
     *
     * @param   string  $name  Model name
     *
     * @return null|object
     */
    public static function getModel($name) {
        $model = null;

        // If the file exists, let's
        if (file_exists(JPATH_SITE . '/components/com_faqcosis/models/' . strtolower($name) . '.php')) {
            require_once JPATH_SITE . '/components/com_faqcosis/models/' . strtolower($name) . '.php';
            $model = JModelLegacy::getInstance($name, 'FaqcosisModel');
        }

        return $model;
    }

    /**
     * Gets the files attached to an item
     *
     * @param   int     $pk     The item's id
     *
     * @param   string  $table  The table's name
     *
     * @param   string  $field  The field's name
     *
     * @return  array  The files
     */
    public static function getFiles($pk, $table, $field) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
                ->select($field)
                ->from($table)
                ->where('id = ' . (int) $pk);

        $db->setQuery($query);

        return explode(',', $db->loadResult());
    }

    /**
     * Gets the edit permission for an user
     *
     * @param   mixed  $item  The item
     *
     * @return  bool
     */
    public static function canUserEdit($item) {
        $permission = false;
        $user = JFactory::getUser();

        if ($user->authorise('core.edit', 'com_faqcosis')) {
            $permission = true;
        } else {
            if (isset($item->created_by)) {
                if ($user->authorise('core.edit.own', 'com_faqcosis') && $item->created_by == $user->id) {
                    $permission = true;
                }
            } else {
                $permission = true;
            }
        }

        return $permission;
    }

}
