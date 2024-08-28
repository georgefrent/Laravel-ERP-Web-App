<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'support_form_id',
        'user_id',
        'message',
    ];

    public function supportForm()
    {
        return $this->belongsTo(SupportForm::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
