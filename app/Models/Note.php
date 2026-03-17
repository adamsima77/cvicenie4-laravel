<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Note extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'notes';

    protected $primaryKey = 'id';

    //public $timestamps = false;

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'status',
        'is_pinned',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    public function pin(){
         $this->is_pinned = true;
         $this->save();
    }

    public function unpin(){
        $this->is_pinned = false;
        $this->save();
    }

    public function archive(){
         $this->status = 'archived';
         $this->save();
    }

    public function publish(){
        $this->status = 'published';
        $this->save();
    }
}
