<?php

namespace Database\Seeders;

use App\Models\BikeModel;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Manufacturer;
use App\Models\Material;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Size;
use App\Models\Supplier;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\User;
use App\Models\VehicleBrand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPermissionsAndRoles();
        $this->seedCategories();
        $this->seedBrands();
        $this->seedManufacturers();
        $this->seedSuppliers();
        $this->seedTaxes();
        $this->seedUnits();
        $this->seedSizes();
        $this->seedColors();
        $this->seedMaterials();
        $this->seedVehicleBrandsAndModels();
        $this->assignAdminRole();
    }

    private function seedPermissionsAndRoles(): void
    {
        $groups = [
            'dashboard' => ['view-dashboard'],
            'masters' => ['manage-categories', 'manage-brands', 'manage-manufacturers', 'manage-suppliers', 'manage-taxes', 'manage-units', 'manage-sizes', 'manage-colors', 'manage-materials', 'manage-vehicle-brands', 'manage-bike-models'],
            'users' => ['manage-admin-users', 'manage-roles', 'manage-permissions', 'view-login-history', 'view-activity-logs'],
            'products' => ['manage-products', 'manage-inventory'],
            'orders' => ['manage-orders', 'manage-returns'],
            'reports' => ['view-reports'],
            'settings' => ['manage-settings'],
        ];

        $permissionIds = [];
        foreach ($groups as $group => $slugs) {
            foreach ($slugs as $slug) {
                $perm = Permission::create([
                    'name' => Str::title(str_replace('-', ' ', $slug)),
                    'slug' => $slug,
                    'group' => $group,
                ]);
                $permissionIds[] = $perm->id;
            }
        }

        $superAdmin = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Full system access',
            'status' => true,
        ]);
        $superAdmin->permissions()->sync($permissionIds);

        Role::create([
            'name' => 'Manager',
            'slug' => 'manager',
            'description' => 'Manage products and orders',
            'status' => true,
        ])->permissions()->sync(
            Permission::whereIn('group', ['dashboard', 'masters', 'products', 'orders'])->pluck('id')
        );
    }

    private function assignAdminRole(): void
    {
        $superAdmin = Role::where('slug', 'super-admin')->first();
        User::where('email', 'admin@bikeworld.com')->update(['role_id' => $superAdmin->id]);
    }

    private function seedCategories(): void
    {
        $root = Category::create([
            'name' => 'Bike Accessories',
            'slug' => 'bike-accessories',
            'description' => 'All bike accessories and riding gear',
            'display_order' => 1,
            'featured' => true,
            'show_in_menu' => true,
            'status' => 'active',
            'is_active' => true,
        ]);

        $subs = [
            'Helmet', 'Riding Jacket', 'Gloves', 'Tank Bag', 'Saddle Bag',
            'Phone Holder', 'Mobile Charger', 'Crash Guard', 'Engine Guard',
            'Tyres', 'Lights', 'Mirrors', 'Lubricants', 'Cleaning Products',
        ];

        foreach ($subs as $i => $name) {
            Category::create([
                'parent_id' => $root->id,
                'name' => $name,
                'slug' => Str::slug($name),
                'display_order' => $i + 1,
                'show_in_menu' => true,
                'status' => 'active',
                'is_active' => true,
            ]);
        }
    }

    private function seedBrands(): void
    {
        foreach (['Vega', 'Steelbird', 'Studds', 'Axor', 'MT', 'SMK', 'Rynox', 'Motul', 'Castrol'] as $name) {
            Brand::create(['name' => $name, 'slug' => Str::slug($name), 'status' => 'active']);
        }
    }

    private function seedManufacturers(): void
    {
        Manufacturer::create([
            'name' => 'BikeWorld Manufacturing Pvt Ltd',
            'address' => 'Plot 12, Industrial Area, Pune, Maharashtra',
            'gst_number' => '27AABCU9603R1ZM',
            'email' => 'manufacturing@bikeworld.com',
            'phone' => '+91-9876543210',
            'contact_person' => 'Rajesh Kumar',
            'status' => 'active',
        ]);
    }

    private function seedSuppliers(): void
    {
        Supplier::create([
            'name' => 'Auto Parts Wholesale India',
            'gst' => '27AAECA1234F1Z5',
            'address' => 'Wholesale Market, Delhi',
            'city' => 'Delhi', 'state' => 'Delhi', 'country' => 'India',
            'mobile' => '+91-9988776655',
            'email' => 'orders@autoparts.in',
            'bank_name' => 'HDFC Bank',
            'bank_account' => '12345678901234',
            'ifsc_code' => 'HDFC0001234',
            'status' => 'active',
        ]);
    }

    private function seedTaxes(): void
    {
        Tax::create(['name' => 'GST 5%', 'percentage' => 5, 'hsn_code' => '8714', 'description' => 'Bike parts 5% GST', 'status' => 'active']);
        Tax::create(['name' => 'GST 12%', 'percentage' => 12, 'hsn_code' => '8714', 'description' => 'Accessories 12% GST', 'status' => 'active']);
        Tax::create(['name' => 'GST 18%', 'percentage' => 18, 'hsn_code' => '8714', 'description' => 'General accessories 18% GST', 'status' => 'active']);
        Tax::create(['name' => 'GST 28%', 'percentage' => 28, 'hsn_code' => '8714', 'description' => 'Luxury items 28% GST', 'status' => 'active']);
    }

    private function seedUnits(): void
    {
        foreach (['Piece', 'Pair', 'Set', 'Bottle', 'Liter', 'Kg', 'Pack'] as $name) {
            Unit::create(['name' => $name, 'short_name' => strtoupper(substr($name, 0, 3)), 'status' => 'active']);
        }
    }

    private function seedSizes(): void
    {
        foreach (['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $i => $name) {
            Size::create(['name' => $name, 'display_order' => $i + 1, 'status' => 'active']);
        }
    }

    private function seedColors(): void
    {
        $colors = [
            ['Black', '#000000'], ['White', '#FFFFFF'], ['Red', '#DC2626'],
            ['Blue', '#2563EB'], ['Matte Black', '#1a1a1a'], ['Silver', '#C0C0C0'],
        ];
        foreach ($colors as [$name, $hex]) {
            Color::create(['name' => $name, 'hex_code' => $hex, 'status' => 'active']);
        }
    }

    private function seedMaterials(): void
    {
        foreach (['Leather', 'Carbon Fiber', 'Aluminium', 'Steel', 'Plastic', 'Rubber'] as $name) {
            Material::create(['name' => $name, 'status' => 'active']);
        }
    }

    private function seedVehicleBrandsAndModels(): void
    {
        $brands = [
            'Royal Enfield' => ['Classic 350', 'Hunter 350', 'Meteor 350', 'Himalayan', 'Bullet 350'],
            'Honda' => ['Shine', 'Unicorn', 'Hornet'],
            'KTM' => ['Duke 200', 'Duke 390', 'Adventure 390'],
            'Yamaha' => ['R15', 'FZ-S', 'MT-15'],
            'Hero' => ['Splendor', 'Passion Pro', 'Xtreme'],
            'Bajaj' => ['Pulsar 150', 'Pulsar 220', 'Dominar 400'],
            'TVS' => ['Apache RTR', 'Raider', 'Jupiter'],
            'Suzuki' => ['Gixxer', 'Access 125'],
            'Kawasaki' => ['Ninja 300', 'Z650'],
            'BMW' => ['G 310 R', 'G 310 GS'],
        ];

        foreach ($brands as $brandName => $models) {
            $slug = Str::slug($brandName);
            $brand = VehicleBrand::create([
                'name' => $brandName,
                'slug' => $slug,
                'image' => app(\App\Services\BrandImageGenerator::class)->generate($brandName, $slug),
                'show_in_shop' => true,
                'status' => 'active',
            ]);

            foreach ($models as $modelName) {
                BikeModel::create([
                    'vehicle_brand_id' => $brand->id,
                    'name' => $modelName,
                    'slug' => Str::slug($modelName),
                    'year' => '2024',
                    'engine_cc' => rand(125, 650),
                    'show_in_shop' => true,
                    'status' => 'active',
                ]);
            }
        }
    }
}
