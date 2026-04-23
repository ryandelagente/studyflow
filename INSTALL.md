# StudyFlow — Installation Guide

Step-by-step instructions for a clean install on **Ubuntu 22.04 LTS** and **Windows 10/11 with XAMPP**.

---

## Ubuntu 22.04 LTS

### 1. Install Apache, PHP, and MariaDB

```bash
sudo apt update && sudo apt upgrade -y

# Apache
sudo apt install -y apache2

# PHP 8.1 and required extensions
sudo apt install -y php8.1 php8.1-fpm php8.1-mysqli php8.1-curl \
    php8.1-json php8.1-mbstring php8.1-xml php8.1-openssl libapache2-mod-php8.1

# MariaDB
sudo apt install -y mariadb-server mariadb-client

# Enable and start services
sudo systemctl enable --now apache2 mariadb
```

### 2. Secure MariaDB and create the database

```bash
sudo mysql_secure_installation   # follow prompts; set a root password

sudo mysql -u root -p <<'SQL'
CREATE DATABASE productivity_hub
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
CREATE USER 'studyflow'@'localhost' IDENTIFIED BY 'change_me_please';
GRANT ALL PRIVILEGES ON productivity_hub.* TO 'studyflow'@'localhost';
FLUSH PRIVILEGES;
SQL
```

### 3. Clone the repository

```bash
sudo apt install -y git
cd /var/www/html
sudo git clone https://github.com/ryandelagente/studyflow.git
sudo chown -R www-data:www-data studyflow
sudo chmod -R 755 studyflow
```

### 4. Import the schema

```bash
mysql -u studyflow -p productivity_hub < /var/www/html/studyflow/sql/productivity_hub.sql
```

### 5. Configure secrets and database credentials

```bash
cd /var/www/html/studyflow
sudo cp secrets.example.php secrets.php
sudo nano secrets.php          # set OPENROUTER_API_KEY
sudo nano config.php           # set DB_USERNAME, DB_PASSWORD
```

### 6. Configure Apache

```bash
sudo nano /etc/apache2/sites-available/studyflow.conf
```

Paste:

```apache
<VirtualHost *:80>
    ServerName studyflow.local
    DocumentRoot /var/www/html/studyflow

    <Directory /var/www/html/studyflow>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/studyflow_error.log
    CustomLog ${APACHE_LOG_DIR}/studyflow_access.log combined
</VirtualHost>
```

```bash
sudo a2ensite studyflow.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
```

### 7. First run

Open `http://studyflow.local/register.php` in your browser, create your account, then log in at `http://studyflow.local/login.php`.

---

## Windows 10/11 with XAMPP

### 1. Install XAMPP

Download XAMPP 8.1+ from <https://www.apachefriends.org> and run the installer. Accept the defaults. Install to `C:\xampp`.

Start **Apache** and **MySQL** from the XAMPP Control Panel.

### 2. Clone the repository

Open **Git Bash** (install from <https://git-scm.com> if needed):

```bash
cd /c/xampp/htdocs
git clone https://github.com/ryandelagente/studyflow.git
```

Or download the ZIP from GitHub and extract it to `C:\xampp\htdocs\studyflow`.

### 3. Create the database and import the schema

Open your browser and navigate to `http://localhost/phpmyadmin`.

1. Click **New** in the left panel.
2. Enter `productivity_hub` as the database name, select `utf8mb4_unicode_ci`, click **Create**.
3. Click the new `productivity_hub` database, then click the **Import** tab.
4. Choose `C:\xampp\htdocs\studyflow\sql\productivity_hub.sql` and click **Go**.

Or use the XAMPP shell:

```bash
"C:\xampp\mysql\bin\mysql.exe" -u root productivity_hub < C:\xampp\htdocs\studyflow\sql\productivity_hub.sql
```

### 4. Configure secrets and database credentials

```bash
cd /c/xampp/htdocs/studyflow
cp secrets.example.php secrets.php
```

Open `secrets.php` in a text editor and set:

```php
define('OPENROUTER_API_KEY', 'sk-or-v1-your-key-here');
```

Open `config.php` and verify the `DB_*` constants match your XAMPP setup (XAMPP defaults: host `localhost`, user `root`, password `""`).

### 5. First run

Open `http://localhost/studyflow/register.php`, create your account, and log in at `http://localhost/studyflow/login.php`.

---

## PHP Extension Checklist

Verify the required extensions are enabled in `php.ini` (`C:\xampp\php\php.ini` on Windows, `/etc/php/8.1/apache2/php.ini` on Ubuntu):

```
extension=mysqli
extension=curl
extension=json
extension=openssl
```

After any `php.ini` change, restart Apache.

---

## Troubleshooting

| Symptom | Likely cause | Fix |
|---------|-------------|-----|
| Blank page | PHP error suppressed | Check `error_log` or enable `display_errors = On` in `php.ini` |
| "OpenRouter API key not configured" | `secrets.php` missing or empty | Copy `secrets.example.php` to `secrets.php` and add key |
| Database connection failed | Wrong credentials | Re-check `DB_*` constants in `config.php` |
| 403 Forbidden (Apache) | `AllowOverride None` | Set `AllowOverride All` in the VirtualHost or `httpd.conf` |
| cURL SSL error | System CA bundle missing | Install `ca-certificates` (Ubuntu) or update XAMPP |
