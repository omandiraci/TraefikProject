# Traefik Projesi

Bu proje, Traefik reverse proxy kullanarak WordPress, MariaDB ve phpMyAdmin servislerini yöneten bir Docker Compose yapılandırması içerir.

## Özellikler

- **Traefik v2.5**: Modern reverse proxy ve load balancer
- **WordPress**: En son WordPress sürümü
- **MariaDB 10.6**: Veritabanı sunucusu
- **phpMyAdmin**: Veritabanı yönetim arayüzü

## Kurulum

1. Projeyi klonlayın:
   ```bash
   git clone https://github.com/omandiraci/TraefikProject.git
   cd TraefikProject
   ```

2. Servisleri başlatın:
   ```bash
   docker-compose up -d
   ```

## Erişim Noktaları

### Traefik Dashboard
- URL: http://localhost:8080
- Port: 8080
- Açıklama: Traefik yönetim paneli

### WordPress
- Traefik üzerinden: http://localhost/wp
- Doğrudan erişim: http://localhost:8081
- Port: 8081
- Açıklama: WordPress web sitesi

### phpMyAdmin
- Traefik üzerinden: http://localhost/phpmyadmin
- Doğrudan erişim: http://localhost:8082
- Port: 8082
- Açıklama: Veritabanı yönetim paneli

## Servis Detayları

### Traefik
- Port: 80 (HTTP), 443 (HTTPS), 8080 (Dashboard)
- Docker provider aktif
- Dashboard erişimi etkin

### WordPress
- Port: 8081 (doğrudan erişim)
- Traefik üzerinden: /wp path'i ile erişim
- MariaDB ile entegre

### MariaDB
- Veritabanı: wordpress_db
- Kullanıcı: wordpress_user
- Şifre: wordpress_password

### phpMyAdmin
- Port: 8082 (doğrudan erişim)
- Traefik üzerinden: /phpmyadmin path'i ile erişim
- Root erişimi mevcut

## Ağ Yapılandırması

- **proxy**: Traefik ve web servisleri için
- **wordpress-network**: WordPress ve veritabanı servisleri için

## Güvenlik Notları

1. Üretim ortamında şifreleri değiştirin
2. SSL sertifikalarını yapılandırın
3. Güvenlik duvarı kurallarını gözden geçirin

## Lisans

MIT 