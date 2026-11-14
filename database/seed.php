<?php
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Config/Database.php';

try {
    $db = (new Database())->getConnection();

    echo "=== SEEDING DATABASE WITH REALISTIC DATA ===\n";

    // 1. Seed Admin User
    $adminEmail = 'admin@homecare.com';
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$adminEmail]);
    $adminId = $stmt->fetchColumn();

    if (!$adminId) {
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role, phone, address) VALUES (?, ?, ?, 'ADMIN', ?, ?)");
        $stmt->execute([
            'Administrator HomeCare',
            $adminEmail,
            password_hash('admin123', PASSWORD_DEFAULT),
            '081122334455',
            'Kantor Pusat HomeCare Service, Jl. Sudirman No. 88, Jakarta Pusat'
        ]);
        $adminId = $db->lastInsertId();
        echo "[+] Admin created: admin@homecare.com / admin123 (ID: $adminId)\n";
    }

    // 2. Seed Technicians & Users
    $techniciansData = [
        [
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@homecare.com',
            'phone' => '081234567801',
            'address' => 'Jl. Kebon Jeruk No. 12, Jakarta Barat',
            'specialization' => 'Air Conditioner (AC) & Sistem Pendingin',
            'experience' => 6,
            'availability' => 1,
            'rating' => 4.90
        ],
        [
            'name' => 'Joko Susilo',
            'email' => 'joko.susilo@homecare.com',
            'phone' => '081234567802',
            'address' => 'Jl. Tebet Raya No. 45, Jakarta Selatan',
            'specialization' => 'Kulkas, Freezer & Lemari Es',
            'experience' => 5,
            'availability' => 1,
            'rating' => 4.85
        ],
        [
            'name' => 'Hendra Wijaya',
            'email' => 'hendra.wijaya@homecare.com',
            'phone' => '081234567803',
            'address' => 'Jl. Cempaka Putih No. 19, Jakarta Pusat',
            'specialization' => 'Mesin Cuci (Front/Top Load) & Pengering',
            'experience' => 4,
            'availability' => 1,
            'rating' => 4.80
        ],
        [
            'name' => 'Rian Pratama',
            'email' => 'rian.pratama@homecare.com',
            'phone' => '081234567804',
            'address' => 'Jl. Rawamangun No. 7, Jakarta Timur',
            'specialization' => 'Televisi (Smart TV/LED), Audio & Elektronik Rumah',
            'experience' => 5,
            'availability' => 0,
            'rating' => 4.75
        ],
        [
            'name' => 'Agus Kurniawan',
            'email' => 'agus.kurniawan@homecare.com',
            'phone' => '081234567805',
            'address' => 'Jl. Pluit Kencana No. 23, Jakarta Utara',
            'specialization' => 'Pompa Air, Water Heater, Microwave & Kompor Gas/Induksi',
            'experience' => 7,
            'availability' => 1,
            'rating' => 4.95
        ]
    ];

    $techMap = [];
    foreach ($techniciansData as $t) {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$t['email']]);
        $uId = $stmt->fetchColumn();

        if (!$uId) {
            $stmt = $db->prepare("INSERT INTO users (name, email, password, role, phone, address) VALUES (?, ?, ?, 'TEKNISI', ?, ?)");
            $stmt->execute([
                $t['name'],
                $t['email'],
                password_hash('teknisi123', PASSWORD_DEFAULT),
                $t['phone'],
                $t['address']
            ]);
            $uId = $db->lastInsertId();
        }

        $stmt = $db->prepare("SELECT id FROM technicians WHERE user_id = ?");
        $stmt->execute([$uId]);
        $tId = $stmt->fetchColumn();

        if (!$tId) {
            $stmt = $db->prepare("INSERT INTO technicians (user_id, specialization, experience, availability, rating) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $uId,
                $t['specialization'],
                $t['experience'],
                $t['availability'],
                $t['rating']
            ]);
            $tId = $db->lastInsertId();
            echo "[+] Technician created: {$t['name']} ({$t['specialization']}) - ID: $tId\n";
        }
        $techMap[$t['name']] = $tId;
    }

    // 3. Seed Equipment Catalog
    $equipmentList = [
        // AC
        ['name' => 'Air Conditioner (AC) Split Inverter', 'brand' => 'Daikin', 'category' => 'Pendingin Udara', 'description' => 'AC Split Wall Inverter hemat listrik dan hembusan dingin presisi.'],
        ['name' => 'Air Conditioner (AC) Standard', 'brand' => 'Panasonic', 'category' => 'Pendingin Udara', 'description' => 'AC Panasonic dengan teknologi nanoe-G pemurni udara.'],
        ['name' => 'Air Conditioner (AC) Dual Cool', 'brand' => 'LG', 'category' => 'Pendingin Udara', 'description' => 'AC inverter LG dengan pendinginan cepat dan hemat energi.'],
        ['name' => 'Air Conditioner (AC) Plasmacluster', 'brand' => 'Sharp', 'category' => 'Pendingin Udara', 'description' => 'AC Sharp dengan fitur ion Plasmacluster pencegah jamur dan bakteri.'],
        
        // Kulkas
        ['name' => 'Kulkas 2 Pintu No Frost', 'brand' => 'Samsung', 'category' => 'Lemari Es & Pendingin', 'description' => 'Kulkas dua pintu bebas bunga es dengan teknologi All-around Cooling.'],
        ['name' => 'Kulkas Side by Side Smart Inverter', 'brand' => 'LG', 'category' => 'Lemari Es & Pendingin', 'description' => 'Kulkas kapasitas besar multi-door dengan kompresor Smart Inverter.'],
        ['name' => 'Kulkas 1 Pintu Belleza', 'brand' => 'Polytron', 'category' => 'Lemari Es & Pendingin', 'description' => 'Kulkas satu pintu dengan tempered glass door elegan dan pendinginan merata.'],
        ['name' => 'Kulkas 2 Pintu J-Tech Inverter', 'brand' => 'Sharp', 'category' => 'Lemari Es & Pendingin', 'description' => 'Kulkas hemat energi dengan kontrol temperatur presisi.'],
        
        // Mesin Cuci
        ['name' => 'Mesin Cuci Front Loading AI DD', 'brand' => 'LG', 'category' => 'Mesin Cuci & Pengering', 'description' => 'Mesin cuci bukaan depan dengan motor penggerak langsung AI DD.'],
        ['name' => 'Mesin Cuci Top Loading QuickDrive', 'brand' => 'Samsung', 'category' => 'Mesin Cuci & Pengering', 'description' => 'Mesin cuci bukaan atas dengan tabung stainless dan pencucian cepat.'],
        ['name' => 'Mesin Cuci UltimateCare 300', 'brand' => 'Electrolux', 'category' => 'Mesin Cuci & Pengering', 'description' => 'Mesin cuci bukaan depan dengan fitur perawatan serat kain maksimal.'],
        ['name' => 'Mesin Cuci 2 Tabung Super Aquamagic', 'brand' => 'Sharp', 'category' => 'Mesin Cuci & Pengering', 'description' => 'Mesin cuci dua tabung dengan daya putar kuat dan hemat air.'],
        
        // Televisi
        ['name' => 'Smart LED TV 4K Crystal UHD', 'brand' => 'Samsung', 'category' => 'Televisi & Hiburan', 'description' => 'Smart TV 4K Ultra HD dengan prosesor Crystal 4K.'],
        ['name' => 'Smart OLED TV 4K Cinema', 'brand' => 'LG', 'category' => 'Televisi & Hiburan', 'description' => 'TV OLED dengan kontras sempurna dan refresh rate tinggi.'],
        ['name' => 'Bravia 4K Google TV', 'brand' => 'Sony', 'category' => 'Televisi & Hiburan', 'description' => 'TV premium dengan prosesor Cognitive XR dan sistem audio Dolby Atmos.'],
        
        // Peralatan Dapur & Pompa
        ['name' => 'Pompa Air Otomatis Sumur Dangkal', 'brand' => 'Shimizu', 'category' => 'Pompa & Sanitasi', 'description' => 'Pompa air otomatis dengan daya hisap kuat dan tahan karat.'],
        ['name' => 'Water Heater Listrik Storage', 'brand' => 'Ariston', 'category' => 'Water Heater & Pemanas', 'description' => 'Pemanas air mandi dengan lapisan titanium enameled dan proteksi ELCB.'],
        ['name' => 'Microwave Grill Digital', 'brand' => 'Sharp', 'category' => 'Peralatan Dapur & Memasak', 'description' => 'Microwave pemanggang dengan menu otomatis dan sensor defrost.'],
        ['name' => 'Dispenser Galon Bawah Hot & Cold', 'brand' => 'Miyako', 'category' => 'Peralatan Dapur & Minum', 'description' => 'Dispenser air dengan tangki stainless steel dan fitur safety child lock.'],
        ['name' => 'Kompor Gas 2 Tungku Kaca', 'brand' => 'Rinnai', 'category' => 'Peralatan Dapur & Memasak', 'description' => 'Kompor gas tempered glass tahan panas dengan api turbo.'],
        ['name' => 'Digital Rice Cooker / Magic Com', 'brand' => 'Philips', 'category' => 'Peralatan Dapur & Memasak', 'description' => 'Pemasak nasi digital dengan panci ProCeramic+ 5 lapis anti lengket.']
    ];

    $equipMap = [];
    foreach ($equipmentList as $eq) {
        $stmt = $db->prepare("SELECT id FROM equipment WHERE LOWER(name) = LOWER(?) AND LOWER(brand) = LOWER(?)");
        $stmt->execute([$eq['name'], $eq['brand']]);
        $eqId = $stmt->fetchColumn();

        if (!$eqId) {
            $stmt = $db->prepare("INSERT INTO equipment (name, brand, category, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$eq['name'], $eq['brand'], $eq['category'], $eq['description']]);
            $eqId = $db->lastInsertId();
            echo "[+] Equipment created: {$eq['name']} ({$eq['brand']}) - ID: $eqId\n";
        }
        $equipMap[$eq['brand'] . ' ' . $eq['name']] = $eqId;
    }

    // 4. Seed Sample Customer Orders for demo users
    $stmt = $db->query("SELECT id, name, email FROM users WHERE role = 'CUSTOMER'");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($customers)) {
        $firstCust = $customers[0];
        $custId = $firstCust['id'];

        // Check if orders already exist
        $stmt = $db->prepare("SELECT COUNT(*) FROM service_orders WHERE customer_id = ?");
        $stmt->execute([$custId]);
        $orderCount = $stmt->fetchColumn();

        if ($orderCount == 0) {
            $daikinId = $db->query("SELECT id FROM equipment WHERE brand = 'Daikin' LIMIT 1")->fetchColumn();
            $samsungFridgeId = $db->query("SELECT id FROM equipment WHERE brand = 'Samsung' AND category LIKE '%Lemari%' LIMIT 1")->fetchColumn();
            $lgWasherId = $db->query("SELECT id FROM equipment WHERE brand = 'LG' AND category LIKE '%Mesin Cuci%' LIMIT 1")->fetchColumn();

            $techBudiId = $techMap['Budi Santoso'] ?? 1;
            $techJokoId = $techMap['Joko Susilo'] ?? 2;
            $techHendraId = $techMap['Hendra Wijaya'] ?? 3;

            // Order 1: Completed Order (AC Servis)
            $stmt = $db->prepare("
                INSERT INTO service_orders (customer_id, technician_id, equipment_id, complaint, ai_diagnosis, status, service_date, address, estimated_cost, final_cost, created_at)
                VALUES (?, ?, ?, ?, ?, 'COMPLETED', DATE_SUB(NOW(), INTERVAL 3 DAY), 'Jl. Kemang Raya No. 15, Jakarta Selatan', 150000, 150000, DATE_SUB(NOW(), INTERVAL 4 DAY))
            ");
            $stmt->execute([
                $custId,
                $techBudiId,
                $daikinId,
                'AC bocor air di indoor dan kurang dingin',
                'Analisis AI menunjukkan pipa drainase tersumbat debu/lumut dan filter kotor.'
            ]);
            $order1Id = $db->lastInsertId();

            // Service report for Order 1
            $stmt = $db->prepare("
                INSERT INTO service_reports (order_id, technician_id, inspection_result, repair_action, notes, cost, completed_at)
                VALUES (?, ?, ?, ?, ?, 150000, DATE_SUB(NOW(), INTERVAL 3 DAY))
            ");
            $stmt->execute([
                $order1Id,
                $techBudiId,
                'Filter indoor penuh debu tebal dan saluran pipa drainase tersumbat lendir lumut. Freon masih normal (80 PSI).',
                'Pembersihan filter udara, steam cuci koil indoor dan outdoor, serta flushing saluran pembuangan air.',
                'AC sudah berfungsi dingin maksimal dan tidak ada tetesan air lagi. Disarankan servis berkala 3 bulan sekali.'
            ]);

            // Review for Order 1
            $stmt = $db->prepare("
                INSERT INTO reviews (order_id, customer_id, technician_id, rating, comment, created_at)
                VALUES (?, ?, ?, 5, 'Pelayanan teknisi Pak Budi sangat memuaskan, ramah, dan cepat selesai. AC langsung dingin normal kembali!', DATE_SUB(NOW(), INTERVAL 3 DAY))
            ");
            $stmt->execute([$order1Id, $custId, $techBudiId]);

            // Order 2: In Progress Order (Kulkas Samsung)
            $stmt = $db->prepare("
                INSERT INTO service_orders (customer_id, technician_id, equipment_id, complaint, ai_diagnosis, status, service_date, address, estimated_cost, final_cost, created_at)
                VALUES (?, ?, ?, ?, ?, 'IN_PROGRESS', DATE_ADD(NOW(), INTERVAL 2 HOUR), 'Jl. Kemang Raya No. 15, Jakarta Selatan', 250000, NULL, DATE_SUB(NOW(), INTERVAL 1 DAY))
            ");
            $stmt->execute([
                $custId,
                $techJokoId,
                $samsungFridgeId,
                'Kulkas bagian bawah tidak dingin, tapi freezer atas membeku normal',
                'Analisis AI mengindikasikan gangguan pada sistem defrost otomatis atau kipas sirkulasi chiller.'
            ]);
            $order2Id = $db->lastInsertId();

            // Order 3: Pending Order (Mesin Cuci LG)
            $stmt = $db->prepare("
                INSERT INTO service_orders (customer_id, technician_id, equipment_id, complaint, ai_diagnosis, status, service_date, address, estimated_cost, final_cost, created_at)
                VALUES (?, NULL, ?, ?, ?, 'PENDING', DATE_ADD(NOW(), INTERVAL 1 DAY), 'Jl. Kemang Raya No. 15, Jakarta Selatan', 200000, NULL, NOW())
            ");
            $stmt->execute([
                $custId,
                $lgWasherId,
                'Mesin cuci mengeluarkan bunyi berdecit keras saat proses spin/pengeringan',
                'Analisis AI menduga tali fanbelt aus atau ada bantalan bearing tabung yang perlu pelumasan.'
            ]);
            $order3Id = $db->lastInsertId();

            echo "[+] Created 3 realistic sample orders for Customer ID {$custId} (Completed #$order1Id, In Progress #$order2Id, Pending #$order3Id)\n";
        }
    }

    echo "=== DATABASE SEEDING COMPLETED SUCCESSFULLY! ===\n";

} catch (Exception $e) {
    echo "ERROR SEEDING: " . $e->getMessage() . "\n";
}
