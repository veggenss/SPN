<?php
namespace Spn\Service;

use Spn\Repository\UserRepository;

class UserService{
    private UserRepository $repo;
    
    public function __construct()
    {
        $this->repo = new UserRepository;
    }
    
    public function getUserFromToken(string $token)
    {
        $user = $this->repo->findByToken($token);
        if(!$user){
            throw new \Spn\Exceptions\UserException("Couldn't Find User By Token");
        }
        
        if((int)$user['expires_at'] < time()){
            $this->repo->removeExpiredToken();
            return null;
        }
        
        return (int)$user['user_id'];
    }
}