<?php

App::uses('AbstractPasswordHasher', 'Controller/Component/Auth');

class PlainPasswordHasher extends AbstractPasswordHasher 
{
    public function hash($password) 
    {
        return $password;
    }

	public function check($password, $hashedPassword) 
	{
		return $hashedPassword === $this->hash($password);
	}
}