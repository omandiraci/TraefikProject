# 📘 Docker Compose Kullanım Kılavuzu

Bu dosya, `docker-compose.yml` içerisindeki servislerin detaylı açıklamasını ve bağlantı portlarını içerir.

## 🔧 Servisler

### 1. 🚦 Traefik

* **image:** `traefik:v2.5`
* **container\_name:** `traefik`
* **Açıklama:** Tüm trafiği yöneten reverse proxy sunucusudur.
* **Portlar:**

  * `80:80` → HTTP
  * `443:443` → HTTPS
  * `8080:8080` → Traefik dashboard (geliştirme amaçlı açık bırakıldı)
* **Volumes:**

  * Docker socket (`/var/run/docker.sock`) okunur olarak bağlanır, böylece container bilgilerine erişebilir.
* **Command:**

  * Dashboard aktif
  * Docker provider aktif
  * Yalnızca etiketlenmiş container'ları yönlendirir (exposedbydefault=false)

### 2. 📝 WordPress

* **image:** `wordpress:latest`
* **container\_name:** (belirtilmemiş)
* **Portlar:**

  * `8081:80` → WordPress web arayüzü (localhost:8081/wp)
* **Volumes:**

  * `./wordpress:/var/www/html` → WordPress dosyalarının yerel klasöre bağlanması
* **Environment:**

  * Veritabanı bağlantı bilgileri
* **Labels (Traefik):**

  * URL yönlendirme kuralı (`/wp` ile başlayanlar)

### 3. 🛢️ MariaDB

* **image:** `mariadb:10.6`
* **container\_name:** `mariadb`
* **Açıklama:** WordPress veritabanı
* **Volumes:**

  * `./mysql-data:/var/lib/mysql`
* **Environment:**

  * Root ve WordPress kullanıcı bilgileri tanımlı

### 4. 🛠️ phpMyAdmin

* **image:** `phpmyadmin/phpmyadmin`
* **container\_name:** (belirtilmemiş)
* **Portlar:**

  * `8082:80` → phpMyAdmin web arayüzü (localhost:8082/phpmyadmin)
* **Environment:**

  * MariaDB sunucusu bilgileri
* **Labels (Traefik):**

  * URL yönlendirme kuralı (`/phpmyadmin` ile başlayanlar)

## 🌐 Ağlar

* `wordpress-network`: WordPress ile DB arasında
* `proxy`: Traefik ile diğer servisler arasında

## 🗂️ Port Tablosu

| Servis     | İç Port | Dış Port | Açıklama                         |
| ---------- | ------- | -------- | -------------------------------- |
| Traefik    | 80      | 80       | HTTP istekleri                   |
|            | 443     | 443      | HTTPS istekleri                  |
|            | 8080    | 8080     | Traefik Dashboard                |
| WordPress  | 80      | 8081     | Web Arayüzü (/wp)                |
| phpMyAdmin | 80      | 8082     | Veritabanı Arayüzü (/phpmyadmin) |

## 📝 Notlar

* `api.insecure=true` opsiyonu üretimde **kapatılmalıdır**.
* `docker.sock`'a erişim yüksek yetki gerektirir, sadece güvenli ortamlarda kullanın.

---

Herhangi bir sorun yaşarsanız GitHub Issues sekmesinden bildirebilirsiniz.

Happy Dockering! 🐳
