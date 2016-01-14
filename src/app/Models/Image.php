<?php namespace DanPowell\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model {

    protected $fillable = [
        'title',
        'slug',
        'seo_title',
        'seo_description',
        'markup',
        'styles',
        'scripts',
        'template'
    ];

	public function rules($id = null)
	{
	    return [
	        'title' => 'required',
	        'slug' => 'required|unique:pages,slug,' . $id
	    ];
	}

    protected $casts = [
        'id' => 'integer',
    ];

    protected $appends = ['created_at_human', 'updated_at_human'];

    public function getUpdatedAtHumanAttribute()
    {
        return $this->updated_at->toFormattedDateString();
    }

    public function getCreatedAtHumanAttribute()
    {
        return $this->created_at->toFormattedDateString();
    }


	public function attachment()
    {
        return $this->morphTo();
    }

    public function sections()
    {
        return $this->morphMany('DanPowell\Shop\Models\Section', 'attachment')->orderBy('rank', 'ASC');
    }

    protected $touches = ['attachment'];



    protected static function boot() {
        parent::boot();

        // When deleting we should also clean up any relationships
        static::deleting(function($model) {
             $model->sections()->delete();
        });
    }

}
