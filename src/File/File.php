<?php

namespace Reactor\Transit\File;


use Illuminate\Database\Eloquent\Model as Eloquent;
use Reactor\Transit\Contract\Deletable as DeletableContract;
use Reactor\Transit\Contract\Downloadable as DownloadableContract;
use Reactor\Transit\Contract\HasMetadata as HasMetadataContract;
use Reactor\Transit\Contract\Uploadable as UploadableContract;

class File extends Eloquent implements DownloadableContract, UploadableContract, DeletableContract, HasMetadataContract {

    use Downloadable, Uploadable, Deletable, HasMetadata;

    /**
     * The fillable fields for the model.
     *
     * @var array
     */
    protected $fillable = ['extension', 'mimetype', 'size', 'name', 'path', 'metadata'];

}