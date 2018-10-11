<?php

use Illuminate\Database\Eloquent\Model as Eloquent;
use Reactor\Transit\Contract\Uploadable as UploadableContract;
use Reactor\Transit\File\Uploadable;

class UploadableItem extends Eloquent implements UploadableContract {

    use Uploadable;

    /**
     * The fillable fields for the model.
     *
     * @var array
     */
    protected $fillable = ['extension', 'mimetype', 'size', 'name', 'path'];

    protected $table = 'files';

}