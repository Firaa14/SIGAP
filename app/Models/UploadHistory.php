<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UploadHistory extends Model
{
    protected $fillable = [
        'user_id',
        'filename',
        'total_rows',
        'valid_rows',
        'imported_rows',
        'new_rows',
        'updated_rows',
        'skipped_rows',
        'error_rows',
        'status',
        'plta_distribution',
        'uploaded_at',
    ];

    protected $casts = [
        'plta_distribution' => 'array',
        'uploaded_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
