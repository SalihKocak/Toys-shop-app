# Laragon'da MongoDB PHP Eklentisi

## 1) Önce paketleri kur (MongoDB eklentisi olmadan)

Laragon Terminal'de (proje klasöründe):

```cmd
composer install --ignore-platform-req=ext-mongodb
```

Böylece `vendor` klasörü oluşur.

---

## 2) MongoDB PHP eklentisini ekle

### Yol A – Laragon menüsü (varsa)

- Laragon’a sağ tık → **PHP** → **Quick add** veya **Extensions**
- Listede **MongoDB** varsa ekle / işaretle

### Yol B – Elle DLL ekleme (Laragon PHP 8.3)

1. **DLL’i indir (PHP 8.3 Thread Safe x64):**  
   Şu linke tıkla, ZIP inecek:  
   **https://downloads.php.net/~windows/pecl/releases/mongodb/2.0.0/php_mongodb-2.0.0-8.3-ts-vs16-x64.zip**

2. **ZIP’i aç**, içinden **php_mongodb.dll** dosyasını bul (genelde bir alt klasörde).

3. **DLL’i Laragon PHP ext klasörüne kopyala:**  
   Klasör: `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\ext\`  
   Sadece **php_mongodb.dll** dosyasını bu klasöre yapıştır.

4. **php.ini’yi düzenle:**  
   Dosyayı aç: `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.ini`  
   En sona şu satırı ekle (Not Defteri ile açıp kaydet):
   ```ini
   extension=mongodb
   ```

5. **Laragon’u yeniden başlat:** Laragon’da **Stop** → **Start**.  
   Sonra terminalde kontrol et: `php -m | findstr mongodb` → `mongodb` yazmalı.

---

## 3) Kontrol ve projeyi çalıştırma

Laragon Terminal’de:

```cmd
cd C:\Users\sfkoc\Desktop\ToyShop_App
php -m | findstr mongodb
```

`mongodb` satırı görünmeli.

Sonra:

```cmd
php scripts/seed.php
php -S localhost:8000 -t public
```

Tarayıcıda: http://localhost:8000
