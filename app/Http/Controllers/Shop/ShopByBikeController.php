<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\BikeModel;
use App\Models\VehicleBrand;
use App\Services\SeoService;
use Illuminate\View\View;

class ShopByBikeController extends Controller
{
    public function brand(VehicleBrand $vehicleBrand): View
    {
        abort_unless($vehicleBrand->status === 'active' && $vehicleBrand->show_in_shop, 404);

        $models = $vehicleBrand->bikeModels()
            ->where('status', 'active')
            ->where('show_in_shop', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('shop.shop-by-bike.brand', compact('vehicleBrand', 'models'));
    }

    public function model(VehicleBrand $vehicleBrand, BikeModel $bikeModel): View
    {
        abort_unless($bikeModel->vehicle_brand_id === $vehicleBrand->id, 404);

        abort_unless(
            $bikeModel->status === 'active'
            && $bikeModel->show_in_shop
            && $vehicleBrand->status === 'active'
            && $vehicleBrand->show_in_shop,
            404
        );

        $products = $bikeModel->products()
            ->with('category')
            ->where('is_active', true)
            ->latest()
            ->paginate(12);

        $bikeModel->setRelation('vehicleBrand', $vehicleBrand);

        $seo = app(SeoService::class)->forBikeModel($bikeModel);

        return view('shop.shop-by-bike.model', compact('bikeModel', 'products', 'vehicleBrand', 'seo'));
    }
}
