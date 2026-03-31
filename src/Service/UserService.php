<?php
namespace Spn\Service;

use Spn\Repository\UserRepository;

class UserService{
    private UserRepository $repo;
    
    public function __construct(){
        $this->repo = new UserRepository;
    }
    
    
}