# PHP php.ini Oluşturma (Windows)

PHP "Loaded Configuration File: (none)" diyorsa php.ini yok demektir. Şu adımlarla oluşturun.

## 1) PHP klasörünü bulun

CMD'de çalıştırın:
```
where php
```
Örnek çıktı: `C:\php\php.exe` → PHP klasörü **C:\php**

Veya:
```
php -i | findstr "Installation Directory"
```

## 2) O klasöre gidin

Örnek:
```
cd C:\php
```
(Kendi yolunuzu yazın.)

## 3) php.ini şablonunu kopyalayın

Klasörde şunlardan biri olmalı:
- `php.ini-development`
- `php.ini-production`

Biri yoksa diğerini kullanın. Kopyalayıp `php.ini` yapın:

**CMD (Yönetici olarak açın):**
```
copy php.ini-development php.ini
```
veya
```
copy php.ini-production php.ini
```

## 4) extension_dir ayarlayın

`php.ini` dosyasını Not Defteri ile açın. Şunu arayın:
```
;extension_dir = "ext"
```
Başındaki `;` kaldırın ve yolunu PHP klasörüne göre düzeltin:
```
extension_dir = "C:\php\ext"
```
(Kendi PHP yolunuzu yazın.)

## 5) OpenSSL'i açın

Aynı dosyada arayın:
```
;extension=openssl
```
veya
```
;extension=php_openssl.dll
```
Başındaki `;` kaldırın:
```
extension=openssl
```
Kaydedin.

## 6) Kontrol

Yeni bir CMD açın:
```
php --ini
```
Artık "Loaded Configuration File" satırında bir yol görünmeli.

Sonra:
```
php -r "var_dump(extension_loaded('openssl'));"
```
Çıktı: `bool(true)` olmalı.

Proje klasöründe:
```
cd C:\Users\sfkoc\Desktop\ToyShop_App
php composer.phar install
```
