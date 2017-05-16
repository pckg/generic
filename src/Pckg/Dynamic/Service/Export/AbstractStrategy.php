<?php namespace Pckg\Dynamic\Service\Export;

use Pckg\Database\Entity;

abstract class AbstractStrategy implements Strategy
{

    /**
     * @var Entity
     */
    protected $entity;

    protected $responseType = 'application/octet-stream';

    protected $extension = '.txt';

    protected $fileName;

    protected $fileContent;

    protected $data = [];

    protected $headers = [];

    public function getExtension()
    {
        return $this->extension;
    }

    public function input(Entity $entity)
    {
        $this->entity = $entity;
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;

        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getData()
    {
        return $this->data ?? $this->entity->all()->toArray();
    }

    public function getResponseType()
    {
        return $this->responseType;
    }

    public function outputHeaders($fileSize, $fileName)
    {
        header("Cache-Control: private");
        header("Content-Type: " . $this->responseType);
        header("Content-Length: " . $fileSize);
        header("Content-Disposition: attachment; filename=" . $fileName . '.' . $this->extension);

        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');

        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
    }

    public function setFileName($name)
    {
        $this->fileName = $name;
    }

    public function setFileContent($content)
    {
        $this->fileContent = $content;
    }

    abstract public function prepare();

    public function output()
    {
        $this->outputHeaders(strlen($this->fileContent), $this->fileName);

        echo $this->fileContent;
    }

}