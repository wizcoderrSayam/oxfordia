<?php

namespace App\Models;

use App\Enums\CourseStatus;
use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'category_id', 'title', 'slug', 'course_code', 'short_description', 'description',
    'duration_value', 'duration_unit', 'fee', 'is_featured', 'status',
])]
class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory;

    public const DURATION_UNITS = ['hours', 'days', 'weeks', 'months', 'years'];

    /**
     * Free-licence Unsplash photos used until an image is uploaded.
     */
    private const DEFAULT_IMAGES = [
        'photo-1522202176988-66273c2fd55f',
        'photo-1498050108023-c5249f4df085',
        'photo-1526379095098-d400fd0bf935',
        'photo-1561070791-2526d30994b5',
        'photo-1551288049-bebda4e38f71',
        'photo-1554224155-6726b3ff858f',
        'photo-1531482615713-2afd69097998',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CourseStatus::class,
            'fee' => 'decimal:2',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<CourseCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'category_id');
    }

    /**
     * @return HasMany<CourseModule, $this>
     */
    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * @return HasMany<Certificate, $this>
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @param  Builder<Course>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('status', CourseStatus::Published);
    }

    public function isPublished(): bool
    {
        return $this->status === CourseStatus::Published;
    }

    public function isPurchasable(): bool
    {
        return $this->isPublished() && $this->fee !== null && (float) $this->fee > 0;
    }

    /**
     * Whether the course has history that forbids a hard delete.
     */
    public function hasHistory(): bool
    {
        return $this->enrollments()->exists() || $this->orders()->exists() || $this->certificates()->exists();
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function durationLabel(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->duration_value || ! $this->duration_unit) {
                return null;
            }

            return $this->duration_value.' '.Str::plural(Str::singular($this->duration_unit), $this->duration_value);
        });
    }

    /**
     * Public URL of the uploaded image, or a default stock image.
     *
     * @return Attribute<string, never>
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->image_path) {
                return Storage::disk('public')->url($this->image_path);
            }

            $photo = self::DEFAULT_IMAGES[($this->id ?? 0) % count(self::DEFAULT_IMAGES)];

            return "https://images.unsplash.com/{$photo}?auto=format&fit=crop&w=1200&q=70";
        });
    }
}
