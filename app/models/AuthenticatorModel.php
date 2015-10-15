<?
namespace App\Models;

use DibiConnection,
    Nette\Security as NS;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\IIdentity;

class AuthenticatorModel extends \Nette\Object implements IAuthenticator
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
        $count = $result->count();

        if ($count < 1) {
            throw new NS\AuthenticationException('User not found.');
        }

        if (!NS\Passwords::verify($password, $result->password)) {
            throw new NS\AuthenticationException('Invalid password.');
        }

        return new NS\Identity($result->id, $result->role, array('username' => $result->username));
    }
}