<?php

namespace Reactor\Transit\Service;


use Illuminate\Config\Repository;
use Reactor\Transit\Contract\Uploadable;
use Reactor\Transit\Exception\InvalidExtensionException;
use Reactor\Transit\Exception\InvalidMimeTypeException;
use Reactor\Transit\Exception\InvalidUploadException;
use Reactor\Transit\Exception\MaxFileSizeExceededException;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadService {

    /**
     * @var Repository
     */
    protected $config;

    /**
     * @var bool
     */
    protected $validatesUploadedFile;

    /**
     * @var int
     */
    protected $maxUploadSize;

    /**
     * @var array
     */
    protected $allowedExtensions;
    protected $allowedMimeTypes;

    /**
     * @var string
     */
    protected $modelName;

    /**
     * Constructor
     *
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;

        $this->validatesUploadedFile(
            $this->config->get('transit.validates', true)
        );

        $this->allowedExtensions($this->config->get('transit.extensions', [
            'jpg', 'jpeg', 'png', 'gif', 'bmp',
            'txt', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'
        ]));

        $this->allowedMimeTypes($this->config->get('transit.mimetypes', [
            'image/jpeg', 'image/gif', 'image/png', 'image/bmp',
            'text/plain', 'application/pdf', 'application/msword', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint'
        ]));

        $this->modelName(
            $this->config->get('transit.model',
                'Reactor\Transit\File\File'));

        $size = $this->config->get('transit.max_size', UploadedFile::getMaxFilesize());

        $this->maxUploadSize($size);
    }

    /**
     * Sets and gets validation mode
     *
     * @param bool $validate
     * @return bool
     */
    public function validatesUploadedFile($validate = null)
    {
        if (func_num_args() === 0)
        {
            return $this->validatesUploadedFile;
        }

        return $this->validatesUploadedFile = (bool)$validate;
    }

    /**
     * @param int $size
     * @return int
     */
    public function maxUploadSize($size = null)
    {
        if (func_num_args() === 0)
        {
            return $this->maxUploadSize;
        }

        return $this->maxUploadSize = min(
            (int)$size,
            UploadedFile::getMaxFilesize()
        );
    }

    /**
     * @param array $extensions
     * @return array
     */
    public function allowedExtensions($extensions = null)
    {
        if (func_num_args() === 0)
        {
            return $this->allowedExtensions;
        }

        return $this->allowedExtensions = (array)$extensions;
    }

    /**
     * @param array $mimetypes
     * @return array
     */
    public function allowedMimeTypes($mimetypes = null)
    {
        if (func_num_args() === 0)
        {
            return $this->allowedMimeTypes;
        }

        return $this->allowedMimeTypes = (array)$mimetypes;
    }

    /**
     * @param string
     * @return string
     */
    public function modelName($name = null)
    {
        if (func_num_args() === 0)
        {
            return $this->modelName;
        }

        return $this->modelName = (string)$name;
    }

    /**
     * Uploads a file
     *
     * @param UploadedFile $uploadedFile
     * @param int|null $id if the model should have specific id
     * @return Upload
     */
    public function upload(UploadedFile $uploadedFile, $id = null)
    {
        if ($this->validatesUploadedFile())
        {
            $this->validateUploadedFile($uploadedFile);
        }

        return $this->processUpload($uploadedFile, $id);
    }

    /**
     * @param UploadedFile $uploadedFile
     * @throws InvalidUploadException
     * @throws MaxFileSizeExceededException
     * @throws InvalidExtensionException
     * @throws InvalidMimeTypeException
     */
    protected function validateUploadedFile(UploadedFile $uploadedFile)
    {
        if ( ! $uploadedFile->isValid())
        {
            throw new InvalidUploadException($uploadedFile->getErrorMessage());
        }

        if ($this->maxUploadSize() < $uploadedFile->getSize())
        {
            throw new MaxFileSizeExceededException('Uploaded file exceeded maximum allowed upload size.');
        }

        if ( ! in_array(strtolower($uploadedFile->getClientOriginalExtension()), $this->allowedExtensions()))
        {
            throw new InvalidExtensionException('Files with extension (' . $uploadedFile->getClientOriginalExtension() . ') are not allowed');
        }

        if ( ! in_array($uploadedFile->getMimeType(), $this->allowedMimeTypes()))
        {
            throw new InvalidMimeTypeException('Files with mime type (' . $uploadedFile->getMimeType() . ') are not allowed');
        }
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param int|null $id
     * @return Upload
     */
    protected function processUpload(UploadedFile $uploadedFile, $id = null)
    {
        $uploadData = $this->prepareUploadData($uploadedFile);

        $uploadData['path'] = $this->moveUploadedFile($uploadedFile);

        $upload = $this->saveUpload($uploadData, $id);

        return $upload;
    }

    /**
     * Returns an array of data for upload model
     *
     * @param UploadedFile $uploadedFile
     * @return array
     */
    protected function prepareUploadData(UploadedFile $uploadedFile)
    {
        return [
            'extension' => $uploadedFile->getClientOriginalExtension(),
            'mimetype'  => $uploadedFile->getMimeType(),
            'size'      => $uploadedFile->getSize(),
            'name'      => $uploadedFile->getClientOriginalName(),
        ];
    }

    /**
     * @param UploadedFile $uploadedFile
     * @return array
     */
    protected function moveUploadedFile(UploadedFile $uploadedFile)
    {
        list($fullPath, $relativePath) = $this->getUploadPath();

        $filename = $this->getNewFileName(
            $uploadedFile->getClientOriginalExtension()
        );

        $uploadedFile->move($fullPath, $filename);

        return $relativePath . '/' . $filename;
    }

    /**
     * Creates a new random file name
     *
     * @param string $extension
     * @return string
     */
    public function getNewFileName($extension)
    {
        return md5(uniqid(mt_rand(), true)) . '.' . $extension;
    }

    /**
     * Returns the current upload directory
     *
     * @return string
     */
    public function getUploadPath()
    {
        $relativePath = date('Y/m');
        $fullPath = upload_path($relativePath);

        $this->makeUploadPath($fullPath);

        return [$fullPath, $relativePath];
    }

    /**
     * Creates the current upload directory
     *
     * @param $uploadPath
     * @throws RuntimeException
     */
    protected function makeUploadPath($uploadPath)
    {
        if ( ! file_exists($uploadPath))
        {
            if ( ! mkdir($uploadPath, 0777, true))
            {
                throw new RuntimeException('Directory (' . $uploadPath . ') could not be created.');
            }
        }
    }

    /**
     * @param array $uploadData
     * @param int|null $id
     * @return mixed
     */
    protected function saveUpload(array $uploadData, $id = null)
    {
        $upload = $this->getNewUploadModel($id);

        $upload->saveUploadData($uploadData);

        return $upload;
    }

    /**
     * Creates a new upload model
     *
     * @param int|null $id
     * @return mixed
     */
    protected function getNewUploadModel($id = null)
    {
        $uploadModel = $this->modelName();

        $upload = new $uploadModel;

        if ( ! $upload instanceof Uploadable)
        {
            throw new RuntimeException('The upload model must implement the "Reactor\Transit\Contract\Uploadable" interface.');
        }

        if ($id)
        {
            $upload->setKey($id);
        }

        return $upload;
    }

}
