<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InboundService;
use App\Services\OutboundService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(InboundService $inboundService, OutboundService $outboundService): void
    {
        // 1. Tạo Users
        $admin = User::create([
            'username' => 'admin',
            'password' => Hash::make('123456'),
            'full_name' => 'Nguyễn Văn A (Admin)',
            'email' => 'admin@khohang.vn',
            'role' => 'admin',
            'status' => true,
        ]);

        $manager = User::create([
            'username' => 'quanly',
            'password' => Hash::make('123456'),
            'full_name' => 'Trần Thị B (Quản lý)',
            'email' => 'quanly@khohang.vn',
            'role' => 'manager',
            'status' => true,
        ]);

        $staff = User::create([
            'username' => 'nhanvien',
            'password' => Hash::make('123456'),
            'full_name' => 'Lê Quang C (Nhân viên)',
            'email' => 'nhanvien@khohang.vn',
            'role' => 'staff',
            'status' => true,
        ]);

        // 2. Tạo Nhà cung cấp (Suppliers)
        $sup1 = Supplier::create([
            'code' => 'NCC001',
            'name' => 'Công ty Phân Phối Digiworld (DGW)',
            'phone' => '02839255766',
            'email' => 'contact@digiworld.com.vn',
            'address' => '195 Cô Bắc, P. Cô Giang, Quận 1, TP. HCM',
            'status' => true,
        ]);

        $sup2 = Supplier::create([
            'code' => 'NCC002',
            'name' => 'Công ty TNHH Synnex FPT',
            'phone' => '02473006666',
            'email' => 'synnex@fpt.com.vn',
            'address' => 'Tòa nhà FPT, Phố Duy Tân, Cầu Giấy, Hà Nội',
            'status' => true,
        ]);

        $sup3 = Supplier::create([
            'code' => 'NCC003',
            'name' => 'Công ty TNHH Viễn Sơn',
            'phone' => '02838326085',
            'email' => 'info@microstar.com.vn',
            'address' => '162B Bùi Thị Xuân, Phường Phạm Ngũ Lão, Quận 1, TP. HCM',
            'status' => true,
        ]);

        // 3. Tạo Khách hàng (Customers)
        $cust1 = Customer::create([
            'code' => 'KH001',
            'name' => 'Công ty Cổ phần Công nghệ An Phát',
            'phone' => '0987654321',
            'address' => '49 Thái Hà, Đống Đa, Hà Nội',
            'status' => true,
        ]);

        $cust2 = Customer::create([
            'code' => 'KH002',
            'name' => 'Trường Đại học Bách Khoa',
            'phone' => '0912345678',
            'address' => '268 Lý Thường Kiệt, Quận 10, TP. HCM',
            'status' => true,
        ]);

        $cust3 = Customer::create([
            'code' => 'KH003',
            'name' => 'Trung tâm Đào tạo Lập trình ABC',
            'phone' => '0909888999',
            'address' => '123 Nguyễn Văn Cừ, Quận 5, TP. HCM',
            'status' => true,
        ]);

        // 4. Tạo Sản phẩm máy tính mẫu (Products)
        $p1 = Product::create([
            'code' => 'MT001',
            'name' => 'Dell XPS 13 Plus 9320',
            'brand' => 'Dell',
            'cpu' => 'Intel Core i7-1360P',
            'ram' => '16GB LPDDR5 6000MHz',
            'storage' => '512GB PCIe Gen 4 SSD',
            'gpu' => 'Intel Iris Xe Graphics',
            'release_date' => '2026-02-15',
            'cost_price' => 0,
            'selling_price' => 38990000,
            'quantity' => 0,
            'min_quantity' => 5,
            'status' => true,
        ]);

        $p2 = Product::create([
            'code' => 'MT002',
            'name' => 'Asus ROG Zephyrus G14 (2024)',
            'brand' => 'Asus',
            'cpu' => 'AMD Ryzen 9 8945HS',
            'ram' => '32GB LPDDR5X',
            'storage' => '1TB M.2 NVMe SSD',
            'gpu' => 'NVIDIA GeForce RTX 4070 8GB',
            'release_date' => '2026-04-10',
            'cost_price' => 0,
            'selling_price' => 52490000,
            'quantity' => 0,
            'min_quantity' => 3,
            'status' => true,
        ]);

        $p3 = Product::create([
            'code' => 'MT003',
            'name' => 'Apple MacBook Pro 14 M3 Pro',
            'brand' => 'Apple',
            'cpu' => 'Apple M3 Pro 11-core',
            'ram' => '18GB Unified Memory',
            'storage' => '512GB SSD',
            'gpu' => '14-core GPU tích hợp',
            'release_date' => '2026-01-20',
            'cost_price' => 0,
            'selling_price' => 47990000,
            'quantity' => 0,
            'min_quantity' => 4,
            'status' => true,
        ]);

        $p4 = Product::create([
            'code' => 'MT004',
            'name' => 'Lenovo ThinkPad X1 Carbon Gen 11',
            'brand' => 'Lenovo',
            'cpu' => 'Intel Core i7-1355U',
            'ram' => '16GB LPDDR5',
            'storage' => '512GB SSD PCIe',
            'gpu' => 'Intel Iris Xe',
            'release_date' => '2025-11-01',
            'cost_price' => 0,
            'selling_price' => 41500000,
            'quantity' => 0,
            'min_quantity' => 5,
            'status' => true,
        ]);

        $p5 = Product::create([
            'code' => 'MT005',
            'name' => 'PC Gaming Titan Ultra Core i9',
            'brand' => 'Titan Gaming',
            'cpu' => 'Intel Core i9-14900K',
            'ram' => '64GB DDR5 6400MHz RGB',
            'storage' => '2TB NVMe Gen4 + 4TB HDD',
            'gpu' => 'NVIDIA RTX 4090 24GB GDDR6X',
            'release_date' => '2026-05-01',
            'cost_price' => 0,
            'selling_price' => 89900000,
            'quantity' => 0,
            'min_quantity' => 2,
            'status' => true,
        ]);

        // Mặt hàng mẫu máy tính Apple MacBook Pro mở bán từ trước
        $p6 = Product::create([
            'code' => 'MACBOOK-PRO-16',
            'name' => 'Apple MacBook Pro 16 M3 Max 36GB 1TB Space Black',
            'brand' => 'Apple',
            'cpu' => 'Apple M3 Max (14-core CPU)',
            'ram' => '36GB Unified Memory',
            'storage' => '1TB SSD NVMe',
            'gpu' => '30-core GPU Ray-Tracing',
            'release_date' => '2025-09-15',
            'cost_price' => 0,
            'selling_price' => 79990000,
            'quantity' => 0,
            'min_quantity' => 5,
            'status' => true,
        ]);

        // 5. Thực hiện Phiếu Nhập đầu tiên qua InboundService (tính giá vốn bình quân, ghi thẻ kho chuẩn QT1, QT3, QT4, QT6)
        $inboundService->taoPhieuNhap(
            supplierId: $sup1->id,
            userId: $staff->id,
            items: [
                ['product_id' => $p1->id, 'quantity' => 15, 'import_price' => 32000000],
                ['product_id' => $p2->id, 'quantity' => 8, 'import_price' => 44000000],
                ['product_id' => $p3->id, 'quantity' => 12, 'import_price' => 41000000],
                ['product_id' => $p6->id, 'quantity' => 15, 'import_price' => 28500000],
            ],
            note: 'Nhập lô hàng laptop và MacBook từ Digiworld',
            createdDate: now()->subDays(10)->format('Y-m-d H:i:s')
        );

        $inboundService->taoPhieuNhap(
            supplierId: $sup2->id,
            userId: $manager->id,
            items: [
                ['product_id' => $p4->id, 'quantity' => 10, 'import_price' => 35000000],
                ['product_id' => $p5->id, 'quantity' => 5, 'import_price' => 76000000],
            ],
            note: 'Nhập lô Thinkpad và PC Gaming từ Synnex FPT',
            createdDate: now()->subDays(6)->format('Y-m-d H:i:s')
        );

        // 6. Thực hiện một phiếu nhập bổ sung để kiểm chứng công thức giá vốn bình quân gia quyền
        $inboundService->taoPhieuNhap(
            supplierId: $sup1->id,
            userId: $staff->id,
            items: [
                // MT001 đang có 15 chiếc giá 32.000.000, nhập thêm 5 chiếc giá 34.000.000
                // Giá vốn mới = (15*32M + 5*34M)/(20) = (480M + 170M)/20 = 650M/20 = 32.500.000đ
                ['product_id' => $p1->id, 'quantity' => 5, 'import_price' => 34000000],
            ],
            note: 'Nhập bổ sung 5 máy Dell XPS theo biến động giá thị trường',
            createdDate: now()->subDays(4)->format('Y-m-d H:i:s')
        );

        // 7. Thực hiện Phiếu Xuất mẫu qua OutboundService (trừ kho, ghi thẻ kho chuẩn QT2, QT3, QT4)
        $outboundService->taoPhieuXuat(
            customerId: $cust1->id,
            userId: $staff->id,
            items: [
                ['product_id' => $p1->id, 'quantity' => 3, 'export_price' => 38500000],
                ['product_id' => $p2->id, 'quantity' => 2, 'export_price' => 52000000],
            ],
            note: 'Xuất hàng giao cho đại lý An Phát',
            createdDate: now()->subDays(2)->format('Y-m-d H:i:s')
        );

        $outboundService->taoPhieuXuat(
            customerId: $cust2->id,
            userId: $staff->id,
            items: [
                ['product_id' => $p3->id, 'quantity' => 4, 'export_price' => 47500000],
                ['product_id' => $p4->id, 'quantity' => 2, 'export_price' => 41000000],
            ],
            note: 'Xuất cấp thiết bị cho phòng lab Đại học Bách Khoa',
            createdDate: now()->subDays(1)->format('Y-m-d H:i:s')
        );

        $outboundService->taoPhieuXuat(
            customerId: $cust3->id,
            userId: $staff->id,
            items: [
                ['product_id' => $p6->id, 'quantity' => 2, 'export_price' => 34500000],
            ],
            note: 'Xuất 2 máy MacBook Pro 16 (Còn tồn 13 máy đọng vốn mở bán từ 09/2025)',
            createdDate: now()->subDays(60)->format('Y-m-d H:i:s')
        );
    }
}
