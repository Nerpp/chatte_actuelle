<?php
namespace App\Services;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;


class FileSysteme
{
    public function __construct()
    {
        $this->filesystem =  new Filesystem() ;
    }

    public function createFolder($folder)
    {
        try {
            $this->filesystem->mkdir($folder, 0700);
        } catch (IOExceptionInterface $exception) {
            echo "An error occurred while creating your directory at " . $exception->getPath();
        }
    }

    public function renameFolder($oldFolder,$newFolder)
    {
        try {
            $this->filesystem->rename($oldFolder, $newFolder, false);
        } catch (IOExceptionInterface $exception) {
            echo "An error occurred while creating your directory at " . $exception->getPath();
        }
    }

    public function remove($folder)
    {
        try {
            $this->filesystem->remove([$folder]);
        } catch (IOExceptionInterface $exception) {
            echo "An error occurred while creating your directory at " . $exception->getPath();
        }
    }

}