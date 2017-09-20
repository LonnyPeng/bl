<?php

namespace App\Controller\Plugin;

class Password
{
    /**
     * This funstion validates a plain text password with an encrpyted password
     *
     * @param string $plain
     * @param string $encrypted
     * @return boolean
     */
    public function validate($plain, $encrypted)
    {
        if (empty($plain) || empty($encrypted)) {
            return false;
        }

        // split to the hash / salt
        $stack = explode(':', $encrypted);
        if (count($stack) != 2 || md5($stack[1] . $plain) != $stack[0]) {
            return false;
        }

        // correct
        return true;
    }

    /**
     * This function makes a new password from a plaintext password.
     *
     * @param string $plain
     * @return string
     */
    public function encrypt($plain)
    {
        $password = '';

        for ($i = 0; $i < 10; $i++) {
            $password .= mt_rand();
        }
        $salt = substr(md5($password), 0, 2);
        $password = md5($salt . $plain) . ':' . $salt;

        return $password;
    }

    /**
     * This function generate a plain text password and an encrpyted password
     * @return array
     */
    public function generate()
    {
        $a_z = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $plain = '';
        for ($i = 0; $i < 10; $i++) {
            $int = rand(0,51);
            $plain .= $a_z[$int];
        }
        $password = $this->encrypt($plain);
        return array('plain' => $plain, 'password' => $password);
    }
}
