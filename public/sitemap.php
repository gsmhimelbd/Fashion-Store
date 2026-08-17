<?php
header("Content-Type: application/xml; charset=utf-8");
require_once __DIR__ . '/config/database.php';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'onlinebdmart.com';
$baseUrl = $protocol . $host;

try {
    $db = getDB();
    $products = $db->query("SELECT slug, updated_at, created_at FROM products WHERE is_active = 1 ORDER BY id DESC")->fetchAll();
    $categories = $db->query("SELECT slug, updated_at, created_at FROM categories WHERE is_active = 1")->fetchAll();
    $blogs = $db->query("SELECT slug, created_at FROM blog_posts WHERE is_published = 1")->fetchAll();
} catch (Exception $e) {
    $products = [];
    $categories = [];
    $blogs = [];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Static Core Pages -->
    <url>
        <loc><?= $baseUrl ?>/</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?= $baseUrl ?>/shop.php</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc><?= $baseUrl ?>/categories.php</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?= $baseUrl ?>/deals.php</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc><?= $baseUrl ?>/wholesale.php</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?= $baseUrl ?>/blog.php</loc>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    <url>
        <loc><?= $baseUrl ?>/contact.php</loc>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>

    <!-- Dynamic Category Pages -->
    <?php foreach ($categories as $c): 
        $cDate = !empty($c['updated_at']) ? date('Y-m-d', strtotime($c['updated_at'])) : date('Y-m-d');
    ?>
    <url>
        <loc><?= $baseUrl ?>/shop.php?category=<?= urlencode($c['slug']) ?></loc>
        <lastmod><?= $cDate ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; ?>

    <!-- Dynamic Product Pages -->
    <?php foreach ($products as $p): 
        $pDate = !empty($p['updated_at']) ? date('Y-m-d', strtotime($p['updated_at'])) : (!empty($p['created_at']) ? date('Y-m-d', strtotime($p['created_at'])) : date('Y-m-d'));
    ?>
    <url>
        <loc><?= $baseUrl ?>/product.php?slug=<?= urlencode($p['slug']) ?></loc>
        <lastmod><?= $pDate ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <?php endforeach; ?>

    <!-- Dynamic Blog Posts -->
    <?php foreach ($blogs as $b): 
        $bDate = !empty($b['created_at']) ? date('Y-m-d', strtotime($b['created_at'])) : date('Y-m-d');
    ?>
    <url>
        <loc><?= $baseUrl ?>/blog.php?slug=<?= urlencode($b['slug']) ?></loc>
        <lastmod><?= $bDate ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
    <?php endforeach; ?>
</urlset>
