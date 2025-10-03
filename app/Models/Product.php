<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'description',
        'coating',
        'is_active',
    ];

    // Constantes para categorias
    const CATEGORIES = [
        'A' => 'Anel',
        'BR' => 'Brinco',
        'C'  => 'Colar',
        'CJ' => 'Conjunto',
        'PU' => 'Pulseira',
        'PI' => 'Pingente',
    ];

    // Constantes para banhos
    const COATINGS = [
        'DO' => 'Dourado',
        'RH' => 'Ródio branco',
        'RO' => 'Rose',
        'NE' => 'Ródio negro',
        'MIX' => 'Mix de banhos',
        'CL' => 'Colorido',
    ];

    // Métodos de acesso
    public function getCategoryNameAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function getCoatingNameAttribute(): ?string
    {
        return self::COATINGS[$this->coating] ?? $this->coating;
    }
}
