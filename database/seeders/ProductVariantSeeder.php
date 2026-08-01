<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        $product = Product::where('slug', 'axor-apex-matte-black-helmet')->first();

        if (! $product) {
            $this->command->warn('Product not found: Axor Apex Full Face Helmet Matte Black');

            return;
        }

        $product->variants()->delete();

        $product->update([
            'product_type' => 'variable',
        ]);

        $variants = [
            [
                'sku' => 'BW-HEL-006-BK-M',
                'color_id' => 5,
                'size_id' => 3,
                'price' => 3799,
                'stock' => 12,
                'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
            ],
            [
                'sku' => 'BW-HEL-006-BK-L',
                'color_id' => 5,
                'size_id' => 4,
                'price' => 3799,
                'stock' => 10,
                'image' => 'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=800&q=80',
            ],
            [
                'sku' => 'BW-HEL-006-RD-M',
                'color_id' => 3,
                'size_id' => 3,
                'price' => 3899,
                'stock' => 8,
                'image' => 'https://images.unsplash.com/photo-1591637333184-19aa84baa3c5?w=800&q=80',
            ],
            [
                'sku' => 'BW-HEL-006-WH-L',
                'color_id' => 2,
                'size_id' => 4,
                'price' => 3899,
                'stock' => 6,
                'image' => 'https://images.unsplash.com/photo-1583121274602-3e2820c69888?w=800&q=80',
            ],
            [
                'sku' => 'BW-HEL-006-BL-XL',
                'color_id' => 4,
                'size_id' => 5,
                'price' => 3999,
                'stock' => 5,
                'image' => 'https://images.unsplash.com/photo-1619642751034-765df6897c10?w=800&q=80',
            ],
        ];

        foreach ($variants as $variant) {
            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $variant['sku'],
                'color_id' => $variant['color_id'],
                'size_id' => $variant['size_id'],
                'price' => $variant['price'],
                'stock' => $variant['stock'],
                'image' => $variant['image'],
                'is_active' => true,
            ]);
        }

        $this->command->info("Added ".count($variants)." variants to: {$product->name}");
        $this->command->info("View at: /products/{$product->slug}");
    }
}
