<?php

namespace App\Models;

use DibiConnection, Nette\Security as NS;

class AuthenticatorModel extends \Nette\Object implements NS\IAuthenticator
{
	const TABLE = 'pouzivatel';


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

	        
        function authenticate(array $credentials)
    {
        list($meno, $heslo) = $credentials;
              
        $result = $this->database->select('*')
            ->from(self::TABLE)
            ->where('meno = %s',$meno)
            ->fetch();
        $count = $result->count();
        $pocet = $result->count();
        
        if ($count <=0) {
            throw new NS\AuthenticationException('User not found.');
        }

        if (!NS\Passwords::verify($heslo, $result->heslo)) {
            throw new NS\AuthenticationException('Invalid password.');
        }

        return new NS\Identity($result->id, $result->role, array('meno' => $result->meno));
    }
}