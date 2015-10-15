<?php

namespace App\Models;

use DibiConnection,
    Nette;

class UserModel extends \Nette\Object {

    const TABLE = 'pouzivatel';

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
     * @param array|\DibiRow $user
     * @return bool
     */
    public function save($user) {
        if (!isset($user['id'])) {
            $temp = $user['heslo'];
            $tempRemember = $user['remember'];
            unset($user['remember']);
            $user['heslo'] = Nette\Security\Passwords::hash($user['heslo']);

            $this->database->insert(self::TABLE, $user)
                    ->execute();

            $user['heslo'] = $temp;
            $user['remember'] = $tempRemember;

            $user['id'] = $this->database->getInsertId();
        }

        return $this->database->getAffectedRows() == 1;
    }

}
