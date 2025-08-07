<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranslationValue extends Model
{
    protected $fillable = [
        'translation_id', 'locale_id', 'value'
    ];

    public function translation()
    {
        return $this->belongsTo(Translation::class);
    }

    public function locale()
    {
        return $this->belongsTo(Locale::class);
    }
}
