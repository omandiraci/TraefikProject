# 📘 Docker Compose ve Kubernetes Kullanım Kılavuzu

Bu rehber, projenin hem Docker Compose hem de Kubernetes ortamında nasıl çalıştırılacağını açıklar. Aşağıda servislerin detaylı açıklaması ve yapılandırma bilgileri yer almaktadır.

---

## 🐳 Docker Compose Kurulumu

```bash
# Docker servislerini başlatın
docker-compose up -d
```

* Traefik, WordPress, MariaDB ve phpMyAdmin servisleri ayağa kalkar.
* Traefik Dashboard: `http://localhost:8080`
* WordPress: `http://localhost:8081/wp`
* phpMyAdmin: `http://localhost:8082/phpmyadmin`

---

## ☸️ Kubernetes Deployments

### Hazırlık

Docker Desktop üzerinde Kubernetes etkinleştirildiğinden emin olun:

* `Settings > Kubernetes > Enable Kubernetes`

### Uygulama

```bash
# Manifest dosyasını apply edin
kubectl apply -f kubernetes/kubernetes-manifest.yaml
```

### Servisler

| Servis     | Tip      | Port | Açıklama                      |
| ---------- | -------- | ---- | ----------------------------- |
| traefik    | NodePort | 80   | HTTP istekleri                |
| wordpress  | NodePort | 80   | WordPress arayüzü             |
| phpmyadmin | NodePort | 80   | phpMyAdmin veritabanı arayüzü |

> Not: Traefik dashboard da 8080 portu üzerinden NodePort olarak expose edilir.

---

## 📂 Klasör Yapısı

```
TraefikProject/
├── docker-compose.yml
├── kubernetes/
│   └── kubernetes-manifest.yaml
├── README.md
└── Docker-compose-guide.md
```

---

## ⚠️ Güvenlik Uyarısı

* `--api.insecure=true` sadece geliştirme ortamında kullanılmalıdır.
* Docker soketine verilen erişim yüksek yetki gerektirir. Üretim ortamı için dikkatli olunmalıdır.

---

## 🤝 Katkıda Bulunmak

Pull request'ler, hata bildirimleri ve öneriler için GitHub üzerinden katkıda bulunabilirsiniz.

---

Happy Deployment! 🚀
