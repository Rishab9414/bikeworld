<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'sub_category_id', 'brand_id', 'manufacturer_id', 'supplier_id', 'tax_id', 'unit_id',
        'name', 'short_name', 'slug', 'sku', 'barcode', 'qr_code',
        'product_type', 'product_condition', 'hsn_code', 'country_of_origin',
        'warranty', 'return_days', 'replace_days', 'min_order_qty', 'max_order_qty',
        'description', 'short_description', 'long_description', 'specification',
        'installation_guide', 'box_contents', 'care_instructions', 'warranty_info',
        'price', 'compare_price', 'purchase_price', 'landing_cost', 'selling_price', 'mrp',
        'discount_percent', 'offer_price', 'dealer_price', 'wholesale_price', 'tax_included', 'commission',
        'stock', 'reserved_stock', 'low_stock_alert', 'warehouse', 'rack_number', 'reorder_level',
        'image', 'primary_image', 'thumbnail', 'gallery', 'video_url', 'youtube_url',
        'weight', 'length', 'width', 'height', 'shipping_class', 'free_shipping', 'cod_available',
        'meta_title', 'meta_keywords', 'meta_description', 'canonical_url', 'og_image',
        'status', 'is_active', 'featured', 'trending', 'new_arrival', 'best_seller',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'purchase_price' => 'decimal:2',
            'landing_cost' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'mrp' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'offer_price' => 'decimal:2',
            'dealer_price' => 'decimal:2',
            'wholesale_price' => 'decimal:2',
            'commission' => 'decimal:2',
            'weight' => 'decimal:3',
            'length' => 'decimal:2',
            'width' => 'decimal:2',
            'height' => 'decimal:2',
            'gallery' => 'array',
            'is_active' => 'boolean',
            'featured' => 'boolean',
            'trending' => 'boolean',
            'new_arrival' => 'boolean',
            'best_seller' => 'boolean',
            'tax_included' => 'boolean',
            'free_shipping' => 'boolean',
            'cod_available' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function subCategory(): BelongsTo { return $this->belongsTo(Category::class, 'sub_category_id'); }
    public function brand(): BelongsTo { return $this->belongsTo(Brand::class); }
    public function manufacturer(): BelongsTo { return $this->belongsTo(Manufacturer::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function tax(): BelongsTo { return $this->belongsTo(Tax::class); }
    public function unit(): BelongsTo { return $this->belongsTo(Unit::class); }
    public function variants(): HasMany { return $this->hasMany(ProductVariant::class); }
    public function features(): HasMany { return $this->hasMany(ProductFeature::class)->orderBy('sort_order'); }
    public function tags(): HasMany { return $this->hasMany(ProductTag::class); }
    public function documents(): HasMany { return $this->hasMany(ProductDocument::class); }
    public function bikeModels(): BelongsToMany { return $this->belongsToMany(BikeModel::class, 'product_bike_model'); }
    public function cartItems(): HasMany { return $this->hasMany(CartItem::class); }
    public function orderItems(): HasMany { return $this->hasMany(OrderItem::class); }
    public function reviews(): HasMany { return $this->hasMany(ProductReview::class); }

    public function isInStock(): bool { return $this->stock > 0; }

    public function formattedPrice(): string
    {
        return '₹'.number_format($this->price, 2);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('status', 'approved')->latest();
    }

    public function displayImage(): ?string
    {
        return $this->primary_image ?? $this->image ?? $this->thumbnail;
    }

    public function displayImageUrl(): ?string
    {
        $path = $this->displayImage();

        if (empty($path)) {
            return null;
        }

        return str_starts_with($path, 'http') ? $path : asset('storage/'.$path);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'published' => 'Published',
            'out_of_stock' => 'Out of Stock',
            'archived' => 'Archived',
            default => ucfirst($this->status),
        };
    }
}
