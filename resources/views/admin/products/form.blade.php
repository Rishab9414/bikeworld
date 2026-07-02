@extends('admin.layouts.app')
@php
    $p = $product;
    $isEdit = $p->exists;
    $m = $masters;
    $qf = fn($fields) => htmlspecialchars(json_encode($fields), ENT_QUOTES, 'UTF-8');
    $subCats = $m['sub_categories'];
    $fe = fn(string $field) => $errors->has($field) ? 'admin-input-error' : '';
    $initialTab = $errors->hasAny(['selling_price', 'stock', 'purchase_price', 'mrp']) ? 'pricing' : 'basic';
@endphp
@section('title', $isEdit ? 'Edit Product' : 'Add Product')
@section('page-title', $isEdit ? 'Edit Product' : 'Add Product')
@section('page-subtitle', $isEdit ? $p->name : 'Create a new product with all details')

@section('content')
@if($errors->any())
<div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4">
    <p class="text-sm font-semibold text-red-800 mb-2">Please fix the following errors:</p>
    <ul class="list-disc list-inside space-y-1">
        @foreach($errors->all() as $error)
        <li class="text-sm text-red-700">{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ $isEdit ? route('admin.products.update', $p) : route('admin.products.store') }}" method="POST" enctype="multipart/form-data"
    x-data="{ tab: '{{ $initialTab }}' }" id="product-form" novalidate>
    @csrf
    @if($isEdit) @method('PUT') @endif

    {{-- Tab Nav --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm mb-6 overflow-x-auto">
        <div class="flex border-b border-slate-100 min-w-max">
            @foreach(['basic'=>'Basic','pricing'=>'Pricing & Stock','content'=>'Descriptions','media'=>'Images & Media','shipping'=>'Shipping','compatibility'=>'Compatibility','variants'=>'Variants','features'=>'Features & Tags','seo'=>'SEO','documents'=>'Documents'] as $key => $label)
            <button type="button" @click="tab='{{ $key }}'" :class="tab==='{{ $key }}' ? 'border-indigo-600 text-indigo-600 bg-indigo-50/50' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="px-5 py-3.5 text-sm font-semibold border-b-2 whitespace-nowrap transition-colors">{{ $label }}</button>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">

            {{-- BASIC --}}
            <div x-show="tab==='basic'" data-tab="basic" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                <h3 class="font-bold text-slate-900 text-lg">Basic Details</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Product Name <span class="text-red-500">*</span></label>
                        <input name="name" value="{{ old('name', $p->name) }}" required class="admin-input text-sm {{ $fe('name') }}">
                        @error('name')<p class="admin-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Short Name</label><input name="short_name" value="{{ old('short_name', $p->short_name) }}" class="admin-input text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">SKU</label><input name="sku" value="{{ old('sku', $p->sku) }}" class="admin-input text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Barcode</label><input name="barcode" value="{{ old('barcode', $p->barcode) }}" class="admin-input text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">QR Code</label><input name="qr_code" value="{{ old('qr_code', $p->qr_code) }}" class="admin-input text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Product Type</label><select name="product_type" class="admin-input text-sm"><option value="simple" @selected(old('product_type',$p->product_type)==='simple')>Simple</option><option value="variable" @selected(old('product_type',$p->product_type)==='variable')>Variable</option></select></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Condition</label><select name="product_condition" class="admin-input text-sm"><option value="new" @selected(old('product_condition',$p->product_condition)==='new')>New</option><option value="used" @selected(old('product_condition',$p->product_condition)==='used')>Used</option><option value="refurbished" @selected(old('product_condition',$p->product_condition)==='refurbished')>Refurbished</option></select></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">HSN Code</label><input name="hsn_code" value="{{ old('hsn_code', $p->hsn_code) }}" class="admin-input text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Country of Origin</label><input name="country_of_origin" value="{{ old('country_of_origin', $p->country_of_origin) }}" class="admin-input text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Warranty</label><input name="warranty" value="{{ old('warranty', $p->warranty) }}" class="admin-input text-sm" placeholder="1 Year"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Return Days</label><input name="return_days" type="number" value="{{ old('return_days', $p->return_days) }}" class="admin-input text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Replace Days</label><input name="replace_days" type="number" value="{{ old('replace_days', $p->replace_days) }}" class="admin-input text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Min Order Qty</label><input name="min_order_qty" type="number" value="{{ old('min_order_qty', $p->min_order_qty ?? 1) }}" class="admin-input text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Max Order Qty</label><input name="max_order_qty" type="number" value="{{ old('max_order_qty', $p->max_order_qty) }}" class="admin-input text-sm"></div>
                </div>
            </div>

            {{-- PRICING --}}
            <div x-show="tab==='pricing'" x-cloak data-tab="pricing" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                <h3 class="font-bold text-slate-900 text-lg">Pricing & Inventory</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Purchase Price</label><input name="purchase_price" type="number" step="0.01" value="{{ old('purchase_price', $p->purchase_price) }}" class="admin-input text-sm {{ $fe('purchase_price') }}">@error('purchase_price')<p class="admin-field-error">{{ $message }}</p>@enderror</div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Landing Cost</label><input name="landing_cost" type="number" step="0.01" value="{{ old('landing_cost', $p->landing_cost) }}" class="admin-input text-sm"></div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Selling Price <span class="text-red-500">*</span></label>
                        <input name="selling_price" type="number" step="0.01" min="0" value="{{ old('selling_price', $p->selling_price ?? $p->price) }}" required class="admin-input text-sm {{ $fe('selling_price') }}">
                        @error('selling_price')<p class="admin-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">MRP</label><input name="mrp" type="number" step="0.01" value="{{ old('mrp', $p->mrp ?? $p->compare_price) }}" class="admin-input text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Discount %</label><input name="discount_percent" type="number" step="0.01" value="{{ old('discount_percent', $p->discount_percent) }}" class="admin-input text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Offer Price</label><input name="offer_price" type="number" step="0.01" value="{{ old('offer_price', $p->offer_price) }}" class="admin-input text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Dealer Price</label><input name="dealer_price" type="number" step="0.01" value="{{ old('dealer_price', $p->dealer_price) }}" class="admin-input text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Wholesale Price</label><input name="wholesale_price" type="number" step="0.01" value="{{ old('wholesale_price', $p->wholesale_price) }}" class="admin-input text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Commission</label><input name="commission" type="number" step="0.01" value="{{ old('commission', $p->commission) }}" class="admin-input text-sm"></div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Stock <span class="text-red-500">*</span></label>
                        <input name="stock" type="number" min="0" value="{{ old('stock', $p->stock ?? 0) }}" required class="admin-input text-sm {{ $fe('stock') }}">
                        @error('stock')<p class="admin-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Reserved Stock</label><input name="reserved_stock" type="number" min="0" value="{{ old('reserved_stock', $p->reserved_stock ?? 0) }}" class="admin-input text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Low Stock Alert</label><input name="low_stock_alert" type="number" value="{{ old('low_stock_alert', $p->low_stock_alert ?? 5) }}" class="admin-input text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Warehouse</label><input name="warehouse" value="{{ old('warehouse', $p->warehouse) }}" class="admin-input text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Rack Number</label><input name="rack_number" value="{{ old('rack_number', $p->rack_number) }}" class="admin-input text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Reorder Level</label><input name="reorder_level" type="number" value="{{ old('reorder_level', $p->reorder_level) }}" class="admin-input text-sm"></div>
                </div>
                <label class="flex items-start gap-2">
                    <input type="checkbox" name="tax_included" value="1" @checked(old('tax_included', $p->tax_included)) class="rounded text-indigo-600 mt-0.5">
                    <span class="text-sm text-slate-700">
                        <span class="font-medium">Tax included in price</span>
                        <span class="block text-xs text-slate-500 mt-0.5">Leave unchecked to add GST at checkout on top of the selling price.</span>
                    </span>
                </label>
            </div>

            {{-- CONTENT --}}
            <div x-show="tab==='content'" x-cloak class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                <h3 class="font-bold text-slate-900 text-lg">Descriptions</h3>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Short Description</label><textarea name="short_description" rows="2" class="admin-input text-sm">{{ old('short_description', $p->short_description) }}</textarea></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Long Description</label><textarea name="long_description" rows="4" class="admin-input text-sm">{{ old('long_description', $p->long_description) }}</textarea></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Specification</label><textarea name="specification" rows="4" class="admin-input text-sm">{{ old('specification', $p->specification) }}</textarea></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Installation Guide</label><textarea name="installation_guide" rows="3" class="admin-input text-sm">{{ old('installation_guide', $p->installation_guide) }}</textarea></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Box Contents</label><textarea name="box_contents" rows="2" class="admin-input text-sm">{{ old('box_contents', $p->box_contents) }}</textarea></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Care Instructions</label><textarea name="care_instructions" rows="2" class="admin-input text-sm">{{ old('care_instructions', $p->care_instructions) }}</textarea></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Warranty Information</label><textarea name="warranty_info" rows="2" class="admin-input text-sm">{{ old('warranty_info', $p->warranty_info) }}</textarea></div>
            </div>

            {{-- MEDIA --}}
            <div x-show="tab==='media'" x-cloak class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                <h3 class="font-bold text-slate-900 text-lg">Images & Media</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Primary Image</label><input type="file" name="primary_image" accept="image/*" class="admin-input text-sm">@if($p->primary_image)<img src="{{ asset('storage/'.$p->primary_image) }}" class="mt-2 w-24 h-24 object-cover rounded-lg">@endif</div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Thumbnail</label><input type="file" name="thumbnail" accept="image/*" class="admin-input text-sm"></div>
                    <div class="sm:col-span-2"><label class="block text-sm font-medium text-slate-700 mb-1">Gallery Images</label><input type="file" name="gallery[]" accept="image/*" multiple class="admin-input text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Video URL</label><input name="video_url" value="{{ old('video_url', $p->video_url) }}" class="admin-input text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">YouTube URL</label><input name="youtube_url" value="{{ old('youtube_url', $p->youtube_url) }}" class="admin-input text-sm"></div>
                </div>
            </div>

            {{-- SHIPPING --}}
            <div x-show="tab==='shipping'" x-cloak class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                <h3 class="font-bold text-slate-900 text-lg">Shipping Details</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Weight (kg)</label><input name="weight" type="number" step="0.001" value="{{ old('weight', $p->weight) }}" class="admin-input text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Length (cm)</label><input name="length" type="number" step="0.01" value="{{ old('length', $p->length) }}" class="admin-input text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Width (cm)</label><input name="width" type="number" step="0.01" value="{{ old('width', $p->width) }}" class="admin-input text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Height (cm)</label><input name="height" type="number" step="0.01" value="{{ old('height', $p->height) }}" class="admin-input text-sm"></div>
                </div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Shipping Class</label><input name="shipping_class" value="{{ old('shipping_class', $p->shipping_class) }}" class="admin-input text-sm max-w-xs"></div>
                <label class="flex items-center gap-2"><input type="checkbox" name="free_shipping" value="1" @checked(old('free_shipping', $p->free_shipping)) class="rounded text-indigo-600"><span class="text-sm">Free Shipping</span></label>
                <label class="flex items-center gap-2"><input type="checkbox" name="cod_available" value="1" @checked(old('cod_available', $p->cod_available ?? true)) class="rounded text-indigo-600"><span class="text-sm">COD Available</span></label>
            </div>

            {{-- COMPATIBILITY --}}
            <div x-show="tab==='compatibility'" x-cloak class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                <h3 class="font-bold text-slate-900 text-lg">Vehicle Compatibility</h3>
                <p class="text-sm text-slate-500">Select all compatible bike models</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-80 overflow-y-auto border border-slate-200 rounded-xl p-4">
                    @php $selectedBikes = old('bike_model_ids', $p->bikeModels?->pluck('id')->toArray() ?? []); @endphp
                    @foreach($m['bike_models']->groupBy(fn($b) => $b->vehicleBrand->name) as $brandName => $models)
                        <div class="sm:col-span-2 font-semibold text-sm text-indigo-600 mt-2 first:mt-0">{{ $brandName }}</div>
                        @foreach($models as $bm)
                        <label class="flex items-center gap-2 text-sm text-slate-700 py-1"><input type="checkbox" name="bike_model_ids[]" value="{{ $bm->id }}" @checked(in_array($bm->id, $selectedBikes)) class="rounded text-indigo-600"><span>{{ $bm->name }}</span></label>
                        @endforeach
                    @endforeach
                </div>
            </div>

            {{-- VARIANTS --}}
            <div x-show="tab==='variants'" x-cloak class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                <div class="flex justify-between items-center"><h3 class="font-bold text-slate-900 text-lg">Product Variants</h3><button type="button" id="add-variant-btn" class="text-sm text-indigo-600 font-semibold">+ Add Variant</button></div>
                <div id="variants-container" class="space-y-3"></div>
            </div>

            {{-- FEATURES --}}
            <div x-show="tab==='features'" x-cloak class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                <div class="flex justify-between items-center"><h3 class="font-bold text-slate-900 text-lg">Features</h3><button type="button" id="add-feature-btn" class="text-sm text-indigo-600 font-semibold">+ Add Feature</button></div>
                <div id="features-container" class="space-y-2"></div>
                <div class="pt-4"><label class="block text-sm font-medium text-slate-700 mb-1">Tags (comma separated)</label><input name="tags" value="{{ old('tags', $p->tags?->pluck('tag')->implode(', ')) }}" class="admin-input text-sm" placeholder="Helmet, ISI Certified, Riding Gear"></div>
            </div>

            {{-- SEO --}}
            <div x-show="tab==='seo'" x-cloak class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                <h3 class="font-bold text-slate-900 text-lg">SEO Settings</h3>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">URL Slug</label><input name="slug" value="{{ old('slug', $p->slug) }}" class="admin-input text-sm"></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Meta Title</label><input name="meta_title" value="{{ old('meta_title', $p->meta_title) }}" class="admin-input text-sm"></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Meta Keywords</label><textarea name="meta_keywords" rows="2" class="admin-input text-sm">{{ old('meta_keywords', $p->meta_keywords) }}</textarea></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Meta Description</label><textarea name="meta_description" rows="3" class="admin-input text-sm">{{ old('meta_description', $p->meta_description) }}</textarea></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Canonical URL</label><input name="canonical_url" value="{{ old('canonical_url', $p->canonical_url) }}" class="admin-input text-sm"></div>
            </div>

            {{-- DOCUMENTS --}}
            <div x-show="tab==='documents'" x-cloak class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                <h3 class="font-bold text-slate-900 text-lg">Product Documents</h3>
                @foreach(['manual'=>'User Manual PDF','installation'=>'Installation PDF','warranty'=>'Warranty Card','safety'=>'Safety Instructions'] as $docKey => $docLabel)
                <div><label class="block text-sm font-medium text-slate-700 mb-1">{{ $docLabel }}</label><input type="file" name="document_{{ $docKey }}" accept=".pdf,.doc,.docx" class="admin-input text-sm">
                @php $doc = $p->documents?->firstWhere('type', $docKey); @endphp
                @if($doc)<p class="text-xs text-emerald-600 mt-1">Uploaded: {{ basename($doc->file_path) }}</p>@endif</div>
                @endforeach
            </div>
        </div>

        {{-- SIDEBAR --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4 sticky top-24" data-tab="publish">
                <h3 class="font-bold text-slate-900">Publish</h3>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" required class="admin-input text-sm {{ $fe('status') }}">
                        @foreach(['draft','published','out_of_stock','archived'] as $st)
                        <option value="{{ $st }}" @selected(old('status', $p->status ?? 'draft')===$st)>{{ ucfirst(str_replace('_',' ',$st)) }}</option>
                        @endforeach
                    </select>
                    @error('status')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-2">
                    <label class="flex items-center gap-2"><input type="checkbox" name="featured" value="1" @checked(old('featured',$p->featured)) class="rounded text-indigo-600"><span class="text-sm">Featured</span></label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="trending" value="1" @checked(old('trending',$p->trending)) class="rounded text-indigo-600"><span class="text-sm">Trending</span></label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="new_arrival" value="1" @checked(old('new_arrival',$p->new_arrival)) class="rounded text-indigo-600"><span class="text-sm">New Arrival</span></label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="best_seller" value="1" @checked(old('best_seller',$p->best_seller)) class="rounded text-indigo-600"><span class="text-sm">Best Seller</span></label>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl">{{ $isEdit ? 'Update Product' : 'Create Product' }}</button>
                <a href="{{ route('admin.products.index') }}" class="block text-center text-sm text-slate-500 hover:text-slate-700">Cancel</a>
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4" data-tab="classification" id="classification-section">
                <h3 class="font-bold text-slate-900">Classification</h3>
                @include('admin.products.partials.master-select', ['name'=>'category_id','label'=>'Category','options'=>$m['categories'],'value'=>$p->category_id,'required'=>true,'storeUrl'=>route('admin.masters.categories.store'),'fields'=>$qf([['name'=>'name','label'=>'Category Name','required'=>true]])])
                <div data-master-wrap data-store-url="{{ route('admin.masters.categories.store') }}" data-master-label="Add Sub Category" data-fields="{{ $qf([['name'=>'name','label'=>'Sub Category Name','required'=>true],['name'=>'parent_id','label'=>'','required'=>false]]) }}">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Sub Category</label>
                    <div class="flex gap-2">
                        <select name="sub_category_id" id="sub_category_id" class="admin-input text-sm flex-1 py-2.5">
                            <option value="">Select Sub Category</option>
                            @foreach($subCats->where('parent_id', $p->category_id) as $sc)
                            <option value="{{ $sc->id }}" @selected(old('sub_category_id', $p->sub_category_id)==$sc->id)>{{ $sc->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" data-quick-add class="shrink-0 w-10 h-10 flex items-center justify-center bg-indigo-100 text-indigo-700 hover:bg-indigo-200 rounded-xl font-bold text-lg">+</button>
                    </div>
                </div>
                @include('admin.products.partials.master-select', ['name'=>'brand_id','label'=>'Brand','options'=>$m['brands'],'value'=>$p->brand_id,'storeUrl'=>route('admin.masters.brands.store'),'fields'=>$qf([['name'=>'name','label'=>'Brand Name','required'=>true]])])
                @include('admin.products.partials.master-select', ['name'=>'manufacturer_id','label'=>'Manufacturer','options'=>$m['manufacturers'],'value'=>$p->manufacturer_id,'storeUrl'=>route('admin.masters.manufacturers.store'),'fields'=>$qf([['name'=>'name','label'=>'Manufacturer Name','required'=>true]])])
                @include('admin.products.partials.master-select', ['name'=>'supplier_id','label'=>'Supplier','options'=>$m['suppliers'],'value'=>$p->supplier_id,'storeUrl'=>route('admin.masters.suppliers.store'),'fields'=>$qf([['name'=>'name','label'=>'Supplier Name','required'=>true]])])
                @include('admin.products.partials.master-select', ['name'=>'tax_id','label'=>'Tax / GST','options'=>$m['taxes'],'value'=>$p->tax_id,'storeUrl'=>route('admin.masters.taxes.store'),'fields'=>$qf([['name'=>'name','label'=>'Tax Name','required'=>true],['name'=>'percentage','label'=>'Percentage','required'=>true,'placeholder'=>'18']])])
                @include('admin.products.partials.master-select', ['name'=>'unit_id','label'=>'Unit','options'=>$m['units'],'value'=>$p->unit_id,'storeUrl'=>route('admin.masters.units.store'),'fields'=>$qf([['name'=>'name','label'=>'Unit Name','required'=>true]])])
            </div>
        </div>
    </div>
</form>

@include('admin.products.partials.quick-master-modal')
@endsection

@push('scripts')
<script type="module">
const masters = @json($m);
window.productFormMasters = masters;
const existingVariants = @json($p->variants ?? []);
const existingFeatures = @json($p->features?->pluck('feature') ?? []);

const form = document.getElementById('product-form');
const fieldTabMap = {
    name: 'basic', short_name: 'basic', sku: 'basic', product_type: 'basic', product_condition: 'basic',
    selling_price: 'pricing', stock: 'pricing', purchase_price: 'pricing', mrp: 'pricing',
    category_id: 'classification', sub_category_id: 'classification', brand_id: 'classification',
    status: 'publish',
};

function markFieldError(el) {
    el.classList.add('admin-input-error');
}

function clearFieldError(el) {
    el.classList.remove('admin-input-error');
}

function focusField(el) {
    if (!el || !form) return;
    const panel = el.closest('[data-tab]');
    const tab = panel?.dataset.tab;
    if (tab === 'classification' || tab === 'publish') {
        panel?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else if (tab && form._x_dataStack) {
        Alpine.$data(form).tab = tab;
    }
    setTimeout(() => {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        el.focus({ preventScroll: true });
    }, 100);
}

function validateProductForm(e) {
    if (!form) return true;
    form.querySelectorAll('.admin-input-error').forEach(clearFieldError);

    const required = form.querySelectorAll('[required]');
    let firstInvalid = null;

    required.forEach(el => {
        const empty = el.type === 'select-one' ? !el.value : !String(el.value).trim();
        if (empty) {
            markFieldError(el);
            if (!firstInvalid) firstInvalid = el;
        }
    });

    if (firstInvalid) {
        e.preventDefault();
        focusField(firstInvalid);
        return false;
    }

    if (!form.checkValidity()) {
        e.preventDefault();
        const invalid = form.querySelector(':invalid');
        if (invalid) {
            markFieldError(invalid);
            focusField(invalid);
        }
        return false;
    }

    return true;
}

form?.addEventListener('submit', validateProductForm);
form?.querySelectorAll('input, select, textarea').forEach(el => {
    el.addEventListener('input', () => clearFieldError(el));
    el.addEventListener('change', () => clearFieldError(el));
});

@if($errors->any())
const firstErrorField = @json($errors->keys()[0] ?? null);
if (firstErrorField && form) {
    const el = form.querySelector(`[name="${firstErrorField}"]`);
    if (el) focusField(el);
}
@endif

document.getElementById('category_id')?.addEventListener('change', function() {
    window.productForm.filterSubCategories(this.value, document.getElementById('sub_category_id'), masters.sub_categories);
});

document.getElementById('add-variant-btn')?.addEventListener('click', () => {
    window.productForm.addVariantRow(document.getElementById('variants-container'), masters);
});
document.getElementById('add-feature-btn')?.addEventListener('click', () => {
    window.productForm.addFeatureRow(document.getElementById('features-container'));
});

existingVariants.forEach(v => window.productForm.addVariantRow(document.getElementById('variants-container'), masters, v));
existingFeatures.forEach(f => window.productForm.addFeatureRow(document.getElementById('features-container'), f));
if (!existingFeatures.length) window.productForm.addFeatureRow(document.getElementById('features-container'));
</script>
@endpush
