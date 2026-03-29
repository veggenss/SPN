<?php
namespace Spn\Service;

use Spn\Repository\UserRepository;

class AuthService{
    private UserRepository $userRepo;
    
    public function __construct(){
        $this->userRepo = new UserRepository;
    }
    
    public function login(array $data):bool|array{
        $user = $this->userRepo->findByName($data['username']);
        if(!$user || !password_verify($data['password'], $user['password'])){
            return false;
        }
        return $user;
    }
    
    public function register(array $data){
        if($this->userRepo->findByName($data['username'])){
            return false;
        }
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return $this->userRepo->save($data);
    }
    
    public function delete(array $data){
        
    }
}