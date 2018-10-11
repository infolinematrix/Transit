<?php

use Illuminate\Database\Eloquent\Model as Eloquent;
use Reactor\Transit\Contract\HasMetadata as HasMetadataContract;
use Reactor\Transit\File\HasMetadata;

class ItemHasMetadata extends Eloquent implements HasMetadataContract {

    use HasMetadata;

}