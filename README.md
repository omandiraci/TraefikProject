# 🚀 Traefik + WordPress + phpMyAdmin Docker Projesi

Bu proje, Docker kullanarak aşağıdaki servisleri içeren izole bir WordPress geliştirme ortamı oluşturur:

* 🌐 **Traefik v2.5** (reverse proxy & load balancer)
* 📝 **WordPress** (içerik yönetimi)
* 🛢️ **MariaDB** (veritabanı)
* 🛠️ **phpMyAdmin** (veritabanı yönetim arayüzü)

## 📂 Proje Yapısı

```
TraefikProject/
├── docker-compose.yml
├── wordpress/               # WordPress verileri
├── mysql-data/              # MariaDB verileri
├── README.md
└── Docker-Compose-Guide.md
```

## 🖼️ Mimarî

```
    [Client Browser]
          │
          ▼
     ┌──────────┐
     │ Traefik  │ ◄──────────── Docker Socket
     └──────────┘
        │    │
        ▼    ▼
 ┌────────┐ ┌────────────┐
 │ WordPress │ │ phpMyAdmin │
 └────────┘ └────────────┘
        │
        ▼
    ┌─────────┐
    │ MariaDB │
    └─────────┘
```

## ⚙️ Kurulum Adımları

1. **Depoyu Klonlayın:**

```bash
git clone https://github.com/omandiraci/TraefikProject.git
cd TraefikProject
```

2. **Servisleri Başlatın:**

```bash
docker-compose up -d
```

3. **Servislere Erişim:**

| Servis        | Adres                                                                |
| ------------- | -------------------------------------------------------------------- |
| WordPress     | [http://localhost:8081/wp](http://localhost:8081/wp)                 |
| phpMyAdmin    | [http://localhost:8082/phpmyadmin](http://localhost:8082/phpmyadmin) |
| Traefik Panel | [http://localhost:8080](http://localhost:8080)                       |

## 🔐 Varsayılan Bilgiler

**WordPress DB:**

* Kullanıcı: `wordpress_user`
* Şifre: `wordpress_password`

**phpMyAdmin:**

* Kullanıcı: `root`
* Şifre: `root_password`

## 📦 Gereksinimler

* Docker
* Docker Compose

## 📤 Yayınlama

Dosyaları GitHub'a göndermek için:

```bash
git add .
git commit -m "İlk kurulum"
git push -u origin main
```

## 📄 Lisans

MIT Lisansı
