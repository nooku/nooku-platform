<?php
/**
 * Kodekit Component - http://www.timble.net/kodekit
 *
 * @copyright      Copyright (C) 2011 - 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license        MPL v2.0 <https://www.mozilla.org/en-US/MPL/2.0>
 * @link           https://github.com/timble/kodekit-languages for the canonical source repository
 */

namespace Kodekit\Component\Languages;

use Kodekit\Library;

/**
 * Translatable Database Behavior
 *
 * @author  Gergo Erdosi <http://github.com/gergoerdosi>
 * @package Kodekit\Component\Languages
 */
class DatabaseBehaviorTranslatable extends Library\DatabaseBehaviorAbstract implements Library\ObjectMultiton
{
    protected $_tables;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_tables = $this->getObject('com:languages.model.tables')
            ->enabled(true)
            ->fetch();
    }

    public function isSupported()
    {
        $table  = $this->getMixer();

        if($table instanceof Library\DatabaseTableInterface)
        {
            // If table is not enabled, return null to prevent enqueueing.
            $needle = array(
                'name'           => $table->getBase(),
                'component_name' => $table->getIdentifier()->package
            );

            return count($this->_tables->find($needle)) ? true : false;
        }

        return true;
    }

    public function getMixableMethods($exclude = array())
    {
        $methods = parent::getMixableMethods($exclude);
        $mixer   = $this->getMixer();

        if (!is_null($mixer))
        {
            // If table is not enabled, don't mix the methods.
            $table  = $mixer instanceof Library\DatabaseTableInterface ? $mixer : $mixer->getTable();
            $needle = array(
                'name'           => $table->getBase(),
                'component_name' => $table->getIdentifier()->package
            );

            if (!count($this->_tables->find($needle)))
            {
                $methods['isTranslatable'] = false;

                unset($methods['getLanguages']);
                unset($methods['getTranslations']);
            }
        }

        return $methods;
    }

    public function getLanguages()
    {
        return $this->getObject('languages');
    }

    public function getTranslations()
    {
        $translations = $this->getObject('com:languages.model.translations')
            ->table($this->getMixer()->getIdentifier()->package)
            ->row($this->id)
            ->fetch();

        return $translations;
    }

    protected function _beforeSelect(Library\DatabaseContext $context)
    {
        if ($query = $context->query)
        {
            $table     = $this->_tables->find(array('name' => $context->table));
            $languages = $this->getObject('languages');
            $active    = $languages->getActive();
            $default   = $languages->getDefault();

            // Join translation to add status to rows.
            $params = $context->query->params;
            if (!$query->isCountQuery() && $params->has('translated')) {
                $translated = $params->has('translated');

                $query->columns(array(
                    'translation_status'   => 'translations.status',
                    'translation_original' => 'translations.original',
                    'translation_deleted'  => 'translations.deleted'))
                    ->join(array('translations' => 'languages_translations'),
                        'translations.table = :translation_table' .
                        ' AND translations.row = tbl.' . $table->unique_column .
                        ' AND translations.iso_code = :translation_iso_code')
                    ->bind(array(
                        'translation_iso_code' => $active->iso_code,
                        'translation_table'    => $table->name
                    ));

                if (!is_null($translated))
                {
                    $status = $translated ? LanguagesModelEntityTranslation::STATUS_COMPLETED : array(
                        LanguagesModelEntityTranslation::STATUS_MISSING,
                        LanguagesModelEntityTranslation::STATUS_OUTDATED
                    );

                    $query->where('translations.status IN :translation_status')
                        ->bind(array('translation_status' => (array)$status));
                }
            }

            // Modify table in the query if active language is not the default
            if ($active->iso_code != $default->iso_code) {
                $context->query->table[key($query->table)] = strtolower($active->iso_code) . '_' . $table->name;
            }
        }
    }

    protected function _afterInsert(Library\DatabaseContext $context)
    {
        if ($context->affected)
        {
            $languages = $this->getObject('languages');
            $active    = $languages->getActive();
            $default   = $languages->getDefault();

            $translation = array(
                'iso_code' => $active->iso_code,
                'table'    => $context->table,
                'row'      => $context->data->id,
                'status'   => ModelEntityTranslation::STATUS_COMPLETED,
                'original' => 1
            );

            // Insert item into the translations table.
            $this->getObject('com:languages.model.entity.translation')
                ->setProperties($translation)
                ->save();

            // Insert item into language specific tables.
            $table = $this->_tables->find(array('name' => $context->table));

            foreach ($languages as $language)
            {
                if ($language->iso_code != $default->iso_code)
                {
                    $query = clone $context->query;
                    $query->table(strtolower($language->iso_code) . '_' . $query->table);

                    if (($key = array_search($table->unique_column, $query->columns)) !== false) {
                        $query->values[0][$key] = $context->data->id;
                    }

                    $this->getTable()->getDriver()->insert($query);
                }

                if ($language->iso_code != $active->iso_code)
                {
                    // Insert item into translations table.
                    $translation['iso_code'] = $language->iso_code;
                    $translation['status']   = ModelEntityTranslation::STATUS_MISSING;
                    $translation['original'] = 0;

                    $this->getObject('com:languages.model.entity.translation')
                        ->setProperties($translation)
                        ->save();
                }
            }
        }
    }

    protected function _beforeUpdate(Library\DatabaseContext $context)
    {
        $languages = $this->getObject('languages');
        $active    = $languages->getActive();
        $default   = $languages->getDefault();

        if ($active->iso_code != $default->iso_code) {
            $context->query->table(strtolower($active->iso_code) . '_' . $context->query->table);
        }
    }

    protected function _afterUpdate(Library\DatabaseContext $context)
    {
        $languages = $this->getObject('languages');
        $default   = $languages->getDefault();
        $active    = $languages->getActive();

        // Update item in the translations table.
        $table       = $this->_tables->find(array('name' => $context->table));
        $translation = $this->getObject('com:languages.database.table.translations')
            ->select(array(
                'iso_code' => $active->iso_code,
                'table'    => $context->table,
                'row'      => $context->data->id
            ), Library\Database::FETCH_ROW);

        $translation->setProperties(array(
            'status' => ModelEntityTranslation::STATUS_COMPLETED
        ))->save();

        // Set the other items to outdated if they were completed before.
        $query = $this->getObject('lib:database.query.select')
            ->where('iso_code <> :iso_code')
            ->where('table = :table')
            ->where('row = :row')
            ->where('status = :status')
            ->bind(array(
                'iso_code' => $active->iso_code,
                'table'    => $context->table,
                'row'      => $context->data->id,
                'status'   => ModelEntityTranslation::STATUS_COMPLETED
            ));

        $translations = $this->getObject('com:languages.database.table.translations')
            ->select($query);

        $translations->status = ModelEntityTranslation::STATUS_OUTDATED;
        $translations->save();

        // Copy the item's data to all missing translations.
        $database = $this->getTable()->getDriver();
        $prefix   = $active->iso_code != $default->iso_code ? strtolower($active->iso_code . '_') : '';
        $select   = $this->getObject('lib:database.query.select')
            ->table($prefix . $table->name)
            ->where($table->unique_column . ' = :unique')
            ->bind(array('unique' => $context->data->id));

        $query->bind(array('status' => ModelEntityTranslation::STATUS_MISSING));
        $translations = $this->getObject('com:languages.database.table.translations')
            ->select($query);

        foreach ($translations as $translation)
        {
            $prefix = $translation->iso_code != $default->iso_code ? strtolower($translation->iso_code . '_') : '';
            $query  = 'REPLACE INTO ' . $database->quoteIdentifier($prefix . $table->name) . ' ' . $select;
            $database->execute($query);
        }
    }

    protected function _beforeDelete(Library\DatabaseContext $context)
    {
        $languages = $this->getObject('languages');
        $active    = $languages->getActive();
        $default   = $languages->getDefault();

        if ($active->iso_code != $default->iso_code) {
            $context->query->table(strtolower($active->iso_code) . '_' . $context->table);
        }
    }

    protected function _afterDelete(Library\DatabaseContext $context)
    {
        if ($context->data->getStatus() == Library\Database::STATUS_DELETED)
        {
            $languages = $this->getObject('languages');
            $default   = $languages->getDefault();
            $active    = $languages->getActive();

            // Remove item from other tables too.
            $database = $this->getTable()->getDriver();
            $query    = clone $context->query;

            foreach ($languages as $language)
            {
                if ($language->iso_code != $active->iso_code)
                {
                    $prefix = $language->iso_code != $default->iso_code ? strtolower($language->iso_code . '_') : '';
                    $query->table($prefix . $context->table);
                    $database->delete($query);
                }
            }

            // Mark item as deleted in translations table.
            $translations = $this->getObject('com:languages.database.table.translations')
                ->select(array('table' => $context->table, 'row' => $context->data->id));

            foreach ($translations as $translation) {
                $translation->setProperties(array('deleted' => 1));
            }

            $translations->save();
        }
    }
}