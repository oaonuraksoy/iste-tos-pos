=== İşte Tos Pos ===
Contributors: onuraksoy
Tags: woocommerce, payment gateway, isbank, netsypay, sanal pos, 3d pay, turkiye, payment, gateway, pos
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 8.3
Stable tag: 1.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
WC requires at least: 9.0
WC tested up to: 9.5

İş Bankası NetSpay altyapısı ile tam uyumlu, güvenli ve hızlı WooCommerce Sanal POS ödeme eklentisi.

== Description ==

**İşte Tos Pos**, WooCommerce mağazanız için İş Bankası NetSpay altyapısını kullanarak ödeme almanızı sağlayan profesyonel bir Sanal POS eklentisidir. 3D Pay (Gelişmiş Güvenlik) entegrasyonu ile hem satıcıyı hem de alıcıyı koruma altına alır.

= Temel Özellikler =
*   **İş Bankası NetSpay Entegrasyonu:** Doğrudan İş Bankası altyapısı üzerinden işlem yapın.
*   **3D Pay Güvenliği:** En yüksek güvenlik standartlarına sahip 3D Secure ödeme akışı.
*   **Kolay Kurulum:** Karmaşık ayarlar gerektirmeden hızlıca yayına alın.
*   **Hash Doğrulama:** İşlemlerin bütünlüğünü korumak için gelişmiş SHA-512 algoritması.
*   **WooCommerce 9.0+ Tam Uyumluluk:** En güncel WooCommerce sürümleri için optimize edilmiştir.
*   **Güvenli Yanıt İşleme:** Ödeme sonuçlarını güvenli bir endpoint üzerinden karşılar.

== Installation ==

1.  Eklentiyi WordPress yönetim panelinizdeki 'Eklentiler > Yeni Ekle' bölümünden yükleyin veya dosyaları `/wp-content/plugins/` dizinine yükleyin.
2.  'Eklentiler' sekmesinden eklentiyi etkinleştirin.
3.  'WooCommerce > Ayarlar > Ödemeler' sekmesine gidin.
4.  'İşte Tos Pos' seçeneğini bulun ve 'Yönet' butonuna tıklayın.
5.  İş Bankası NetSpay paneli üzerinden aldığınız Mağaza Numarası (Client ID), API Kullanıcı Adı, API Şifresi ve 3D Key bilgilerini girin.
6.  Değişiklikleri kaydedin ve mağazanızda ödeme almaya başlayın.

== Frequently Asked Questions ==

= SSL Sertifikası gerekli mi? =
Evet, Sanal POS işlemleri için sitenizde geçerli bir SSL sertifikası (HTTPS) bulunması zorunludur.

= Hangi PHP sürümleri destekleniyor? =
Eklenti, modern güvenlik ve performans standartları için PHP 8.3 ve üzerini gerektirir.

= Test ortamı var mı? =
Evet, eklenti ayarlarından 'Test Modu'nu aktif ederek denemeler yapabilirsiniz.

== Screenshots ==

1. Admin ayarlar ekranı.
2. Checkout (Ödeme) sayfasındaki görünüm.
3. Başarılı ödeme sonrası sipariş detayları.

== Changelog ==

= 1.2.1 =
*   SHA-512 Hash algoritması güncellendi.
*   WooCommerce 9.5 uyumluluğu doğrulandı.
*   Hata ayıklama (logging) sistemi geliştirildi.

= 1.0.0 =
*   İlk kararlı sürüm yayınlandı.

== Upgrade Notice ==

= 1.2.1 =
Güvenlik ve performans iyileştirmeleri için bu sürümü hemen güncelleyin.
