<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_type', 'transaction_type', 'jurisdiction', 'order', 'key', 'title',
        'purpose', 'steps', 'legal_note', 'link_label', 'link_url', 'mandatory',
    ];

    protected $casts = [
        'mandatory' => 'boolean',
    ];
}
