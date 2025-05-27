# Kubernetes Manifest Guide: WordPress, MariaDB, Traefik, and phpMyAdmin

Bu kılavuz, sağladığınız `manifest.yaml` dosyasını detaylı bir şekilde açıklamaktadır. Bu dosya, Kubernetes üzerinde bir WordPress sitesi, MariaDB veritabanı, Traefik ters proxy ve phpMyAdmin yönetim arayüzünü çalıştırmak için gerekli tanımlamaları içerir.

**Nasıl İndirilir?**

Bu kılavuzu indirilebilir bir şekilde kullanmak için aşağıdaki adımları izleyebilirsiniz:

1.  Bu metin kutusundaki tüm metni seçin ve kopyalayın.
2.  Metin düzenleyici (örneğin, Not Defteri, TextEdit, VS Code) açın.
3.  Kopyaladığınız metni düzenleyiciye yapıştırın.
4.  Dosyayı `kubernetes_guide.md` adıyla kaydedin. `.md` uzantısı, dosyanın Markdown formatında olduğunu belirtir.
5.  Bu `.md` dosyasını herhangi bir Markdown okuyucu veya GitHub, GitLab gibi platformlarda görüntüleyebilirsiniz. Bu platformlar Markdown formatını otomatik olarak yorumlayarak başlıkları, kod bloklarını ve diğer biçimlendirmeleri gösterecektir.
6.  İsteğe bağlı olarak, kılavuzda belirtilen görselleri ilgili yerlere manuel olarak ekleyebilirsiniz.

---

## 1. Persistent Volume Claims (PVC) - Kalıcı Disk Talepleri

