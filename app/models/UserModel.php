<?php

namespace App\Models;

use DibiConnection, Nette;
use Nette\Security\AuthenticationException;
use Nette\Security\IIdentity,
    Nette\Security as NS;
use Tracy\Debugger;

 class UserModel extends \Nette\Object implements Nette\Security\IAuthenticator
{
    const TABLE = 'user';

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
     * @param array|\DibiRow $user
     * @return bool
     */
    public function save(&$user)
    {
        if (!isset($user['id']))
        {
            $temp = $user['password'];
            $tempRemember = $user['remember'];
            unset($user['remember']);
            $user['password'] = Nette\Security\Passwords::hash($user['password']);

            $this->database->insert(self::TABLE, $user)
                ->execute();

            $user['password'] = $temp;
            $user['remember'] = $tempRemember;

            $user['id'] = $this->database->getInsertId();
        }

        return $this->database->getAffectedRows() == 1;
    }

    /**
     * Performs an authentication against e.g. database.
     * and returns IIdentity on success or throws AuthenticationException
     * @return IIdentity
     * @throws AuthenticationException
     */
    function authenticate(array $credentials)
    {
        list($username, $password) = $credentials;

        $result = $this->database->select('*')
            ->from(self::TABLE)
            ->where('username = %s',$username)
            ->fetch();

        $count = $result ? $result->count() : 0;

        if ($count < 1) {
            throw new NS\AuthenticationException('User not found.');
        }

        if (!NS\Passwords::verify($password, $result->password)) {
            throw new NS\AuthenticationException('Invalid password.');
        }

        $columnArray['last_login%sql'] = 'NOW()';

        $this->database->update(self::TABLE,$columnArray)->where('id = %i',$result->id)->execute();

        return new NS\Identity($result->id, $result->role, array('username' => $result->username));
    }

    public function findAll($groupId = null) {

        $query = $this->database->select(self::TABLE.'.id, '
            .self::TABLE.'.username, '
            .self::TABLE.'.email, '
            .self::TABLE.'.last_login')
            ->from(self::TABLE);

        if($groupId) {
            $query->leftJoin(GroupModel::TABLE_TO_USERS)
                ->on(self::TABLE.'.id = '.GroupModel::TABLE_TO_USERS.'.user_id')
                ->leftJoin(GroupModel::TABLE)
                ->on(GroupModel::TABLE.'.id = '.GroupModel::TABLE_TO_USERS.'.group_id');

            $query->where('`'.GroupModel::TABLE.'`.id = %i',$groupId);
        }
        $query->orderBy(self::TABLE.'.id');

        return $query->fetchAll();
    }

    public function addUserToGroup($userId, $groupId) {
        $result = $this->database->select('*')
            ->from(GroupModel::TABLE_TO_USERS)
            ->where(GroupModel::TABLE_TO_USERS.'.user_id = %i AND '.GroupModel::TABLE_TO_USERS.'.group_id = %i',$userId,$groupId)
            ->fetch();

        $count = $result ? $result->count() : 0;

        if($count > 0) {
            return false;
        }

        $columnArray['group_id%i'] = $groupId;
        $columnArray['user_id%i'] = $userId;

        $this->database->insert(GroupModel::TABLE_TO_USERS,$columnArray)->execute();
        return true;
    }

    public function removeUserFromGroup($userId, $groupId) {
        $result = $this->database->select('*')
            ->from(GroupModel::TABLE_TO_USERS)
            ->where(GroupModel::TABLE_TO_USERS.'.user_id = %i AND '.GroupModel::TABLE_TO_USERS.'.group_id = %i',$userId,$groupId)
            ->fetch();

        $count = $result ? $result->count() : 0;

        if($count < 1) {
            return false;
        }

        $this->database->delete(GroupModel::TABLE_TO_USERS)
            ->where(GroupModel::TABLE_TO_USERS.'.group_id = %i AND '.GroupModel::TABLE_TO_USERS.'.user_id = %i',$groupId,$userId)
            ->execute();
        return true;
    }

    public function removeUser($userId) {
        $this->database->delete(GroupModel::TABLE_TO_USERS)
            ->where(GroupModel::TABLE_TO_USERS.'.user_id = %i',$userId)
            ->execute();

        $this->database->delete(self::TABLE)
            ->where(self::TABLE.'.id = %i',$userId)
            ->execute();
    }
}