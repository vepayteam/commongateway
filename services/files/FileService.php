<?php


namespace app\services\files;


class FileService
{
    public function send(string $file): void
    {
        if (file_exists($file)) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            if ($fd = fopen($file, 'rb')) {
                while (!feof($fd)) {
                    echo fread($fd, 1024);
                    usleep(1000);
                }
                fclose($fd);
            }
            exit;
        }
    }
}
