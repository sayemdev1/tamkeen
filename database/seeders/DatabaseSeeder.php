<?php


use Illuminate\Database\Seeder;
use App\Models\{Product, ProductCategory, Rating, Store, Order, OrderProduct, PaymentMethod, Address, Role, User, CategoryProduct, Coupon, CouponOrderUser, CouponPackageUser, MembershipLevel, Package, PackageItem, PackageUser};

class DatabaseSeeder extends Seeder
{
    public function run()
    {
       
          Role::create(['name' => 'admin', 'description' => 'Administrator']);
        Role::create(['name' => 'user', 'description' => 'Regular User']);
        Role::create(['name' => 'distributor', 'description' => 'Product Distributor']);
        // Seed users
        User::factory()->count(10)->create();

        // Seed stores
        Store::factory()->count(10)->create();

        // Seed categories
        ProductCategory::factory()->count(10)->create();

        // Seed products
        Product::factory()->count(10)->create();

        CategoryProduct::factory()->count(10)->create();

        // Seed ratings
        Rating::factory()->count(10)->create();

        // Seed orders
        Order::factory()->count(10)->create();

        // Seed order products
        OrderProduct::factory()->count(10)->create();

        // Seed payment methods
        PaymentMethod::factory()->count(10)->create();

        // Seed addresses
        Address::factory()->count(10)->create();

        MembershipLevel::create([
            'level_name' => 'Basic',
            'monthly_fee' => 10.00,
            'description' => 'Basic membership with limited access.',
            'condition_1' => 'Condition 1 description',
            'condition_2' => 'Condition 2 description',
            'icon' => 'icon-url-1.png',
        ]);

        MembershipLevel::create([
            'level_name' => 'Premium',
            'monthly_fee' => 20.00,
            'description' => 'Premium membership with more benefits.',
            'condition_1' => 'Condition 1 description',
            'condition_2' => 'Condition 2 description',
            'icon' => 'icon-url-2.png',
        ]);

        MembershipLevel::create([
            'level_name' => 'VIP',
            'monthly_fee' => 30.00,
            'description' => 'VIP membership with all features.',
            'condition_1' => 'Condition 1 description',
            'condition_2' => 'Condition 2 description',
            'icon' => 'icon-url-3.png',
        ]);

        // Seed Packages
        Package::create([
            'name' => 'Basic Package',
            'total_price' => 100.00,
            'number_of_uses' => 5,
            'profit_percentage_in_level_1' => 10,
            'profit_percentage_in_level_2' => 15,
            'profit_percentage_in_level_3' => 20,
        ]);

        Package::create([
            'name' => 'Standard Package',
            'total_price' => 200.00,
            'number_of_uses' => 10,
            'profit_percentage_in_level_1' => 15,
            'profit_percentage_in_level_2' => 20,
            'profit_percentage_in_level_3' => 25,
        ]);

        // Seed Package Items
        PackageItem::create([
            'package_id' => 1, // Assuming package with ID 1
            'product_id' => 1, // Assuming product with ID 1
            'quantity' => 2,
        ]);

        PackageItem::create([
            'package_id' => 2, // Assuming package with ID 2
            'product_id' => 2, // Assuming product with ID 2
            'quantity' => 3,
        ]);

        // Seed Coupons
        Coupon::create([
            'name' => 'Summer Sale',
            'coupon_type' => 'discount',
            'promotion_code' => 'SUMMER2023',
            'expired_at' => now()->addMonths(1),
            'discount_type' => 'percentage',
            'percentage' => 10,
            'status' => 'active',
            'number_of_uses' => 100,
            'use_for' => 'order',
        ]);

        // Seed Coupon Order User
        CouponOrderUser::create([
            'coupon_id' => 1, // Assuming coupon with ID 1
            'order_id' => 1, // Assuming order with ID 1
            'user_id' => 1, // Assuming user with ID 1
        ]);

        // Seed Coupon Package User
        CouponPackageUser::create([
            'coupon_id' => 1, // Assuming coupon with ID 1
            'package_id' => 1, // Assuming package with ID 1
            'user_id' => 1, // Assuming user with ID 1
            'price_of_package_before_coupon' => 100.00,
            'price_of_package_after_coupon' => 90.00,
        ]);

        // Seed Package User
        PackageUser::create([
            'package_id' => 1, // Assuming package with ID 1
            'user_id' => 1, // Assuming user with ID 1
            'price_of_package_before_coupon' => 100.00,
            'price_of_package_after_coupon' => 90.00,
            'coupon_id' => 1, // Assuming coupon with ID 1
        ]);
    }
}
