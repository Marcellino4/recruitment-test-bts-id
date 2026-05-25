<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title',
        'price',
        'description',
        'category',
        'images',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'float',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'price' => $this->price,
            'description' => $this->description,
            'category' => $this->category,
            'images' => $this->images,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'created_by' => $this->createdBy?->name,
            'created_by_id' => (string) $this->created_by_id,
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'updated_by' => $this->updatedBy?->name,
            'updated_by_id' => (string) $this->updated_by_id,
        ];
    }
}
