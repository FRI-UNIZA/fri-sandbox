<?php

namespace App\Models;

use DibiConnection, Nette;

class GroupModel extends \Nette\Object
{
    const TABLE = 'group',
            TABLE_TO_USERS = 'user_in_group';

    /**
     * @var \DibiConnection
     */
    private $database;


    /**
     * @param \DibiConnection
     */
    public function __construct(DibiConnection $database)
    {
        $this->database = $database;
    }

    /**
     * @return array
     */
    public function findAll()
    {
        $query = $this->database->select(self::TABLE.'.id, '.self::TABLE.'.name, '.self::TABLE.'.desc, count('.self::TABLE_TO_USERS.'.user_id) as pocet')
            ->from(self::TABLE)
            ->leftJoin(self::TABLE_TO_USERS)
            ->on(self::TABLE.'.id = '.self::TABLE_TO_USERS.'.group_id')
            ->groupBy(self::TABLE.'.id')
            ->orderBy(self::TABLE.'.name ASC');

        return $query->fetchAll();
    }

    public function findAllByName($name) {
        $query = $this->database->select(self::TABLE.'.id, '.self::TABLE.'.name, '.self::TABLE.'.desc, count('.self::TABLE_TO_USERS.'.user_id) as pocet')
            ->from(self::TABLE)
            ->leftJoin(self::TABLE_TO_USERS)
            ->on(self::TABLE.'.id = '.self::TABLE_TO_USERS.'.group_id')
            ->where('('.self::TABLE.'.name LIKE %s)','%'.$name.'%')
            ->groupBy(self::TABLE.'.id')
            ->orderBy(self::TABLE.'.name ASC');

        return $query->fetchAll();
    }

    public function getName($id) {
        $query = $this->database->select(self::TABLE.'.name')
            ->from(self::TABLE)
            ->where(self::TABLE.'.id = %i',$id);

        return $query->fetch()->name;
    }

    public function addGroup($values) {

        $columnArray['name%s'] = $values->name;
        $columnArray['desc%s'] = $values->desc;

        $this->database->insert(self::TABLE,$columnArray)->execute();
    }
}






