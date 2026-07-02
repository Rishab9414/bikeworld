<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tax;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $rootCategory = Category::where('slug', 'bike-accessories')->first();
        if (! $rootCategory) {
            $this->command->warn('Run MasterDataSeeder first.');

            return;
        }

        $subCategories = Category::where('parent_id', $rootCategory->id)->get()->keyBy('slug');
        $brands = Brand::pluck('id', 'name');
        $tax18 = Tax::where('percentage', 18)->first();
        $tax12 = Tax::where('percentage', 12)->first();
        $unitPiece = Unit::where('name', 'Piece')->first();
        $unitPair = Unit::where('name', 'Pair')->first();
        $unitBottle = Unit::where('name', 'Bottle')->first();

        $products = [
            [
                'name' => 'Vega Crux ISI Full Face Helmet',
                'slug' => 'vega-crux-isi-full-face-helmet',
                'sku' => 'BW-HEL-001',
                'sub' => 'helmet',
                'brand' => 'Vega',
                'mrp' => 2499, 'selling' => 1899, 'stock' => 45,
                'short' => 'ISI certified full face helmet with clear visor and ventilation ports.',
                'featured' => true, 'trending' => true, 'new_arrival' => true,
                'warranty' => '1 Year', 'hsn' => '65061000', 'weight' => 1.2,
            ],
            [
                'name' => 'Steelbird SBA-7 7Wings ISI Helmet',
                'slug' => 'steelbird-sba-7-isi-helmet',
                'sku' => 'BW-HEL-002',
                'sub' => 'helmet',
                'brand' => 'Steelbird',
                'mrp' => 1999, 'selling' => 1499, 'stock' => 60,
                'short' => 'Lightweight ISI helmet with aerodynamic design for daily commute.',
                'featured' => true, 'best_seller' => true,
                'warranty' => '1 Year', 'hsn' => '65061000', 'weight' => 1.1,
            ],
            [
                'name' => 'Studds Ninja Elite Full Face Helmet',
                'slug' => 'studds-ninja-elite-helmet',
                'sku' => 'BW-HEL-003',
                'sub' => 'helmet',
                'brand' => 'Studds',
                'mrp' => 3299, 'selling' => 2599, 'stock' => 30,
                'short' => 'Premium full face helmet with anti-scratch visor.',
                'featured' => false, 'trending' => true,
                'warranty' => '2 Years', 'hsn' => '65061000', 'weight' => 1.3,
            ],
            [
                'name' => 'Rynox Tornado Pro Riding Jacket',
                'slug' => 'rynox-tornado-pro-riding-jacket',
                'sku' => 'BW-JKT-001',
                'sub' => 'riding-jacket',
                'brand' => 'Rynox',
                'mrp' => 8999, 'selling' => 7499, 'stock' => 20,
                'short' => 'All-weather riding jacket with CE Level 2 armor and removable thermal liner.',
                'featured' => true, 'new_arrival' => true,
                'warranty' => '1 Year', 'hsn' => '62014090', 'weight' => 1.8,
                'unit' => 'piece',
            ],
            [
                'name' => 'Rynox Assault Pro Riding Gloves',
                'slug' => 'rynox-assault-pro-gloves',
                'sku' => 'BW-GLV-001',
                'sub' => 'gloves',
                'brand' => 'Rynox',
                'mrp' => 2499, 'selling' => 1999, 'stock' => 55,
                'short' => 'Touchscreen compatible riding gloves with knuckle protection.',
                'featured' => true, 'best_seller' => true,
                'warranty' => '6 Months', 'hsn' => '42032920', 'weight' => 0.3,
                'unit' => 'pair',
            ],
            [
                'name' => 'Axor Apex Full Face Helmet Matte Black',
                'slug' => 'axor-apex-matte-black-helmet',
                'sku' => 'BW-HEL-004',
                'sub' => 'helmet',
                'brand' => 'Axor',
                'mrp' => 4499, 'selling' => 3799, 'stock' => 25,
                'short' => 'Dual visor sports helmet with pinlock ready visor.',
                'featured' => true,
                'warranty' => '2 Years', 'hsn' => '65061000', 'weight' => 1.4,
            ],
            [
                'name' => 'Universal Magnetic Tank Bag 20L',
                'slug' => 'universal-magnetic-tank-bag-20l',
                'sku' => 'BW-BAG-001',
                'sub' => 'tank-bag',
                'brand' => 'MT',
                'mrp' => 2999, 'selling' => 2199, 'stock' => 40,
                'short' => 'Water-resistant magnetic tank bag with clear map pocket.',
                'featured' => false, 'trending' => true,
                'warranty' => '6 Months', 'hsn' => '42029200', 'weight' => 0.9,
            ],
            [
                'name' => 'Waterproof Saddle Bag Pair 28L',
                'slug' => 'waterproof-saddle-bag-28l',
                'sku' => 'BW-BAG-002',
                'sub' => 'saddle-bag',
                'brand' => 'SMK',
                'mrp' => 5499, 'selling' => 4599, 'stock' => 18,
                'short' => 'Heavy-duty saddle bags with rain cover for touring.',
                'featured' => true,
                'warranty' => '1 Year', 'hsn' => '42029200', 'weight' => 2.5,
            ],
            [
                'name' => 'Anti-Vibration Phone Holder with USB Charger',
                'slug' => 'phone-holder-usb-charger',
                'sku' => 'BW-PHN-001',
                'sub' => 'phone-holder',
                'brand' => 'MT',
                'mrp' => 1499, 'selling' => 999, 'stock' => 80,
                'short' => '360° rotatable phone mount with built-in 5V USB charging.',
                'featured' => true, 'best_seller' => true, 'trending' => true,
                'warranty' => '6 Months', 'hsn' => '85177010', 'weight' => 0.4,
            ],
            [
                'name' => '12V Bike Mobile Charger Waterproof',
                'slug' => '12v-bike-mobile-charger',
                'sku' => 'BW-CHG-001',
                'sub' => 'mobile-charger',
                'brand' => 'MT',
                'mrp' => 799, 'selling' => 549, 'stock' => 120,
                'short' => 'Dual USB fast charger for handlebar mount installation.',
                'featured' => false,
                'warranty' => '3 Months', 'hsn' => '85177010', 'weight' => 0.15,
            ],
            [
                'name' => 'Royal Enfield Classic 350 Crash Guard',
                'slug' => 're-classic-350-crash-guard',
                'sku' => 'BW-GRD-001',
                'sub' => 'crash-guard',
                'brand' => 'Steelbird',
                'mrp' => 3999, 'selling' => 3299, 'stock' => 15,
                'short' => 'Heavy-duty stainless steel crash guard for Classic 350.',
                'featured' => true,
                'warranty' => '1 Year', 'hsn' => '87141090', 'weight' => 3.5,
            ],
            [
                'name' => 'Universal Engine Guard Skid Plate',
                'slug' => 'universal-engine-guard-skid-plate',
                'sku' => 'BW-GRD-002',
                'sub' => 'engine-guard',
                'brand' => 'Steelbird',
                'mrp' => 2499, 'selling' => 1999, 'stock' => 22,
                'short' => 'Aluminium engine guard for adventure and street bikes.',
                'featured' => false,
                'warranty' => '1 Year', 'hsn' => '87141090', 'weight' => 2.8,
            ],
            [
                'name' => 'Michelin City Pro Tyre 100/80-17',
                'slug' => 'michelin-city-pro-tyre-100-80-17',
                'sku' => 'BW-TYR-001',
                'sub' => 'tyres',
                'brand' => 'Vega',
                'mrp' => 3200, 'selling' => 2799, 'stock' => 35,
                'short' => 'Tubeless tyre with superior wet grip for city riding.',
                'featured' => true, 'best_seller' => true,
                'warranty' => 'Manufacturer Warranty', 'hsn' => '40114010', 'weight' => 4.2,
            ],
            [
                'name' => 'LED Projector Headlight Bulb H4 6000K',
                'slug' => 'led-projector-headlight-h4',
                'sku' => 'BW-LGT-001',
                'sub' => 'lights',
                'brand' => 'MT',
                'mrp' => 1999, 'selling' => 1299, 'stock' => 65,
                'short' => 'Ultra-bright LED headlight with fan cooling and CAN bus ready.',
                'featured' => true, 'trending' => true,
                'warranty' => '1 Year', 'hsn' => '85395200', 'weight' => 0.25,
            ],
            [
                'name' => 'Bar End Convex Mirror Pair',
                'slug' => 'bar-end-convex-mirror-pair',
                'sku' => 'BW-MIR-001',
                'sub' => 'mirrors',
                'brand' => 'SMK',
                'mrp' => 899, 'selling' => 649, 'stock' => 90,
                'short' => 'Wide-angle bar end mirrors with anti-vibration mount.',
                'featured' => false,
                'warranty' => '3 Months', 'hsn' => '70091000', 'weight' => 0.35,
                'unit' => 'pair',
            ],
            [
                'name' => 'Motul 5100 20W50 Engine Oil 1L',
                'slug' => 'motul-5100-20w50-1l',
                'sku' => 'BW-LUB-001',
                'sub' => 'lubricants',
                'brand' => 'Motul',
                'mrp' => 599, 'selling' => 499, 'stock' => 200,
                'short' => 'Semi-synthetic 4-stroke engine oil for bikes.',
                'featured' => true, 'best_seller' => true,
                'warranty' => 'N/A', 'hsn' => '27101980', 'weight' => 1.0,
                'unit' => 'bottle', 'tax' => 12,
            ],
            [
                'name' => 'Castrol Power1 Cruise 20W50 1L',
                'slug' => 'castrol-power1-cruise-1l',
                'sku' => 'BW-LUB-002',
                'sub' => 'lubricants',
                'brand' => 'Castrol',
                'mrp' => 549, 'selling' => 459, 'stock' => 180,
                'short' => 'Premium 4T engine oil for long ride performance.',
                'featured' => false, 'trending' => true,
                'warranty' => 'N/A', 'hsn' => '27101980', 'weight' => 1.0,
                'unit' => 'bottle', 'tax' => 12,
            ],
            [
                'name' => 'Bike Chain Cleaner & Lube Kit',
                'slug' => 'bike-chain-cleaner-lube-kit',
                'sku' => 'BW-CLN-001',
                'sub' => 'cleaning-products',
                'brand' => 'Motul',
                'mrp' => 1299, 'selling' => 999, 'stock' => 75,
                'short' => 'Complete chain cleaning kit with brush and chain lube spray.',
                'featured' => true, 'new_arrival' => true,
                'warranty' => 'N/A', 'hsn' => '34029099', 'weight' => 0.8,
            ],
        ];

        $created = 0;
        foreach ($products as $p) {
            if (Product::where('slug', $p['slug'])->exists()) {
                continue;
            }

            $subCat = $subCategories->get($p['sub']);
            $unit = match ($p['unit'] ?? 'piece') {
                'pair' => $unitPair,
                'bottle' => $unitBottle,
                default => $unitPiece,
            };
            $tax = ($p['tax'] ?? 18) === 12 ? $tax12 : $tax18;
            $discount = round((($p['mrp'] - $p['selling']) / $p['mrp']) * 100, 1);

            Product::create([
                'category_id' => $rootCategory->id,
                'sub_category_id' => $subCat?->id,
                'brand_id' => $brands[$p['brand']] ?? null,
                'tax_id' => $tax?->id,
                'unit_id' => $unit?->id,
                'name' => $p['name'],
                'short_name' => Str::limit($p['name'], 30),
                'slug' => $p['slug'],
                'sku' => $p['sku'],
                'product_type' => 'simple',
                'product_condition' => 'new',
                'hsn_code' => $p['hsn'],
                'country_of_origin' => 'India',
                'warranty' => $p['warranty'],
                'return_days' => 7,
                'replace_days' => 15,
                'min_order_qty' => 1,
                'short_description' => $p['short'],
                'description' => $p['short'],
                'long_description' => $p['short'].' Ideal for Indian riding conditions. Shop with confidence at BikeWorld.',
                'specification' => 'Genuine product · ISI/BIS certified where applicable · Fast delivery across India',
                'purchase_price' => round($p['selling'] * 0.65, 2),
                'landing_cost' => round($p['selling'] * 0.75, 2),
                'selling_price' => $p['selling'],
                'mrp' => $p['mrp'],
                'price' => $p['selling'],
                'compare_price' => $p['mrp'],
                'discount_percent' => $discount,
                'offer_price' => $p['selling'],
                'stock' => $p['stock'],
                'reserved_stock' => 0,
                'low_stock_alert' => 5,
                'weight' => $p['weight'],
                'free_shipping' => $p['selling'] >= 2000,
                'cod_available' => true,
                'status' => 'published',
                'is_active' => true,
                'featured' => $p['featured'] ?? false,
                'trending' => $p['trending'] ?? false,
                'new_arrival' => $p['new_arrival'] ?? false,
                'best_seller' => $p['best_seller'] ?? false,
                'meta_title' => $p['name'].' | '.config('app.name'),
                'meta_description' => $p['short'],
            ]);

            $created++;
        }

        $this->command->info("Created {$created} dummy products (skipped existing slugs).");
    }
}
