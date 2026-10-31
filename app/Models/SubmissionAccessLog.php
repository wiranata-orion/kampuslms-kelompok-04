<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Log audit: siapa (selain pemilik) mengunduh atau mempratinjau berkas submission.
 */
class SubmissionAccessLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'submission_id',
        'user_id',
        'action',
        'original_name',
        'ip_address',
        'user_agent',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
