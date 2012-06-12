<?php

namespace Gridito\Model;

/**
 * Data model
 *
 * @author Jan Marek
 * @license MIT
 */
interface IModel extends \Countable
{
    const ASC = 'asc';
    const DESC = 'desc';

    public function getUniqueId($item);

    public function getItemByUniqueId($uniqueId);

    public function getItemsByUniqueIds(array $uniqueIds);

    public function getItems();

    public function setSorting($column, $type = self::ASC);

    public function setLimit($limit);

    public function setOffset($offset);

    public function getItemValue($item, $valueName);

}