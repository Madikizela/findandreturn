<?php
include('connection.php');

// Sample products for different schools
$sample_products = [
    [
        'name' => 'School Uniform - Boys',
        'code' => 'UNIFORM001',
        'price' => 250.00,
        'quantity' => 50,
        'size' => 'M',
        'school' => 'Kloof High School',
        'school_admin_email' => 'admin@kloofhigh.co.za',
        'image' => 'product-images/uniform.jpg'
    ],
    [
        'name' => 'School Uniform - Girls',
        'code' => 'UNIFORM002',
        'price' => 280.00,
        'quantity' => 45,
        'size' => 'M',
        'school' => 'Kloof High School',
        'school_admin_email' => 'admin@kloofhigh.co.za',
        'image' => 'product-images/uniform.jpg'
    ],
    [
        'name' => 'School Bag',
        'code' => 'BAG001',
        'price' => 150.00,
        'quantity' => 30,
        'size' => 'Standard',
        'school' => 'Kloof High School',
        'school_admin_email' => 'admin@kloofhigh.co.za',
        'image' => 'product-images/bag.jpg'
    ],
    [
        'name' => 'Mathematics Textbook',
        'code' => 'MATH001',
        'price' => 120.00,
        'quantity' => 25,
        'size' => 'A4',
        'school' => 'Kloof High School',
        'school_admin_email' => 'admin@kloofhigh.co.za',
        'image' => 'product-images/book.jpg'
    ],
    [
        'name' => 'Primary School Uniform',
        'code' => 'PRIMARY001',
        'price' => 200.00,
        'quantity' => 40,
        'size' => 'S',
        'school' => 'Kloof Junior Primary School',
        'school_admin_email' => 'admin@kloofjunior.co.za',
        'image' => 'product-images/uniform.jpg'
    ],
    [
        'name' => 'Art Supplies Kit',
        'code' => 'ART001',
        'price' => 80.00,
        'quantity' => 35,
        'size' => 'Standard',
        'school' => 'Kloof Junior Primary School',
        'school_admin_email' => 'admin@kloofjunior.co.za',
        'image' => 'product-images/art.jpg'
    ],
    [
        'name' => 'Senior School Uniform',
        'code' => 'SENIOR001',
        'price' => 220.00,
        'quantity' => 30,
        'size' => 'L',
        'school' => 'Kloof Senior Primary School',
        'school_admin_email' => 'admin@kloofsenior.co.za',
        'image' => 'product-images/uniform.jpg'
    ],
    [
        'name' => 'Science Lab Kit',
        'code' => 'SCIENCE001',
        'price' => 180.00,
        'quantity' => 20,
        'size' => 'Standard',
        'school' => 'Kloof Senior Primary School',
        'school_admin_email' => 'admin@kloofsenior.co.za',
        'image' => 'product-images/science.jpg'
    ],
    [
        'name' => 'College Uniform',
        'code' => 'COLLEGE001',
        'price' => 300.00,
        'quantity' => 25,
        'size' => 'M',
        'school' => 'Thomas More College',
        'school_admin_email' => 'admin@thomasmore.co.za',
        'image' => 'product-images/uniform.jpg'
    ],
    [
        'name' => 'Sports Kit',
        'code' => 'SPORTS001',
        'price' => 120.00,
        'quantity' => 30,
        'size' => 'M',
        'school' => 'Thomas More College',
        'school_admin_email' => 'admin@thomasmore.co.za',
        'image' => 'product-images/sports.jpg'
    ]
];

echo "<h2>Adding Sample Products</h2>";

$success_count = 0;
$error_count = 0;

foreach ($sample_products as $product) {
    // Check if product already exists
    $check_sql = "SELECT id FROM tblproduct WHERE code = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $product['code']);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo "<p style='color: orange;'>⚠️ Product with code {$product['code']} already exists. Skipping...</p>";
        continue;
    }
    
    // Insert new product
    $sql = "INSERT INTO tblproduct (name, code, image, price, quantity, size, school_admin_email, school) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdisss", 
        $product['name'], 
        $product['code'], 
        $product['image'], 
        $product['price'], 
        $product['quantity'], 
        $product['size'], 
        $product['school_admin_email'], 
        $product['school']
    );
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✅ Added: {$product['name']} for {$product['school']}</p>";
        $success_count++;
    } else {
        echo "<p style='color: red;'>❌ Error adding {$product['name']}: " . $stmt->error . "</p>";
        $error_count++;
    }
    
    $stmt->close();
}

echo "<hr>";
echo "<h3>Summary:</h3>";
echo "<p><strong>Successfully added:</strong> $success_count products</p>";
echo "<p><strong>Errors:</strong> $error_count products</p>";

if ($success_count > 0) {
    echo "<div style='background-color: #d4edda; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
    echo "<h4>🎉 Sample products added successfully!</h4>";
    echo "<p>You can now test the marketplace by:</p>";
    echo "<ol>";
    echo "<li>Going to <a href=''>School Page</a></li>";
    echo "<li>Selecting a school</li>";
    echo "<li>Clicking on 'Marketplace'</li>";
    echo "<li>You should see products specific to that school</li>";
    echo "</ol>";
    echo "</div>";
}

echo "<p><a href='' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to School Page</a></p>";
?> 
