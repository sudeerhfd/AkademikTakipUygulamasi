# 🎓 Akademik Takip Sistemi

PHP ve MySQL ile geliştirilmiş, öğrencilerin projelerini, görevlerini ve dosyalarını yönetebileceği güvenli bir web uygulaması.

---

## 📸 Ekran Görüntüleri

| Giriş Sayfası | Projelerim | Görev Takibi |
|---|---|---|
| E-posta + Şifre + Beni Hatırla | Kart görünümü + modal ekleme | Durum geçişleri (Beklemede → Devam → Tamamlandı) |

---

## ✨ Özellikler

- **Kayıt & Giriş** — E-posta/şifre ile hesap oluşturma, "Beni Hatırla" seçeneği
- **Proje Yönetimi** — Proje ekleme, silme, görev sayısı takibi
- **Görev Takibi** — Projeye bağlı görevler, son tarih, durum geçişleri
- **Dosya Yönetimi** — PDF/JPEG/PNG yükleme, güvenli indirme, proje ile ilişkilendirme
- **Profil Sayfası** — Ad/soyad güncelleme, açık/koyu tema seçimi, şifre değiştirme
- **Güvenli Çıkış** — CSRF korumalı oturum sonlandırma

---

## 🗂️ Dosya Yapısı

```
akademik_takip/
├── index.php              # Otomatik yönlendirme
├── register.php           # Kayıt sayfası
├── login.php              # Giriş sayfası
├── logout.php             # Güvenli çıkış
├── projects.php           # Proje yönetimi
├── tasks.php              # Görev takibi
├── files.php              # Dosya yükleme & indirme
├── profile.php            # Profil & şifre değiştirme
├── database.sql           # Veritabanı şeması
├── config/
│   └── db.php             # PDO bağlantı ayarları
├── includes/
│   ├── session.php        # Oturum, CSRF, sanitize yardımcıları
│   ├── header.php         # Navbar & HTML head
│   └── footer.php         # Bootstrap JS & kapanış
└── uploads/
    └── .htaccess          # Direkt erişim engeli
```

---

## 🗄️ Veritabanı Şeması

```sql
users    (id, ad_soyad, email, password, tema, created_at)
projects (id, user_id, baslik, aciklama, created_at)
tasks    (id, user_id, project_id, baslik, son_tarih, durum, created_at)
files    (id, user_id, project_id, orijinal_ad, kayitli_ad, boyut, mime_type, created_at)
```

Tablolar birbirine `FOREIGN KEY` ile bağlıdır; kullanıcı silindiğinde ilgili tüm veriler otomatik temizlenir (`ON DELETE CASCADE`).

---

## ⚙️ Kurulum

### Gereksinimler
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10+
- Apache (XAMPP / WAMP önerilir)

### Adımlar

**1. Projeyi indirin**
```bash
git clone https://github.com/kullanici-adi/akademik-takip.git
```
ya da ZIP olarak indirip `htdocs` altına çıkartın.

**2. Veritabanını oluşturun**

phpMyAdmin'i açın ve `database.sql` dosyasını import edin:
```
phpMyAdmin → Import → database.sql → Git
```
ya da terminal ile:
```bash
mysql -u root -p < database.sql
```

**3. Veritabanı bağlantısını ayarlayın**

`config/db.php` dosyasını açıp kendi bilgilerinizi girin:
```php
define('DB_USER', 'root');   // MySQL kullanıcı adı
define('DB_PASS', '');       // MySQL şifresi
```

**4. Klasörü doğru konuma taşıyın**
```
C:/xampp/htdocs/webProgramlama/Lab8/odev/
```

**5. Tarayıcıdan açın**
```
http://localhost/webProgramlama/Lab8/odev/
```

---

## 🔒 Güvenlik Önlemleri

| Tehdit | Uygulanan Önlem |
|---|---|
| SQL Injection | PDO Prepared Statements |
| XSS | `htmlspecialchars()` ile tüm çıktılar escape edilir |
| CSRF | Her formda token doğrulaması |
| Session Fixation | Girişte `session_regenerate_id(true)` |
| Oturum Zaman Aşımı | 30 dakika hareketsizlikte otomatik sonlandırma |
| Yetkisiz Dosya Erişimi | Kullanıcı yalnızca kendi dosyasını indirebilir |
| Zararlı Dosya Yükleme | MIME tipi + uzantı kontrolü (`mime_content_type()`) |
| Dosya İsmi Çakışması | `uniqid()` ile benzersiz dosya adı üretilir |
| Direkt Dosya Erişimi | `uploads/.htaccess` ile web üzerinden erişim engeli |
| Şifre Güvenliği | `password_hash()` / `password_verify()` (bcrypt) |

---

## 📋 Kullanım

### Kayıt & Giriş
1. `/register.php` sayfasından hesap oluşturun
2. `/login.php` ile giriş yapın; "Beni Hatırla" ile 30 günlük oturum açılır

### Proje Oluşturma
- **Projeler** menüsünden **+ Yeni Proje Ekle** butonuna tıklayın
- Başlık ve açıklama girerek kaydedin

### Görev Ekleme
- **Görevler** menüsünden **+ Yeni Görev** ile ekleyin
- Durumu **Beklemede → Devam Ediyor → Tamamlandı** olarak güncelleyin

### Dosya Yükleme
- **Dosyalar** menüsünden proje seçin ve PDF/JPEG/PNG yükleyin (maks. 5 MB)
- Yalnızca kendi yüklediğiniz dosyaları görebilir ve indirebilirsiniz

---

## 🛠️ Kullanılan Teknolojiler

- **Backend:** PHP 8, PDO (MySQL)
- **Frontend:** Bootstrap 5.3, Bootstrap Icons
- **Veritabanı:** MySQL / MariaDB
- **Sunucu:** Apache (XAMPP)

---

## 📄 Lisans

Bu proje eğitim amaçlı geliştirilmiştir.
