<?php namespace DanPowell\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model {

    protected $fillable = [
        'markup',
        'scripts',
        'styles',
        'container_classes',
        'section_classes',
        'rank'
    ];

    public function rules()
	{
	    return [
    	    'markup' => 'required',
	        'rank' => 'integer'
	    ];
	}

    protected $casts = [
        'id' => 'integer'
    ];

	public $timestamps = false;

	public function attachment()
    {
        return $this->morphTo();
    }

    protected $touches = ['attachment'];

}
