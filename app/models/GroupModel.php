<?php

namespace App\Models;

use DibiConnection;

class GroupModel extends \Nette\Object {

    const TABLE = 'skupina';
    const TABLE_TO_USERS = 'user_in_group';

    /**
     * @var \DibiConnection
     */
    private $database;

    /**
     * @param \DibiConnection
     */
    public function __construct(DibiConnection $database) {
        $this->database = $database;
    }

    /**
     * @return array
     */
    public function findAll() {
        $query = $this->database->select(self::TABLE . '.id,' . self::TABLE . '.nazov,' . 'count('.self::TABLE_TO_USERS.'.group_id'.') as pocet')
                ->from(self::TABLE)
                ->leftJoin(self::TABLE_TO_USERS)
                ->on(self::TABLE.'.id = '.  self::TABLE_TO_USERS.'.group_id')
                ->groupBy(self::TABLE.'.id')
                ->orderBy(self::TABLE . '.nazov DESC');

        return $query->fetchAll();
    }

}
