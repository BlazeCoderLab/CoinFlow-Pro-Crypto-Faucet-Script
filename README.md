# CoinFlow Pro – Crypto Faucet Script

A fast, responsive, and multi-currency cryptocurrency faucet script designed for ease of use and optimal performance.

**Version: v2.0.2**

---

## 🚀 Key Features

- Multi-cryptocurrency support  
- Integrated FaucetPay wallet for seamless payments  
- Built-in short-link monetization system  
- Integrated reCaptcha & Turnstile Captcha to prevent spam  
- Mobile-friendly, modern user interface and design  
- Secure and user-friendly admin panel  
- Developed with PHP 7.4+ and MySQLi for reliability  
- More features coming soon—driven by user feedback and ongoing development  

---

## 🧾 Minimum Requirements

- PHP version 7.4 or higher (tested on 7.4 & 8.1)  
- MySQLi extension enabled  
- cURL extension enabled

---

## ⚙️ Installation Instructions

1. Upload all files to your web server  
2. Configure your settings in `/libs/config.php`  
3. Run the installer by navigating to: `https://yourdomain.com/install`  
4. Access the Admin Panel at: `https://yourdomain.com/admin`  
   - **Default Admin Credentials**  
     - Username: `admin`  
     - Password: `admin1234`  

---

## 🎫 Need Assistance?

For bug reports, feature requests, or general questions, please open an issue within the repository.

---

## 💰 Support Development / Donations

If you find CoinFlow Pro valuable and want to support ongoing improvements, please consider contributing. Your support helps sustain development and adds new features.

**Donate via:**

- FaucetPay username: `coinflash` (accepts any coin)  
- Cwallet ID: `34484015` (accepts any coin)  
- Direct Cryptocurrency Donations:  
  - Litecoin (LTC): `ltc1qgh7dllc9vr07qxurccy4dhr4t6seleqc42rf6n`  
  - Tron (TRX): `TD8ghNfzUf6o5FJWvJaRBQeFqjECRXcAHP`  
  - Dogecoin (DOGE): `DFh1YvhoxCPgqDgPKn4ok1waqVHW5Vs4qz`  
  - Pepe (PEPE): `0x22B33d5d8129B12926099bd32a36056613f79844`  

---

## 🛠 Changelog

```
Version 2.0.2 - (1 October 2025)
- Fixed: Backend user logging issue for ip check
- Enhanced: Admin data handling
- Updated: New theme for admin login
- Minor bug fixes and performance improvements  

Version 2.0.1 - (21 September 2025)
- Fixed referral commission calculation issue
- Updated some Core Functions to work more efficiently and properly
- Added server level protection for files and efficient error page handling
- Minor bug fixes and performance improvements  

Version 2.0.0 - (16 September 2025)
- Added Turnstile Captcha provider
- Enhanced admin panel security  
- Rewritten core functions for improved experience  
- Added option to change admin credentials from admin panel  
- Introduced live payouts feature  
- Implemented advanced data masking  
- Updated frontend and admin UI  
- Optimized database and backend logic for efficiency and speed  
- Minor bug fixes and performance improvements  

Version 1.1.1 - (12 September 2025)
- Fixed auto domain fetching issue  
- Added manual domain input in ./libs/config.php  
- Admin panel moved to a dedicated folder  
- Minor bug fixes and improvements  

Version 1.1.0 - (11 September 2025)
- Removed unused admin panel fields for performance  
- Deprecated SolveMedia & Bit Captcha providers removed  
- Cleaned database of unused entries for optimization  
- Updated database engine and charset  
- Improved website UI  
- Code optimizations  
- Minor fixes  

Version 1.0.0 - (10 September 2025)
- Updated FaucetHub to FaucetPay library  
- Initial release  
```