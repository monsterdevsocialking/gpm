<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteRequestStatusHistory extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'quote_request_status_history';

    protected $fillable = [
        'quote_request_id', 'user_id', 'old_status', 'new_status', 'note',
    ];

    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
