<?php

use Illuminate\Database\Eloquent\Model as Eloquent;
use Reactor\Transit\Contract\Downloadable as DownloadableContract;
use Reactor\Transit\File\Downloadable;

class DownloadableItem extends Eloquent implements DownloadableContract {

    use Downloadable;

    /**
     * The fillable fields for the model.
     *
     * @var array
     */
    protected $fillable = ['extension', 'mimetype', 'size', 'name', 'path'];

    protected $table = 'files';

}