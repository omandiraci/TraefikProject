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

# Kubernetes Manifest Yapılandırması

Bu README dosyası, TraefikProject için Kubernetes manifest dosyasının detaylı açıklamasını içerir.

## İçerik

1. [Genel Bakış](#genel-bakış)
2. [Bileşenler](#bileşenler)
3. [Kaynak Limitleri](#kaynak-limitleri)
4. [Erişim Noktaları](#erişim-noktaları)
5. [Güvenlik](#güvenlik)
6. [Kurulum](#kurulum)

## Genel Bakış

Bu manifest dosyası, aşağıdaki bileşenleri içeren bir Kubernetes cluster'ı oluşturur:
- Traefik (API Gateway)
- MariaDB (Veritabanı)
- WordPress (Web Uygulaması)
- phpMyAdmin (Veritabanı Yönetim Aracı)

## Bileşenler

### 1. Traefik (API Gateway)
- **Versiyon**: v2.5
- **Replica Sayısı**: 1
- **Özellikler**:
  - Dashboard etkin
  - HTTP (80) ve HTTPS (443) portları
  - Kubernetes Ingress desteği
  - RBAC (Role-Based Access Control) yapılandırması
  - ServiceAccount ve ClusterRole tanımları

### 2. MariaDB (Veritabanı)
- **Versiyon**: 10.6
- **Replica Sayısı**: 1
- **Özellikler**:
  - Persistent Volume kullanımı (1Gi)
  - Otomatik veritabanı ve kullanıcı oluşturma
  - WordPress için özel kullanıcı ve veritabanı

### 3. WordPress
- **Versiyon**: Latest
- **Replica Sayısı**: 3
- **Özellikler**:
  - Persistent Volume kullanımı (1Gi)
  - MariaDB entegrasyonu
  - Otomatik yapılandırma

### 4. phpMyAdmin
- **Versiyon**: Latest
- **Replica Sayısı**: 3
- **Özellikler**:
  - MariaDB entegrasyonu
  - Root erişimi

## Kaynak Limitleri

### Traefik
- **CPU Limit**: 500m (0.5 core)
- **CPU Request**: 100m (0.1 core)
- **Memory Limit**: 256Mi
- **Memory Request**: 64Mi

### MariaDB
- **CPU Limit**: 1000m (1 core)
- **CPU Request**: 200m (0.2 core)
- **Memory Limit**: 1Gi
- **Memory Request**: 256Mi

### WordPress
- **CPU Limit**: 500m (0.5 core)
- **CPU Request**: 100m (0.1 core)
- **Memory Limit**: 512Mi
- **Memory Request**: 128Mi

### phpMyAdmin
- **CPU Limit**: 200m (0.2 core)
- **CPU Request**: 50m (0.05 core)
- **Memory Limit**: 256Mi
- **Memory Request**: 64Mi

## Erişim Noktaları

### Traefik
- **Dashboard**: NodePort 30081
- **HTTP**: NodePort 30080
- **HTTPS**: NodePort 30443

### WordPress
- **HTTP**: NodePort 30082

### phpMyAdmin
- **HTTP**: NodePort 30083

## Güvenlik

### Traefik
- Root olmayan kullanıcı (65532)
- Salt okunur root dosya sistemi
- Tüm yetenekler kaldırıldı
- RBAC ile sınırlı API erişimi

### MariaDB
- Özel kullanıcı ve veritabanı
- Güvenli şifre yapılandırması

## Kurulum

1. Manifest dosyasını uygulayın:
   ```bash
   kubectl apply -f manifest.yaml
   ```

2. Pod'ların durumunu kontrol edin:
   ```bash
   kubectl get pods
   ```

3. Servisleri kontrol edin:
   ```bash
   kubectl get services
   ```

4. Erişim noktaları:
   - Traefik Dashboard: http://localhost:30081
   - WordPress: http://localhost:30082
   - phpMyAdmin: http://localhost:30083

## Notlar

- Tüm servisler NodePort tipinde yapılandırılmıştır
- Persistent Volume'lar ReadWriteOnce modunda çalışır
- WordPress ve phpMyAdmin 3 replica ile yüksek erişilebilirlik sağlar
- Traefik, Kubernetes Ingress controller olarak çalışır
