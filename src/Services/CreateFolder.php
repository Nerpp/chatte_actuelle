<?php

namespace App\Services;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;


class CreateFolder 
{
    public function createFolder(string $where)
    {
        
        $filesystem = new Filesystem();

        try {

            if (!$filesystem->exists($where)){
                $filesystem->mkdir($where, 0777);
            }

        }catch (IOExceptionInterface $exception) {
            echo "An error occurred while creating your directory at ".$exception->getPath();
        }

    }

}
