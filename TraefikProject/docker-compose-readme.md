# Docker Compose Yapılandırma Detayları

Bu belge, TraefikProject'in Docker Compose yapılandırmasını detaylı olarak açıklamaktadır.

## Port Yapılandırması

| Servis | Port | Açıklama |
|--------|------|-----------|
| Traefik | 80 | HTTP trafiği |
| Traefik | 443 | HTTPS trafiği |
| Traefik | 8080 | Dashboard erişimi |
| WordPress | 8081 | Doğrudan erişim |
| phpMyAdmin | 8082 | Doğrudan erişim |

## Servis Yapılandırmaları

### 1. Traefik Servisi

```yaml
services:
  traefik:
    image: traefik:v2.5
    container_name: traefik
    restart: unless-stopped
    ports:
      - "80:80"      # HTTP
      - "443:443"    # HTTPS
      - "8080:8080"  # Dashboard
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock:ro
    command:
      - --api.dashboard=true
      - --api.insecure=true
      - --providers.docker=true
      - --providers.docker.exposedbydefault=false
      - --entrypoints.web.address=:80
      - --entrypoints.websecure.address=:443
```

#### Açıklamalar:
- **image**: Traefik'in 2.5 sürümünü kullanır
- **ports**: 
  - 80: HTTP trafiği
  - 443: HTTPS trafiği
  - 8080: Dashboard erişimi
- **volumes**: Docker socket'i salt okunur olarak bağlar
- **command**: 
  - Dashboard API'sini etkinleştirir
  - Docker provider'ı yapılandırır
  - HTTP ve HTTPS endpoint'lerini tanımlar

### 2. WordPress Servisi

```yaml
  wordpress:
    image: wordpress:latest
    restart: always
    ports:
      - "8081:80"    # WordPress doğrudan erişim
    environment:
      WORDPRESS_DB_HOST: db
      WORDPRESS_DB_USER: wordpress_user
      WORDPRESS_DB_PASSWORD: wordpress_password
      WORDPRESS_DB_NAME: wordpress_db
    volumes:
      - ./wordpress:/var/www/html
    networks:
      - wordpress-network
      - proxy
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.wordpress.rule=PathPrefix(`/wp`)"
      - "traefik.http.routers.wordpress.entrypoints=web"
```

#### Açıklamalar:
- **image**: En son WordPress sürümü
- **ports**: 8081 portu üzerinden doğrudan erişim
- **environment**: Veritabanı bağlantı bilgileri
- **volumes**: WordPress dosyaları için kalıcı depolama
- **labels**: Traefik routing kuralları

### 3. MariaDB Servisi

```yaml
  db:
    image: mariadb:10.6
    container_name: mariadb
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: root_password
      MYSQL_DATABASE: wordpress_db
      MYSQL_USER: wordpress_user
      MYSQL_PASSWORD: wordpress_password
    volumes:
      - ./mysql-data:/var/lib/mysql
```

#### Açıklamalar:
- **image**: MariaDB 10.6 sürümü
- **environment**: Veritabanı yapılandırması
- **volumes**: Veritabanı dosyaları için kalıcı depolama

### 4. phpMyAdmin Servisi

```yaml
  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    restart: always
    ports:
      - "8082:80"    # phpMyAdmin doğrudan erişim
    environment:
      PMA_HOST: db
      PMA_USER: root
      PMA_PASSWORD: root_password
    depends_on:
      - db
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.phpmyadmin.rule=PathPrefix(`/phpmyadmin`)"
```

#### Açıklamalar:
- **image**: phpMyAdmin imajı
- **ports**: 8082 portu üzerinden doğrudan erişim
- **environment**: Veritabanı bağlantı bilgileri
- **labels**: Traefik routing kuralları

## Ağ Yapılandırması

```yaml
networks:
  wordpress-network:
    driver: bridge
  proxy:
    driver: bridge
```

- **wordpress-network**: WordPress ve veritabanı servisleri arasındaki iletişim
- **proxy**: Traefik ve web servisleri arasındaki iletişim

## Volume Yapılandırması

- **./wordpress**: WordPress dosyaları için
- **./mysql-data**: MariaDB veritabanı dosyaları için

## Erişim Noktaları

1. **Traefik Dashboard**:
   - URL: http://localhost:8080
   - Port: 8080

2. **WordPress**:
   - Traefik üzerinden: http://localhost/wp
   - Doğrudan erişim: http://localhost:8081
   - Port: 8081

3. **phpMyAdmin**:
   - Traefik üzerinden: http://localhost/phpmyadmin
   - Doğrudan erişim: http://localhost:8082
   - Port: 8082

## Güvenlik Önerileri

1. Üretim ortamında:
   - Tüm şifreleri değiştirin
   - SSL sertifikalarını yapılandırın
   - Güvenlik duvarı kurallarını ayarlayın

2. Traefik yapılandırması:
   - Dashboard erişimini kısıtlayın
   - SSL/TLS yapılandırması ekleyin
   - Rate limiting ekleyin

3. Veritabanı güvenliği:
   - Güçlü şifreler kullanın
   - Root erişimini kısıtlayın
   - Düzenli yedekleme yapın

## Sorun Giderme

1. Servis erişim sorunları:
   ```bash
   docker-compose logs traefik
   docker-compose logs wordpress
   ```

2. Veritabanı bağlantı sorunları:
   ```bash
   docker-compose logs db
   docker-compose logs phpmyadmin
   ```

3. Ağ sorunları:
   ```bash
   docker network inspect proxy
   docker network inspect wordpress-network
   ```

4. Port çakışmaları:
   ```bash
   netstat -tuln | grep 80
   netstat -tuln | grep 443
   netstat -tuln | grep 8080
   netstat -tuln | grep 8081
   netstat -tuln | grep 8082
   ``` 