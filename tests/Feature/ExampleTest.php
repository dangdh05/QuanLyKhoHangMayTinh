<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\StockCard;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_user_can_view_login_page(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::create([
            'username' => 'testadmin',
            'password' => 'secret123',
            'full_name' => 'Test Admin',
            'role' => 'admin',
            'status' => true,
        ]);

        $response = $this->post('/login', [
            'username' => 'testadmin',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_inbound_note_updates_stock_and_records_stock_card(): void
    {
        $user = User::create([
            'username' => 'staff1',
            'password' => 'password',
            'full_name' => 'Staff 1',
            'role' => 'staff',
            'status' => true,
        ]);

        $supplier = Supplier::create([
            'code' => 'NCC_TEST',
            'name' => 'Supplier Test',
            'status' => true,
        ]);

        $product = Product::create([
            'code' => 'MT_TEST_1',
            'name' => 'Laptop Test 1',
            'cost_price' => 10000000,
            'selling_price' => 15000000,
            'quantity' => 10,
            'min_quantity' => 5,
            'status' => true,
        ]);

        $this->actingAs($user);

        // Nhập thêm 5 máy giá 16.000.000đ
        // Giá vốn mới = (10*10M + 5*16M)/(10+5) = (100M + 80M)/15 = 180M/15 = 12.000.000đ (QT6)
        $response = $this->post(route('inbounds.store'), [
            'supplier_id' => $supplier->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'import_price' => 16000000,
                ],
            ],
            'note' => 'Nhập thử nghiệm',
        ]);

        $response->assertSessionHasNoErrors();

        $product->refresh();
        $this->assertEquals(15, $product->quantity);
        $this->assertEquals(12000000, $product->cost_price);

        // Kiểm tra thẻ kho (QT4)
        $card = StockCard::where('product_id', $product->id)->latest('id')->first();
        $this->assertNotNull($card);
        $this->assertEquals('inbound', $card->transaction_type);
        $this->assertEquals(5, $card->change_quantity);
        $this->assertEquals(15, $card->balance_quantity);
    }

    public function test_outbound_note_deducts_stock_and_prevents_overselling(): void
    {
        $user = User::create([
            'username' => 'staff2',
            'password' => 'password',
            'full_name' => 'Staff 2',
            'role' => 'staff',
            'status' => true,
        ]);

        $customer = Customer::create([
            'code' => 'KH_TEST',
            'name' => 'Customer Test',
            'status' => true,
        ]);

        $product = Product::create([
            'code' => 'MT_TEST_2',
            'name' => 'Laptop Test 2',
            'cost_price' => 10000000,
            'selling_price' => 15000000,
            'quantity' => 5,
            'min_quantity' => 2,
            'status' => true,
        ]);

        $this->actingAs($user);

        // Thử xuất 10 máy khi chỉ tồn 5 máy -> Phải báo lỗi (QT2)
        $failResponse = $this->post(route('outbounds.store'), [
            'customer_id' => $customer->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'export_price' => 15000000,
                ],
            ],
        ]);

        $failResponse->assertSessionHas('error');
        $product->refresh();
        $this->assertEquals(5, $product->quantity); // Tồn không đổi

        // Xuất 3 máy hợp lệ
        $successResponse = $this->post(route('outbounds.store'), [
            'customer_id' => $customer->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                    'export_price' => 15000000,
                ],
            ],
        ]);

        $successResponse->assertSessionHasNoErrors();
        $product->refresh();
        $this->assertEquals(2, $product->quantity); // 5 - 3 = 2 máy

        // Thẻ kho (QT4)
        $card = StockCard::where('product_id', $product->id)->latest('id')->first();
        $this->assertNotNull($card);
        $this->assertEquals('outbound', $card->transaction_type);
        $this->assertEquals(-3, $card->change_quantity);
        $this->assertEquals(2, $card->balance_quantity);
    }

    public function test_aging_overstock_detection_and_warning(): void
    {
        $user = User::create([
            'username' => 'admin_aging',
            'password' => 'password',
            'full_name' => 'Admin Test',
            'role' => 'admin',
            'status' => true,
        ]);

        // Tạo sản phẩm máy tính MacBook mở bán từ trước
        $macbook = Product::create([
            'code' => 'MACBOOK_TEST',
            'name' => 'Apple MacBook Pro 16 M3 Max',
            'brand' => 'Apple',
            'release_date' => '2025-09-15',
            'cost_price' => 65000000,
            'selling_price' => 79000000,
            'quantity' => 15,
            'min_quantity' => 5,
            'status' => true,
        ]);

        // Kiểm tra logic model
        $this->assertTrue($macbook->months_since_release >= 12);
        $this->assertTrue($macbook->isAgingOverstock(12));
        $this->assertEquals('danger', $macbook->aging_risk_level);

        $this->actingAs($user);

        // Truy cập Dashboard phải có cảnh báo hàng tồn ứ đọng quá hạn mở bán
        $dashboardResponse = $this->get(route('dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSeeText('mặt hàng mở bán > 12 tháng vẫn tồn kho');

        // Truy cập báo cáo hàng tồn lâu
        $reportResponse = $this->get(route('reports.slow-moving', ['mode' => 'aging_release', 'aging_months' => 12]));
        $reportResponse->assertStatus(200);
        $reportResponse->assertSee('MACBOOK_TEST');
    }

    public function test_product_creation_and_update_with_release_date(): void
    {
        $admin = User::create([
            'username' => 'admin_mgr',
            'password' => 'password',
            'full_name' => 'Admin Manager',
            'role' => 'admin',
            'status' => true,
        ]);

        $this->actingAs($admin);

        // Tạo mới có ngày mở bán
        $response = $this->post(route('products.store'), [
            'code' => 'TEST_NEW_PROD',
            'name' => 'Laptop Test New',
            'brand' => 'Dell',
            'release_date' => '2025-09-01',
            'selling_price' => 20000000,
            'min_quantity' => 5,
        ]);

        $response->assertSessionHasNoErrors();
        $product = Product::where('code', 'TEST_NEW_PROD')->first();
        $this->assertNotNull($product);
        $this->assertEquals('2025-09-01', $product->release_date->format('Y-m-d'));

        // Cập nhật ngày mở bán
        $updateResponse = $this->put(route('products.update', $product->id), [
            'code' => 'TEST_NEW_PROD',
            'name' => 'Laptop Test Updated',
            'brand' => 'Dell',
            'release_date' => '2025-10-15',
            'selling_price' => 21000000,
            'min_quantity' => 6,
        ]);

        $updateResponse->assertSessionHasNoErrors();
        $product->refresh();
        $this->assertEquals('2025-10-15', $product->release_date->format('Y-m-d'));
    }
}
