<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FirebaseAuthService
{
    protected $auth;
    protected $database;

    public function __construct()
    {
        $this->auth = Firebase::auth();
        $this->database = Firebase::database();
    }

    public function verifyIdToken(string $idToken)
    {
        return $this->auth->verifyIdToken($idToken);
    }

    public function createUserWithEmailAndPassword(string $email, string $password)
    {
        return $this->auth->createUserWithEmailAndPassword($email, $password);
    }

    public function signInWithEmailAndPassword(string $email, string $password)
    {
        return $this->auth->signInWithEmailAndPassword($email, $password);
    }
    
    public function saveUserData(string $uid, array $data)
    {
        return $this->database->getReference("users/{$uid}")->set($data);
    }

    public function getSpecialitiesData(string $uid) {
        return $this->database->getReference("specialities/{$uid}")->getValue();
    }

    public function getUserData(string $uid)
    {
        return $this->database->getReference("users/{$uid}")->getValue();
    }
}
