<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BikeModel;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Manufacturer;
use App\Models\Material;
use App\Models\Product;
use App\Models\Setting;
use App\Models\ProductDocument;
use App\Models\ProductFeature;
use App\Models\ProductTag;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Models\Supplier;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\VehicleBrand;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('admin.products.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = Product::query()
            ->select([
                'id', 'name', 'sku', 'category_id', 'brand_id',
                'selling_price', 'price', 'stock', 'status', 'featured',
                'primary_image', 'image', 'thumbnail', 'created_at',
            ])
            ->with([
                'category:id,name',
                'brand:id,name',
            ]);

        if ($request->filled('search')) {
            $s = '%'.$request->search.'%';
            $query->where(fn ($q) => $q->where('name', 'like', $s)
                ->orWhere('sku', 'like', $s)
                ->orWhere('barcode', 'like', $s));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $perPage = min(max((int) $request->input('per_page', 20), 10), 50);

        $paginator = $query->latest('id')->simplePaginate($perPage);

        $paginator->getCollection()->transform(fn (Product $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'category' => $p->category?->name,
            'brand' => $p->brand?->name,
            'selling_price' => $p->selling_price ?? $p->price,
            'stock' => $p->stock,
            'status' => $p->status,
            'featured' => $p->featured,
            'primary_image' => $p->displayImage(),
            'created_at' => $p->created_at?->format('M d, Y'),
        ]);

        return response()->json(['success' => true, 'data' => $paginator]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product([
                'status' => 'draft',
                'product_type' => 'simple',
                'product_condition' => 'new',
                'min_order_qty' => 1,
                'cod_available' => true,
                'tax_included' => Setting::defaultTaxIncluded(),
                'stock' => 0,
                'reserved_stock' => 0,
                'low_stock_alert' => 5,
            ]),
            'masters' => $this->masterOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProduct($request);

        $product = DB::transaction(function () use ($request, $data) {
            $product = Product::create($this->mapProductData($data, $request));
            $this->syncRelations($product, $request);

            return $product;
        });

        ActivityLogger::log('created', 'products', $product, "Product {$product->name} created");

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        $product->load(['variants.color', 'variants.size', 'variants.material', 'features', 'tags', 'bikeModels.vehicleBrand', 'documents']);

        return view('admin.products.form', [
            'product' => $product,
            'masters' => $this->masterOptions(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validateProduct($request, $product->id);

        DB::transaction(function () use ($request, $product, $data) {
            $product->update($this->mapProductData($data, $request, $product));
            $this->syncRelations($product, $request);
        });

        ActivityLogger::log('updated', 'products', $product, "Product {$product->name} updated");

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
        ActivityLogger::log('deleted', 'products', null, "Product #{$product->id} deleted");

        return response()->json(['success' => true, 'message' => 'Product deleted successfully.']);
    }

    public function options(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->masterOptions()]);
    }

    private function masterOptions(): array
    {
        $categories = Category::where('status', 'active')->orderBy('display_order')->get(['id', 'name', 'parent_id']);
        $rootCategories = $categories->whereNull('parent_id')->values();
        $subCategories = $categories->whereNotNull('parent_id')->values();

        return [
            'categories' => $rootCategories,
            'sub_categories' => $subCategories,
            'brands' => Brand::where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'manufacturers' => Manufacturer::where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'suppliers' => Supplier::where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'taxes' => Tax::where('status', 'active')->orderBy('name')->get(['id', 'name', 'percentage']),
            'units' => Unit::where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'colors' => Color::where('status', 'active')->orderBy('name')->get(['id', 'name', 'hex_code']),
            'sizes' => Size::where('status', 'active')->orderBy('display_order')->get(['id', 'name']),
            'materials' => Material::where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'vehicle_brands' => VehicleBrand::where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'bike_models' => BikeModel::with('vehicleBrand:id,name')->where('status', 'active')->orderBy('name')->get(['id', 'name', 'vehicle_brand_id']),
        ];
    }

    private function validateProduct(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug,'.$id],
            'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku,'.$id],
            'barcode' => ['nullable', 'string', 'max:100'],
            'qr_code' => ['nullable', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'manufacturer_id' => ['nullable', 'exists:manufacturers,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'tax_id' => ['nullable', 'exists:taxes,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'product_type' => ['required', 'in:simple,variable'],
            'product_condition' => ['required', 'in:new,used,refurbished'],
            'hsn_code' => ['nullable', 'string', 'max:50'],
            'country_of_origin' => ['nullable', 'string', 'max:100'],
            'warranty' => ['nullable', 'string', 'max:255'],
            'return_days' => ['nullable', 'integer', 'min:0'],
            'replace_days' => ['nullable', 'integer', 'min:0'],
            'min_order_qty' => ['nullable', 'integer', 'min:1'],
            'max_order_qty' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string'],
            'long_description' => ['nullable', 'string'],
            'specification' => ['nullable', 'string'],
            'installation_guide' => ['nullable', 'string'],
            'box_contents' => ['nullable', 'string'],
            'care_instructions' => ['nullable', 'string'],
            'warranty_info' => ['nullable', 'string'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'landing_cost' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'mrp' => ['nullable', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'offer_price' => ['nullable', 'numeric', 'min:0'],
            'dealer_price' => ['nullable', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'tax_included' => ['nullable', 'boolean'],
            'commission' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'reserved_stock' => ['nullable', 'integer', 'min:0'],
            'low_stock_alert' => ['nullable', 'integer', 'min:0'],
            'warehouse' => ['nullable', 'string', 'max:255'],
            'rack_number' => ['nullable', 'string', 'max:100'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'youtube_url' => ['nullable', 'url', 'max:500'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'shipping_class' => ['nullable', 'string', 'max:100'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'free_shipping' => ['nullable', 'boolean'],
            'cod_available' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_keywords' => ['nullable', 'string'],
            'meta_description' => ['nullable', 'string'],
            'canonical_url' => ['nullable', 'url', 'max:500'],
            'status' => ['required', 'in:draft,published,out_of_stock,archived'],
            'featured' => ['nullable', 'boolean'],
            'trending' => ['nullable', 'boolean'],
            'new_arrival' => ['nullable', 'boolean'],
            'best_seller' => ['nullable', 'boolean'],
            'primary_image' => ['nullable', 'image', 'max:5120'],
            'thumbnail' => ['nullable', 'image', 'max:5120'],
            'existing_gallery' => ['nullable', 'array'],
            'existing_gallery.*' => ['nullable', 'string', 'max:500'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['nullable', 'image', 'max:5120'],
            'bike_model_ids' => ['nullable', 'array'],
            'bike_model_ids.*' => ['exists:bike_models,id'],
            'features' => ['nullable', 'array'],
            'features.*' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string'],
            'variants' => ['nullable', 'array'],
            'variants.*.image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'variants.*.existing_image' => ['nullable', 'string', 'max:500'],
        ]);
    }

    private function mapProductData(array $data, Request $request, ?Product $existing = null): array
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['tax_included'] = $request->boolean('tax_included');
        $data['free_shipping'] = $request->boolean('free_shipping');
        $data['cod_available'] = $request->boolean('cod_available');
        $data['featured'] = $request->boolean('featured');
        $data['trending'] = $request->boolean('trending');
        $data['new_arrival'] = $request->boolean('new_arrival');
        $data['best_seller'] = $request->boolean('best_seller');
        $data['is_active'] = $data['status'] === 'published';
        $data['price'] = $data['offer_price'] ?? $data['selling_price'];
        $data['compare_price'] = $data['mrp'] ?? null;
        $data['description'] = $data['short_description'] ?? $data['long_description'] ?? $existing?->description;

        foreach ([
            'reserved_stock' => 0,
            'low_stock_alert' => 5,
            'min_order_qty' => 1,
        ] as $field => $default) {
            if (! isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                $data[$field] = $default;
            } else {
                $data[$field] = (int) $data[$field];
            }
        }

        if ($request->hasFile('primary_image')) {
            if ($existing?->primary_image) {
                Storage::disk('public')->delete($existing->primary_image);
            }
            $data['primary_image'] = $request->file('primary_image')->store('products', 'public');
            $data['image'] = $data['primary_image'];
        }

        if ($request->hasFile('thumbnail')) {
            if ($existing?->thumbnail) {
                Storage::disk('public')->delete($existing->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('products', 'public');
        }

        $data['gallery'] = $this->syncGalleryImages($request, $existing);

        return $data;
    }

    private function syncGalleryImages(Request $request, ?Product $existing = null): ?array
    {
        $gallery = collect($existing?->gallery ?? [])
            ->filter(fn ($path) => is_string($path) && $path !== '')
            ->values()
            ->all();

        if ($request->has('gallery_sync')) {
            $keep = collect($request->input('existing_gallery', []))
                ->filter(fn ($path) => is_string($path) && $path !== '')
                ->values()
                ->all();

            $removed = array_diff($gallery, $keep);
            foreach ($removed as $path) {
                Storage::disk('public')->delete($path);
            }

            $gallery = $keep;
        }

        $uploads = $request->file('gallery');
        if ($uploads !== null) {
            foreach (is_array($uploads) ? $uploads : [$uploads] as $file) {
                if ($file && $file->isValid()) {
                    $gallery[] = $file->store('products', 'public');
                }
            }
        }

        return $gallery !== [] ? array_values($gallery) : null;
    }

    private function syncRelations(Product $product, Request $request): void
    {
        $product->bikeModels()->sync($request->input('bike_model_ids', []));

        $product->features()->delete();
        foreach (array_filter($request->input('features', [])) as $i => $feature) {
            ProductFeature::create(['product_id' => $product->id, 'feature' => $feature, 'sort_order' => $i]);
        }

        $product->tags()->delete();
        $tags = array_filter(array_map('trim', explode(',', $request->input('tags', ''))));
        foreach ($tags as $tag) {
            ProductTag::create(['product_id' => $product->id, 'tag' => $tag]);
        }

        $previousVariantImages = $product->variants()->pluck('image')->filter()->all();
        $keptVariantImages = [];

        $product->variants()->delete();
        foreach ($request->input('variants', []) as $index => $variant) {
            if (empty($variant['sku']) && empty($variant['price']) && empty($variant['color_id']) && empty($variant['size_id'])) {
                continue;
            }

            $imagePath = $variant['existing_image'] ?? null;
            if ($request->hasFile("variants.{$index}.image")) {
                $imagePath = $request->file("variants.{$index}.image")->store('products/variants', 'public');
            }

            if ($imagePath) {
                $keptVariantImages[] = $imagePath;
            }

            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $variant['sku'] ?? null,
                'barcode' => $variant['barcode'] ?? null,
                'color_id' => $variant['color_id'] ?? null,
                'size_id' => $variant['size_id'] ?? null,
                'material_id' => $variant['material_id'] ?? null,
                'price' => $variant['price'] ?? null,
                'stock' => $variant['stock'] ?? 0,
                'weight' => $variant['weight'] ?? null,
                'image' => $imagePath,
                'is_active' => true,
            ]);
        }

        foreach ($previousVariantImages as $imagePath) {
            if (! in_array($imagePath, $keptVariantImages, true)) {
                Storage::disk('public')->delete($imagePath);
            }
        }

        foreach (['manual', 'installation', 'warranty', 'safety'] as $docType) {
            if ($request->hasFile("document_{$docType}")) {
                $path = $request->file("document_{$docType}")->store('products/documents', 'public');
                $product->documents()->where('type', $docType)->delete();
                ProductDocument::create([
                    'product_id' => $product->id,
                    'type' => $docType,
                    'title' => ucfirst($docType).' Document',
                    'file_path' => $path,
                ]);
            }
        }
    }
}
