<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Registry
 */

namespace Module\User\Registry;

use Pi;
use Pi\Application\Registry\AbstractRegistry;

/**
 * Pi user profile compound field registry
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class CompoundField extends AbstractRegistry
{
    /** @var string Module name */
    protected $module = 'user';

    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = [])
    {
        $fields = [];

        $columns = ['name', 'title', 'compound', 'edit', 'filter', 'is_required'];
        $where   = ['compound' => $options['compound']];
        $model   = Pi::model('compound_field', $this->module);
        $select  = $model->select()->where($where)
            ->columns($columns)
            ->order('id');
        $rowset  = $model->selectWith($select);
        foreach ($rowset as $row) {
            $fields[$row->name] = $row->toArray();
        }

        return $fields;
    }

    /**
     * {@inheritDoc}
     * @param string $compound Compound name: tool, address, education, work
     * @param array
     */
    public function read($compound = '')
    {
        $options = ['compound' => $compound];
        $data    = $this->loadData($options);

        return $data;
    }

    /**
     * {@inheritDoc}
     * @param string $compound
     */
    public function create($compound = '')
    {
        $this->clear('');
        $this->read($compound);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function setNamespace($meta = '')
    {
        return parent::setNamespace('');
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        return $this->clear('');
    }
}
