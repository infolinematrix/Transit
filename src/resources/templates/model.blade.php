<?php echo '<?php'; ?>

namespace {{ $namespace }};


use Reactor\Transit\File\File as TransitFile;

class {{ $name }} extends TransitFile {

    /**
     * The fillable fields for the model.
     *
     * @var array
     */
    protected $fillable = ['extension', 'mimetype', 'size', 'name', 'path', 'metadata'];

}