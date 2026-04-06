# İşte Tos Pos - WooCommerce İş Bankası NetSpay Gateway

[![WordPress v6.0+](https://img.shields.io/badge/WordPress-v6.0%2B-blue.svg)](https://wordpress.org/)
[![WooCommerce v9.0+](https://img.shields.io/badge/WooCommerce-v9.0%2B-purple.svg)](https://woocommerce.com/)
[![PHP v8.3+](https://img.shields.io/badge/PHP-v8.3%2B-777bb4.svg)](https://secure.php.net/)
[![License: GPL-2.0-or-later](https://img.shields.io/badge/License-GPL--2.0--or--later-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

**İşte Tos Pos**, WooCommerce tabanlı e-ticaret siteleri için İş Bankası NetSpay altyapısını kullanarak güvenli, hızlı ve sorunsuz ödeme almanızı sağlayan profesyonel bir Sanal POS eklentisidir.

## 🚀 Öne Çıkan Özellikler

*   **NetSpay 3D Pay Entegrasyonu:** İş Bankası'nın en güncel ödeme teknolojisi ile tam uyumluluk.
*   **SHA-512 Güvenlik Mimarisi:** İşlemlerin güvenliğini sağlamak için yüksek güçlü hashing algoritmaları.
*   **Hızlı ve Kolay Kurulum:** Karmaşık konfigürasyonlara gerek kalmadan dakikalar içinde aktif edilebilir.
*   **Modern Teknoloji:** PHP 8.3 standartlarına uygun, temiz ve sürdürülebilir kod yapısı.
*   **Gelişmiş Hata Yönetimi:** Ödeme süreçlerindeki sorunları tespit etmek için detaylı loglama ve kullanıcı bildirimleri.

## 📋 Gereksinimler

Eklentinin düzgün çalışması için aşağıdaki minimum gereksinimlerin karşılanması gerekir:

*   **WordPress:** 6.0 veya daha yeni bir sürüm.
*   **WooCommerce:** 9.0 veya daha yeni bir sürüm.
*   **PHP:** 8.3 veya üzeri.
*   **SSL Sertifikası:** Sitenizde HTTPS protokolü aktif olmalıdır.

## 🛠️ Kurulum

1.  Bu depoyu (repository) indirin veya `.zip` formatında paketleyin.
2.  WordPress yönetim panelinde `Eklentiler > Yeni Ekle > Eklenti Yükle` adımlarını izleyerek dosyayı yükleyin.
3.  Eklentiyi etkinleştirin.
4.  `WooCommerce > Ayarlar > Ödemeler` sekmesinden **İşte Tos Pos** ayarlarını yapılandırın.

## ⚙️ Konfigürasyon

Ayarlar sayfasında aşağıdaki alanları İş Bankası NetSpay panelinden aldığınız bilgilerle doldurun:

*   **Mağaza Numarası (Client ID)**
*   **API Kullanıcı Adı**
*   **API Şifresi**
*   **3D Key**

## 🔒 Güvenlik

Bu eklenti, ödeme verilerini doğrudan İş Bankası sunucularına iletir ve WordPress veritabanında kart verisi saklamaz. 3D Secure akışı ile müşteri onayı olmadan işlem yapılmasını engeller.

## 🤝 Katkıda Bulunma

Hata bildirimleri, özellik talepleri veya kod katkıları için bir "Issue" açabilir veya "Pull Request" gönderebilirsiniz. Katkılarınızdan memnuniyet duyarız!

## 📄 Lisans

Bu proje **GPLv2** veya daha sonraki sürümleri ile lisanslanmıştır. Daha fazla bilgi için [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html) dosyasına göz atabilirsiniz.

---

**Geliştirici:** [Onur AKSOY](https://onuraksoy.com.tr)  
*Güvenli ve hızlı ödeme çözümleri için tasarlandı.*
