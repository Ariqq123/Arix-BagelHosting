<?php

namespace App\Contracts;

interface PackHost
{
    /**
     * Upload a file to the pack host and return the public download URL.
     *
     * @param string $localPath Absolute path to the local file to upload.
     * @param string $filename  Filename to use for the uploaded file.
     * @return string Public URL from which the file can be downloaded.
     */
    public function upload(string $localPath, string $filename): string;
}