Persistent Volume Claims (PVC'ler), Pod'lar tarafından kullanılacak kalıcı depolama alanları için yapılan taleplerdir. Bu tanımlamalar, WordPress ve MariaDB verileri için kalıcı disklerin Kubernetes tarafından nasıl sağlanacağını belirtir.

**YAML Tanımlamaları:**

~~~yaml
# --- Persistent Volume Claims ---
apiVersion: v1
kind: PersistentVolumeClaim
metadata:
  name: wordpress-pvc
spec:
  accessModes:
    - ReadWriteOnce
  resources:
    requests:
      storage: 1Gi
---
apiVersion: v1
kind: PersistentVolumeClaim
metadata:
  name: mariadb-pvc
spec:
  accessModes:
    - ReadWriteOnce
  resources:
    requests:
      storage: 1Gi
~~~

**Açıklamalar:**

* `apiVersion: v1`: Bu kaynağın Kubernetes Core API'sinin v1 versiyonunu kullandığını belirtir.
* `kind: PersistentVolumeClaim`: Bu kaynağın bir PersistentVolumeClaim (Kalıcı Disk Talebi) olduğunu tanımlar.
* `metadata:`: PVC hakkında meta bilgileri içerir.
    * `name: wordpress-pvc`: WordPress uygulamasının kullanacağı PVC'nin adıdır.
    * `name: mariadb-pvc`: MariaDB veritabanının kullanacağı PVC'nin adıdır.
* `spec:`: PVC'nin istenen özelliklerini tanımlar.
    * `accessModes:`: Bu PVC'ye nasıl erişilebileceğini belirtir.
        * `- ReadWriteOnce`: Bu PVC'ye aynı anda sadece bir Pod tarafından okuma ve yazma erişimi sağlanabilir.
    * `resources:`: İstenen kaynakları tanımlar.
        * `requests:`: Talep edilen minimum kaynak miktarını belirtir.
            * `storage: 1Gi`: Bu PVC için 1 Gigabayt (Gi) boyutunda kalıcı depolama alanı talep edilir. Hem `wordpress-pvc` hem de `mariadb-pvc` için 1 GB depolama alanı talep edilmektedir.

**Potansiyel Görsel:**

Bir Kubernetes kümesi şeması üzerinde PVC'lerin Pod'lara nasıl bağlandığını gösteren basit bir çizim eklenebilir.

---

## 2. RBAC (Role-Based Access Control) - Rol Tabanlı Erişim Kontrolü

RBAC, Kubernetes'te kullanıcıların ve uygulamaların küme kaynaklarına erişimini yönetmek için kullanılan bir mekanizmadır. Bu bölümde, Traefik'in ihtiyaç duyduğu izinler tanımlanmaktadır.

**YAML Tanımlamaları:**

~~~yaml
# --- RBAC ---
---
apiVersion: v1
kind: ServiceAccount
metadata:
  name: traefik
---
apiVersion: rbac.authorization.k8s.io/v1
kind: ClusterRole
metadata:
  name: traefik
rules:
  - apiGroups:
      - ""
    resources:
      - services
      - endpoints
      - secrets
    verbs:
      - get
      - list
      - watch
  - apiGroups:
      - extensions
      - networking.k8s.io
    resources:
      - ingresses
      - ingressclasses
    verbs:
      - get
      - list
      - watch
---
apiVersion: rbac.authorization.k8s.io/v1
kind: ClusterRoleBinding
metadata:
  name: traefik
roleRef:
  apiGroup: rbac.authorization.k8s.io
  kind: ClusterRole
  name: traefik
subjects:
  - kind: ServiceAccount
    name: traefik
    namespace: default
~~~

**Açıklamalar:**

* **`ServiceAccount`:**
    * `apiVersion: v1`: Core API'nin v1 versiyonu.
    * `kind: ServiceAccount`: Bir ServiceAccount kaynağı tanımlar. ServiceAccount'lar, Pod'lar içinde çalışan uygulamaların Kubernetes API sunucusuna güvenli bir şekilde erişmesini sağlar.
    * `metadata: name: traefik`: `traefik` adında bir ServiceAccount oluşturur.

* **`ClusterRole`:**
    * `apiVersion: rbac.authorization.k8s.io/v1`: RBAC API'sinin v1 versiyonu.
    * `kind: ClusterRole`: Küme genelinde geçerli olacak bir rol tanımlar.
    * `metadata: name: traefik`: `traefik` adında bir ClusterRole oluşturur.
    * `rules:`: Bu rolün sahip olacağı izinleri tanımlar.
        * İlk kural, Core API grubundaki (`apiGroups: [""]`) `services`, `endpoints` ve `secrets` kaynakları üzerinde `get`, `list` ve `watch` (izleme) işlemlerini yapma izni verir. Traefik, servislerin ve uç noktalarının durumunu izleyerek trafiği doğru yerlere yönlendirmek için bu izinlere ihtiyaç duyar. Ayrıca TLS sertifikalarını içeren secret'lara da erişmesi gerekir.
        * İkinci kural, `extensions` ve `networking.k8s.io` API gruplarındaki `ingresses` ve `ingressclasses` kaynakları üzerinde `get`, `list` ve `watch` işlemlerini yapma izni verir. Traefik, gelen trafiği yönetmek için Ingress tanımlarını okuması ve değişiklikleri izlemesi gerektiğinden bu izinlere ihtiyaç duyar.

* **`ClusterRoleBinding`:**
    * `apiVersion: rbac.authorization.k8s.io/v1`: RBAC API'sinin v1 versiyonu.
    * `kind: ClusterRoleBinding`: Bir ClusterRole'ü bir veya daha fazla Subject'e (kullanıcı, grup veya ServiceAccount) bağlar. Bu bağlama küme genelinde geçerlidir.
    * `metadata: name: traefik`: `traefik` adında bir ClusterRoleBinding oluşturur.
    * `roleRef:`: Bağlanacak olan ClusterRole'ü tanımlar.
        * `apiGroup: rbac.authorization.k8s.io`: Bağlanacak rolün API grubunu belirtir.
        * `kind: ClusterRole`: Bağlanacak kaynağın türünü belirtir.
        * `name: traefik`: Bağlanacak olan ClusterRole'ün adını belirtir.
    * `subjects:`: Bu role hangi Subject'lerin bağlanacağını tanımlar.
        * `- kind: ServiceAccount`: Bir ServiceAccount'ı hedeflediğini belirtir.
        * `name: traefik`: Hedeflenen ServiceAccount'ın adını belirtir.
        * `namespace: default`: ServiceAccount'ın bulunduğu namespace'i belirtir. Bu örnekte `default` namespace'indedir.

**Özet:** Bu RBAC tanımlamaları, `traefik` ServiceAccount'ının küme üzerindeki servisleri, uç noktaları, sırları ve Ingress tanımlarını okuyabilmesi için gerekli rolleri ve bu rolün ServiceAccount'a bağlanmasını sağlar.

**Potansiyel Görsel:**

RBAC kavramlarını ve ilişkilerini (ServiceAccount, ClusterRole, ClusterRoleBinding) gösteren bir diyagram eklenebilir.

---

## 3. Traefik - Ters Proxy ve Yük Dengeleyici

Traefik, modern bulut-yerel uygulamalar için dinamik bir HTTP ters proxy ve yük dengeleyicidir. Kubernetes ile entegre olarak çalışır ve Ingress tanımlarını otomatik olarak yapılandırabilir.

**YAML Tanımlamaları:**

~~~yaml
# --- Traefik ---
---
apiVersion: apps/v1
kind: Deployment
metadata:
  name: traefik
spec:
  replicas: 1
  selector:
    matchLabels:
      app: traefik
  template:
    metadata:
      labels:
        app: traefik
    spec:
      serviceAccountName: traefik
      containers:
        - name: traefik
          image: traefik:v2.5
          ports:
            - containerPort: 80
            - containerPort: 443
            - containerPort: 8080
          args:
            - --api.dashboard=true
            - --api.insecure=true
            - --entrypoints.web.address=:80
            - --entrypoints.websecure.address=:443
            - --providers.kubernetesingress=true
          resources:
            limits:
              cpu: "500m"
              memory: "256Mi"
            requests:
              cpu: "100m"
              memory: "64Mi"
          livenessProbe:
            httpGet:
              path: /dashboard/
              port: 8080
            initialDelaySeconds: 30
            periodSeconds: 10
          readinessProbe:
            httpGet:
              path: /dashboard/
              port: 8080
            initialDelaySeconds: 5
            periodSeconds: 5
          securityContext:
            capabilities:
              drop:
                - ALL
            readOnlyRootFilesystem: true
            runAsNonRoot: true
            runAsUser: 65532
---
apiVersion: v1
kind: Service
metadata:
  name: traefik-service
spec:
  selector:
    app: traefik
  type: NodePort
  ports:
    - name: web
      port: 80
      targetPort: 80
      nodePort: 30080
    - name: websecure
      port: 443
      targetPort: 443
      nodePort: 30443
    - name: dashboard
      port: 8080
      targetPort: 8080
      nodePort: 30081
~~~

**Açıklamalar:**

* **`Deployment` (Traefik):**
    * `apiVersion: apps/v1`: Apps API'sinin v1 versiyonu.
    * `kind: Deployment`: Bir Deployment kaynağı tanımlar. Deployment, Pod'ların yönetilmesini ve ölçeklendirilmesini sağlar.
    * `metadata: name: traefik`: `traefik` adında bir Deployment oluşturur.
    * `spec:`: Deployment'ın istenen durumunu tanımlar.
        * `replicas: 1`: Her zaman bir adet Traefik Pod'unun çalışır durumda olmasını sağlar.
        * `selector:`: Bu Deployment tarafından hangi Pod'ların yönetileceğini belirleyen etiket seçicisidir.
            * `matchLabels: app: traefik`: Etiketinde `app: traefik` bulunan Pod'ları seçer.
        * `template:`: Oluşturulacak Pod'ların şablonunu tanımlar.
            * `metadata: labels: app: traefik`: Oluşturulacak Pod'lara `app: traefik` etiketi ekler.
            * `spec:`: Pod'un özelliklerini tanımlar.
                * `serviceAccountName: traefik`: Bu Pod'un çalışırken kullanacağı ServiceAccount'ı belirtir (`traefik` olarak tanımladığımız ServiceAccount).
                * `containers:`: Pod içinde çalışacak bir veya daha fazla container'ı tanımlar.
                    * `- name: traefik`: Container'ın adıdır.
                    * `image: traefik:v2.5`: Kullanılacak Docker imajını belirtir (Traefik'in v2.5 versiyonu).
                    * `ports:`: Container içinde dinlenecek portları tanımlar.
                        * `containerPort: 80`: HTTP trafiği için.
                        * `containerPort: 443`: HTTPS trafiği için.
                        * `containerPort: 8080`: Traefik Dashboard'u için.
                    * `args:`: Container başlatılırken Traefik'e iletilecek komut satırı argümanlarını tanımlar.
                        * `--api.dashboard=true`: Traefik Dashboard'unu etkinleştirir.
                        * `--api.insecure=true`: Dashboard'a güvenli olmayan (HTTP üzerinden) erişime izin verir (prodüksiyon ortamları için önerilmez).
                        * `--entrypoints.web.address=:80`: `web` adında bir giriş noktası (entrypoint) tanımlar ve 80 numaralı portu dinlemesini sağlar.
                        * `--entrypoints.websecure.address=:443`: `websecure` adında bir giriş noktası tanımlar ve 443 numaralı portu dinlemesini sağlar (HTTPS için).
                        * `--providers.kubernetesingress=true`: Kubernetes Ingress tanımlayıcı sağlayıcısını etkinleştirir. Traefik, Ingress kaynaklarını otomatik olarak okuyup yapılandırmasını sağlar.
                    * `resources:`: Container için kaynak sınırlarını ve taleplerini tanımlar.
                        * `limits: cpu: "500m", memory: "256Mi"`: Container'ın kullanabileceği maksimum CPU (500 miliCPU) ve bellek (256 MiB) miktarını belirtir.
                        * `requests: cpu: "100m", memory: "64Mi"`: Kubernetes'in bu container için garanti etmesi gereken minimum CPU (100 miliCPU) ve bellek (64 MiB) miktarını belirtir.
                    * `livenessProbe:`: Container'ın sağlıklı olup olmadığını düzenli olarak kontrol eder. Eğer probe başarısız olursa, Kubernetes container'ı yeniden başlatabilir.
                        * `httpGet: path: /dashboard/, port: 8080`: Her 10 saniyede bir (ilk 30 saniye gecikmeyle) 8080 portundaki `/dashboard/` adresine HTTP GET isteği gönderir. Başarılı bir yanıt (2xx veya 3xx) container'ın sağlıklı olduğunu gösterir.
                    * `readinessProbe:`: Container'ın trafiği kabul etmeye hazır olup olmadığını düzenli olarak kontrol eder. Eğer probe başarısız olursa, Kubernetes bu Pod'u servislerin yük dengelemesinden çıkarır.
                        * `httpGet: path: /dashboard/, port: 8080`: Her 5 saniyede bir (ilk 5 saniye gecikmeyle) 8080 portundaki `/dashboard/` adresine HTTP GET isteği gönderir. Başarılı bir yanıt (2xx veya 3xx) container'ın trafiği kabul etmeye hazır olduğunu gösterir.
                    * `securityContext:`: Container için güvenlik bağlamı ayarlarını tanımlar.
                        * `capabilities: drop: - ALL`: Tüm Linux yeteneklerini (capabilities) düşürür, böylece container daha az ayrıcalıkla çalışır.
                        * `readOnlyRootFilesystem: true`: Container'ın kök dosya sistemini salt okunur yapar.
                        * `runAsNonRoot: true`: Container'ın root kullanıcı olarak çalışmasını engeller.
                        * `runAsUser: 65532`: Container'ın belirli bir kullanıcı ID'si (65532) ile çalışmasını sağlar.

* **`Service` (Traefik):**
    * `apiVersion: v1`: Core API'nin v1 versiyonu.
    * `kind: Service`: Bir Service kaynağı tanımlar. Service, bir grup Pod'a sabit bir erişim noktası ve yük dengeleme sağlar.
    * `metadata: name: traefik-service`: `traefik-service` adında bir Service oluşturur.
    * `spec:`: Service'in istenen durumunu tanımlar.
        * `selector: app: traefik`: Bu Service'in hangi Pod'ları hedefleyeceğini belirleyen etiket seçicisidir (etiketinde `app: traefik` bulunan Pod'ları seçer).
        * `type: NodePort`: Bu Service'in türünü NodePort olarak belirtir. NodePort türü, servisi kümedeki her Node'un belirli bir portu üzerinden dış dünyaya açar.
        * `ports:`: Service'in dinleyeceği ve hedef Pod'lardaki container portlarına yönlendireceği portları tanımlar.
            * `- name: web, port: 80, targetPort: 80, nodePort: 30080`: `web` adında bir port tanımlar. Service üzerinde 80 numaralı porttan gelen trafik, hedef Pod'lardaki 80 numaralı porta yönlendirilir ve bu port kümedeki her Node'un 30080 numaralı portu üzerinden erişilebilir hale gelir.
            * `- name: websecure, port: 443, targetPort: 443, nodePort: 30443`: `websecure` adında bir port tanımlar. Service üzerinde 443 numaralı porttan gelen trafik, hedef Pod'lardaki 443 numaralı porta yönlendirilir ve bu port kümedeki her Node'un 30443 numaralı portu üzerinden erişilebilir hale gelir.
            * `- name: dashboard, port: 8080, targetPort: 8080, nodePort: 30081`: `dashboard` adında bir port tanımlar. Service üzerinde 8080 numaralı porttan gelen trafik, hedef Pod'lardaki 8080 numaralı porta yönlendirilir ve bu port kümedeki her Node'un 30081 numaralı portu üzerinden erişilebilir hale gelir.

**Özet:** Bu bölüm, Traefik'in bir Deployment olarak Kubernetes üzerinde nasıl çalıştırılacağını ve bir NodePort Service aracılığıyla dış dünyaya nasıl açılacağını tanımlar. Traefik, Ingress kaynaklarını dinleyerek gelen HTTP/HTTPS trafiğini yönetir ve WordPress gibi uygulamalara yönlendirir.

**Potansiyel Görsel:**

Traefik'in Kubernetes Ingress ile nasıl etkileşime girdiğini ve trafiği Pod'lara nasıl yönlendirdiğini gösteren bir akış şeması eklenebilir. Ayrıca, NodePort servisinin çalışma prensibini gösteren basit bir diyagram da faydalı olabilir.

---

## 4. MariaDB - Veritabanı Sunucusu

MariaDB, popüler bir açık kaynaklı ilişkisel veritabanı yönetim sistemidir. WordPress tarafından veri depolamak için kullanılır.

**YAML Tanımlamaları:**

~~~yaml
# --- MariaDB ---
---
apiVersion: apps/v1
kind: Deployment
metadata:
  name: mariadb
spec:
  replicas: 1
  selector:
    matchLabels:
      app: mariadb
  template:
    metadata:
      labels:
        app: mariadb
    spec:
      containers:
        - name: mariadb
          image: mariadb:10.6
          ports:
            - containerPort: 3306
          env:
            - name: MYSQL_ROOT_PASSWORD
              value: root_password
            - name: MYSQL_DATABASE
              value: wordpress_db
            - name: MYSQL_USER
              value: wordpress_user
            - name: MYSQL_PASSWORD
              value: wordpress_password
          resources:
            limits:
              cpu: "1000m"
              memory: "1Gi"
            requests:
              cpu: "200m"
              memory: "256Mi"
          volumeMounts:
            - name: mariadb-storage
              mountPath: /var/lib/mysql
      volumes:
        - name: mariadb-storage
          persistentVolumeClaim:
            claimName: mariadb-pvc
---
apiVersion: v1
kind: Service
metadata:
  name: db
spec:
  selector:
    app: mariadb
  ports:
    - port: 3306
      targetPort: 3306
~~~

**Açıklamalar:**

* **`Deployment` (MariaDB):**
    * `apiVersion: apps/v1`: Apps API'sinin v1 versiyonu.
    * `kind: Deployment`: Bir Deployment kaynağı tanımlar.
    * `metadata: name: mariadb`: `mariadb` adında bir Deployment oluşturur.
    * `spec:`: Deployment'ın istenen durumunu tanımlar.
        * `replicas: 1`: Her zaman bir adet MariaDB Pod'unun çalışır durumda olmasını sağlar.
        * `selector: matchLabels: app: mariadb`: Etiketinde `app: mariadb` bulunan Pod'ları seçer.
        * `template:`: Oluşturulacak Pod'ların şablonunu tanımlar.
            * `metadata: labels: app: mariadb`: Oluşturulacak Pod'lara `app: mariadb` etiketi ekler.
            * `spec:`: Pod'un özelliklerini tanımlar.
                * `containers:`: Pod içinde çalışacak container'ı tanımlar.
                    * `- name: mariadb`: Container'ın adıdır.
                    * `image: mariadb:10.6`: Kullanılacak Docker imajını belirtir (MariaDB'nin 10.6 versiyonu).
                    * `ports: - containerPort: 3306`: Container içinde MariaDB'nin dinleyeceği standart portu (3306) tanımlar.
                    * `env:`: Container içinde ortam değişkenlerini tanımlar. Bu değişkenler MariaDB'nin yapılandırılması için kullanılır.
                        * `MYSQL_ROOT_PASSWORD: root_password`: MariaDB root kullanıcısının parolasını tanımlar. **Güvenlik nedeniyle bu değeri daha güçlü bir parolayla değiştirmeniz önemlidir.**
                        * `MYSQL_DATABASE: wordpress_db`: Başlangıçta oluşturulacak veritabanının adını tanımlar.
                        * `MYSQL_USER: wordpress_user`: WordPress'in veritabanına erişmek için kullanacağı kullanıcının adını tanımlar.
                        * `MYSQL_PASSWORD: wordpress_password`: WordPress kullanıcısının parolasını tanımlar. **Güvenlik nedeniyle bu değeri daha güçlü bir parolayla değiştirmeniz önemlidir.**
                    * `resources:`: Container için kaynak sınırlarını ve taleplerini tanımlar.
                        * `limits: cpu: "1000m", memory: "1Gi"`: Container'ın kullanabileceği maksimum CPU (1 çekirdek) ve bellek (1 GiB) miktarını belirtir.
                        * `requests: cpu: "200m", memory: "256Mi"`: Kubernetes'in bu container için garanti etmesi gereken minimum CPU (200 miliCPU) ve bellek (256 MiB) miktarını belirtir.
                    * `volumeMounts:`: Container içindeki dizinleri Persistent Volume Claim'lerine bağlar.
                        * `- name: mariadb-storage, mountPath: /var/lib/mysql`: `mariadb-storage` adındaki Volume'u container içindeki `/var/lib/mysql` dizinine bağlar. MariaDB veritabanı dosyaları bu dizinde saklanır.
                * `volumes:`: Pod tarafından kullanılacak Volume'ları tanımlar.
                    * `- name: mariadb-storage, persistentVolumeClaim: claimName: mariadb-pvc`: `mariadb-storage` adında bir Volume tanımlar ve bunun `mariadb-pvc` adındaki Persistent Volume Claim tarafından sağlanan kalıcı diski kullanacağını belirtir.

* **`Service` (MariaDB):**
    * `apiVersion: v1`: Core API'nin v1 versiyonu.
    * `kind: Service`: Bir Service kaynağı tanımlar.
    * `metadata: name: db`: `db` adında bir Service oluşturur. WordPress Pod'ları bu isim üzerinden MariaDB'ye erişecektir.
    * `spec:`: Service'in istenen durumunu tanımlar.
        * `selector: app: mariadb`: Etiketinde `app: mariadb` bulunan Pod'ları hedefleyen bir seçici tanımlar.
        * `ports: - port: 3306, targetPort: 3306`: Service üzerinde 3306 numaralı portu açar ve bu porta gelen trafiği hedef Pod'lardaki 3306 numaralı porta yönlendirir.

**Özet:** Bu bölüm, MariaDB veritabanı sunucusunun bir Deployment olarak nasıl çalıştırılacağını ve bir Service aracılığıyla Kubernetes içindeki diğer uygulamalara (WordPress) nasıl erişilebilir hale getirileceğini tanımlar. Kalıcı veri saklama için `mariadb-pvc` kullanılır.

**Potansiyel Görsel:**

MariaDB Deployment ve Service arasındaki ilişkiyi gösteren basit bir diyagram eklenebilir. Ayrıca, Persistent Volume Claim'in MariaDB Pod'una nasıl bağlandığını gösteren bir görsel de faydalı olabilir.

---

## 5. WordPress - Uygulama Sunucusu

WordPress, popüler bir açık kaynaklı içerik yönetim sistemidir. Bu tanımlamalar, WordPress uygulamasının Kubernetes üzerinde nasıl çalıştırılacağını belirtir.

**YAML Tanımlamaları:**

~~~yaml
# --- WordPress ---
---
apiVersion: apps/v1
kind: Deployment
metadata:
  name: wordpress
spec:
  replicas: 3
  selector:
    matchLabels:
      app: wordpress
  template:
    metadata:
      labels:
        app: wordpress
    spec:
      containers:
        - name: wordpress
          image: wordpress:latest
          ports:
            - containerPort: 80
          env:
            - name: WORDPRESS_DB_HOST
              value: db
            - name: WORDPRESS_DB_USER
              value: wordpress_user
            - name: WORDPRESS_DB_PASSWORD
              value: wordpress_password
            - name: WORDPRESS_DB_NAME
              value: wordpress_db
          resources:
            limits:
              cpu: "500m"
              memory: "512Mi"
            requests:
              cpu: "100m"
              memory: "128Mi"
          volumeMounts:
            - name: wordpress-storage
              mountPath: /var/www/html
      volumes:
        - name: wordpress-storage
          persistentVolumeClaim:
            claimName: wordpress-pvc
---
apiVersion: v1
kind: Service
metadata:
  name: wordpress-service
spec:
  selector:
    app: wordpress
  type: NodePort
  ports:
    - name: http
      port: 80
      targetPort: 80
      nodePort: 30082
~~~

**Açıklamalar:**

* **`Deployment` (WordPress):**
    * `apiVersion: apps/v1`: Apps API'sinin v1 versiyonu.
    * `kind: Deployment`: Bir Deployment kaynağı tanımlar.
    * `metadata: name: wordpress`: `wordpress` adında bir Deployment oluşturur.
    * `spec:`: Deployment'ın istenen durumunu tanımlar.
        * `replicas: 3`: Her zaman üç adet WordPress Pod'unun çalışır durumda olmasını sağlar. Bu, uygulamanın daha yüksek erişilebilirliği ve bir miktar yük dengelemesi için önemlidir.
        * `selector: matchLabels: app: wordpress`: Etiketinde `app: wordpress` bulunan Pod'ları seçer.
        * `template:`: Oluşturulacak Pod'ların şablonunu tanımlar.
            * `metadata: labels: app: wordpress`: Oluşturulacak Pod'lara `app: wordpress` etiketi ekler.
            * `spec:`: Pod'un özelliklerini tanımlar.
                * `containers:`: Pod içinde çalışacak container'ı tanımlar.
                    * `- name: wordpress`: Container'ın adıdır.
                    * `image: wordpress:latest`: Kullanılacak Docker imajını belirtir (WordPress'in en son versiyonu).
                    * `ports: - containerPort: 80`: Container içinde WordPress'in dinleyeceği standart HTTP portunu (80) tanımlar.
                    * `env:`: Container içinde ortam değişkenlerini tanımlar. Bu değişkenler WordPress'in veritabanı ile bağlantı kurması için kullanılır.
                        * `WORDPRESS_DB_HOST: db`: Veritabanı sunucusunun adresini belirtir. `db`, MariaDB Service'inin adıdır. Kubernetes içindeki DNS sayesinde Pod'lar bu isimle MariaDB Service'ine erişebilir.
                        * `WORDPRESS_DB_USER: wordpress_user`: WordPress'in veritabanına erişmek için kullanacağı kullanıcı adını belirtir.
                        * `WORDPRESS_DB_PASSWORD: wordpress_password`: WordPress kullanıcısının veritabanı parolasını belirtir.
                        * `WORDPRESS_DB_NAME: wordpress_db`: WordPress'in kullanacağı veritabanının adını belirtir.
                    * `resources:`: Container için kaynak sınırlarını ve taleplerini tanımlar.
                        * `limits: cpu: "500m", memory: "512Mi"`: Container'ın kullanabileceği maksimum CPU (500 miliCPU) ve bellek (512 MiB) miktarını belirtir.
                        * `requests: cpu: "100m", memory: "128Mi"`: Kubernetes'in bu container için garanti etmesi gereken minimum CPU (100 miliCPU) ve bellek (128 MiB) miktarını belirtir.
                    * `volumeMounts:`: Container içindeki dizinleri Persistent Volume Claim'lerine bağlar.
                        * `- name: wordpress-storage, mountPath: /var/www/html`: `wordpress-storage` adındaki Volume'u container içindeki `/var/www/html` dizinine bağlar. WordPress kurulumu ve yüklemeleri bu dizinde saklanır.
                * `volumes:`: Pod tarafından kullanılacak Volume'ları tanımlar.
                    * `- name: wordpress-storage, persistentVolumeClaim: claimName: wordpress-pvc`: `wordpress-storage` adında bir Volume tanımlar ve bunun `wordpress-pvc` adındaki Persistent Volume Claim tarafından sağlanan kalıcı diski kullanacağını belirtir.

* **`Service` (WordPress):**
    * `apiVersion: v1`: Core API'nin v1 versiyonu.
    * `kind: Service`: Bir Service kaynağı tanımlar.
    * `metadata: name: wordpress-service`: `wordpress-service` adında bir Service oluşturur.
    * `spec:`: Service'in istenen durumunu tanımlar.
        * `selector: app: wordpress`: Etiketinde `app: wordpress` bulunan Pod'ları hedefleyen bir seçici tanımlar.
        * `type: NodePort`: Bu Service'in türünü NodePort olarak belirtir. WordPress'e dışarıdan erişmek için kullanılır.
        * `ports:`: Service'in dinleyeceği ve hedef Pod'lardaki container portlarına yönlendireceği portları tanımlar.
            * `- name: http, port: 80, targetPort: 80, nodePort: 30082`: `http` adında bir port tanımlar. Service üzerinde 80 numaralı porttan gelen trafik, hedef Pod'lardaki 80 numaralı porta yönlendirilir ve bu port kümedeki her Node'un 30082 numaralı portu üzerinden erişilebilir hale gelir.

**Özet:** Bu bölüm, WordPress uygulamasının birden fazla kopya (replica) ile nasıl çalıştırılacağını ve bir NodePort Service aracılığıyla dış dünyaya nasıl açılacağını tanımlar. Kalıcı veri saklama için `wordpress-pvc` kullanılır ve MariaDB Service'i üzerinden veritabanına bağlanır.

**Potansiyel Görsel:**

WordPress Deployment ve Service arasındaki ilişkiyi gösteren basit bir diyagram eklenebilir. Ayrıca, WordPress Pod'larının MariaDB Service'i üzerinden veritabanına nasıl bağlandığını gösteren bir görsel de faydalı olabilir.

---

## 6. phpMyAdmin - Veritabanı Yönetim Aracı

phpMyAdmin, web tabanlı bir MySQL ve MariaDB veritabanı yönetim aracıdır. Bu tanımlamalar, phpMyAdmin'in Kubernetes üzerinde nasıl çalıştırılacağını belirtir.

**YAML Tanımlamaları:**

~~~yaml
# --- phpMyAdmin ---
---
apiVersion: apps/v1
kind: Deployment
metadata:
  name: phpmyadmin
spec:
  replicas: 3
  selector:
    matchLabels:
      app: phpmyadmin
  template:
    metadata:
      labels:
        app: phpmyadmin
    spec:
      containers:
        - name: phpmyadmin
          image: phpmyadmin/phpmyadmin
          env:
            - name: PMA_HOST
              value: db
            - name: PMA_USER
              value: root
            - name: PMA_PASSWORD
              value: root_password
          resources:
            limits:
              cpu: "200m"
              memory: "256Mi"
            requests:
              cpu: "50m"
              memory: "64Mi"
          ports:
            - containerPort: 80
---
apiVersion: v1
kind: Service
metadata:
  name: phpmyadmin-service
spec:
  selector:
    app: phpmyadmin
  type: NodePort
  ports:
    - name: http
      port: 80
      targetPort: 80
      nodePort: 30083
~~~

**Açıklamalar:**

* **`Deployment` (phpMyAdmin):**
    * `apiVersion: apps/v1`: Apps API'sinin v1 versiyonu.
    * `kind: Deployment`: Bir Deployment kaynağı tanımlar.
    * `metadata: name: phpmyadmin`: `phpmyadmin` adında bir Deployment oluşturur.
    * `spec:`: Deployment'ın istenen durumunu tanımlar.
        * `replicas: 3`: Her zaman üç adet phpMyAdmin Pod'unun çalışır durumda olmasını sağlar.
        * `selector: matchLabels: app: phpmyadmin`: Etiketinde `app: phpmyadmin` bulunan Pod'ları seçer.
        * `template:`: Oluşturulacak Pod'ların şablonunu tanımlar.
            * `metadata: labels: app: phpmyadmin`: Oluşturulacak Pod'lara `app: phpmyadmin` etiketi ekler.
            * `spec:`: Pod'un özelliklerini tanımlar.
                * `containers:`: Pod içinde çalışacak container'ı tanımlar.
                    * `- name: phpmyadmin`: Container'ın adıdır.
                    * `image: phpmyadmin/phpmyadmin`: Kullanılacak Docker imajını belirtir (phpMyAdmin'in resmi imajı).
                    * `ports: - containerPort: 80`: Container içinde phpMyAdmin'in dinleyeceği standart HTTP portunu (80) tanımlar.
                    * `env:`: Container içinde ortam değişkenlerini tanımlar. Bu değişkenler phpMyAdmin'in MariaDB veritabanına bağlanması için kullanılır.
                        * `PMA_HOST: db`: Bağlanılacak MariaDB sunucusunun adresini belirtir (`db`, MariaDB Service'imizin adıdır).
                        * `PMA_USER: root`: MariaDB'ye bağlanmak için kullanılacak kullanıcı adını belirtir (bu örnekte root kullanıcısı).
                        * `PMA_PASSWORD: root_password`: MariaDB root kullanıcısının parolasını belirtir. **Bu parola, MariaDB Deployment'ında tanımlanan `MYSQL_ROOT_PASSWORD` ile aynı olmalıdır.**
                    * `resources:`: Container için kaynak sınırlarını ve taleplerini tanımlar.
                        * `limits: cpu: "200m", memory: "256Mi"`: Container'ın kullanabileceği maksimum CPU (200 miliCPU) ve bellek (256 MiB) miktarını belirtir.
                        * `requests: cpu: "50m", memory: "64Mi"`: Kubernetes'in bu container için garanti etmesi gereken minimum CPU (50 miliCPU) ve bellek (64 MiB) miktarını belirtir.

* **`Service` (phpMyAdmin):**
    * `apiVersion: v1`: Core API'nin v1 versiyonu.
    * `kind: Service`: Bir Service kaynağı tanımlar.
    * `metadata: name: phpmyadmin-service`: `phpmyadmin-service` adında bir Service oluşturur.
    * `spec:`: Service'in istenen durumunu tanımlar.
        * `selector: app: phpmyadmin`: Etiketinde `app: phpmyadmin` bulunan Pod'ları hedefleyen bir seçici tanımlar.
        * `type: NodePort`: Bu Service'in türünü NodePort olarak belirtir. phpMyAdmin'e dışarıdan erişmek için kullanılır.
        * `ports:`: Service'in dinleyeceği ve hedef Pod'lardaki container portlarına yönlendireceği portları tanımlar.
            * `- name: http, port: 80, targetPort: 80, nodePort: 30083`: `http` adında bir port tanımlar. Service üzerinde 80 numaralı porttan gelen trafik, hedef Pod'lardaki 80 numaralı porta yönlendirilir ve bu port kümedeki her Node'un 30083 numaralı portu üzerinden erişilebilir hale gelir.

**Özet:** Bu bölüm, phpMyAdmin uygulamasının birden fazla kopya ile nasıl çalıştırılacağını ve bir NodePort Service aracılığıyla dış dünyaya nasıl açılacağını tanımlar. MariaDB Service'i (`db`) üzerinden veritabanına bağlanarak veritabanı yönetimini sağlar.

**Potansiyel Görsel:**

phpMyAdmin Deployment ve Service arasındaki ilişkiyi gösteren basit bir diyagram eklenebilir. Ayrıca, phpMyAdmin'in MariaDB Service'i üzerinden veritabanına nasıl bağlandığını gösteren bir görsel de faydalı olabilir.

---