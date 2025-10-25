# TPS düşüşü — nedenler, etkileyen faktörler ve çözüm adımları

Bu dosya, sunucuda TPS (ticks per second) düştüğünde olası nedenleri, bunların nasıl tespit edileceğini ve uygulanabilir
çözümleri listeler. Amaç; yüksek oyuncu sayısı veya yoğun işlem altında sunucunun kararlı kalmasını sağlamaktır.


### Kritik (Hemen ele alınmalı)
- Aşırı CPU tüketen plugin'leri tespit et ve en ağır 3 plugin'i geçici olarak devre dışı bırak veya optimize et. Uzun çalışan plugin görevleri ana thread'i bloke eder.
- World/Entity ticklerinde meydana gelen patlamalar: toplu chunk yükleme, entitiy spawn/destroy operasyonları veya geniş area-işlemleri CPU/IO patlaması oluşturur.
- Senkron disk I/O (player veri kaydetme, region dosya yazma) TPS'i doğrudan düşürür — kritik yazma işlemlerini asenkron/batch hale getir.
### Yüksek (Kısa sürede konfigürasyonla toparlanabilir)
- `AsyncPool` ve worker tuning: worker sayısı ve worker başına bellek limiti donanıma göre ayarlanmalı. Yanlış konfigürasyon memory pressure veya thread-şişmesine yol açar.
- Ağ tarafı optimizasyonu: paket sıkışmasını önlemek için broadcast/packet batch'leme, paket throttling ve async compression kullan.
- Timings/profiling ile en büyük CPU/latency noktalarını belirle; özellikle sık güncellenen entity'ler ve GUI paketleri kontrol edilsin.
### Orta (Mimari/temizlik, düşük risk)
- Nesne yeniden kullanımı (object pooling) ve gereksiz alokasyonların azaltılması GC baskısını hafifletir.
- Yoğunluk sırasında log seviyesinin azaltılması (debug -> warn) ve gereksiz konsol/gösterim güncellemelerinin sınırlandırılması.

# TPS düşüşü — nedenler, etkileyen faktörler ve çözüm adımları

Bu doküman, sunucuda TPS (ticks per second) düştüğünde nedenleri hızlıca tespit edip düzeltmenize yardımcı olacak pratik adımlar içerir. Önceliklendirme, kısa açıklamalar ve ilgili kod noktalarıyla birlikte uygulanabilir öneriler sunar.

## Önceliklendirme (Kritik -> Yüksek -> Orta)

### Kritik (Hemen ele alınmalı)

- Aşırı CPU tüketen plugin'leri tespit edin; en ağır 3 eklentiyi geçici olarak devre dışı bırakın veya optimize edin. Uzun süren plugin görevleri ana thread'i bloke eder.
- World/Entity ticklerinde meydana gelen patlamalar: toplu chunk yükleme, entity spawn/destroy veya geniş bölge işlemleri CPU/IO patlaması yaratır.
- Senkron disk I/O (player veri kaydetme, region dosya yazma) TPS'i doğrudan düşürür — önemli yazma işlemlerini asenkron veya batch olarak yeniden düzenleyin.

### Yüksek (Kısa sürede konfigürasyonla toparlanabilir)

- `AsyncPool` ve worker tuning: worker sayısını ve worker başına bellek limitlerini donanıma göre ayarlayın. Yanlış konfigürasyon memory pressure veya thread şişmesine yol açabilir.
- Ağ tarafı optimizasyonu: paket batch'leme, paket throttling ve async compression ile paket sıkışmasını önleyin.
- Timings/profiling ile en büyük CPU/latency kaynaklarını belirleyin; sık güncellenen entity ve GUI paketlerine odaklanın.

### Orta (Mimari/temizlik, düşük risk)

- Nesne yeniden kullanımı (object pooling) ve gereksiz alokasyonların azaltılması GC baskısını hafifletir.
- Yoğun dönemlerde log seviyesini düşürün (debug -> warn) ve gereksiz konsol/görsel güncellemeleri sınırlayın.

## Hızlı eylem listesi (adım adım)

1) Timings & Profiling
   - `Timings` ve benzeri profil araçlarını aktif edin. 100/200/500 bot ile kısa yük testleri çalıştırarak en büyük CPU ve latency kaynaklarını tespit edin.
   - Profil çıktılarından: yavaş plugin yöntemleri, ana thread'i bloke eden senkron I/O ve yoğun world/entity tick yollarını listeleyin.

2) Plugin kontrolü (Kritik)
   - En ağır plugin'leri belirleyin; gerekirse geçici devre dışı bırakın veya geliştirici ile optimizasyon talep edin.
   - Plugin API'sinde uzun süren işleri scheduler ile asenkron hale getirin; ana thread üzerinde uzun döngüler çalıştırmayın.

---

## Acil müdahale (hemen uygulanabilecek adımlar)

Aşağıdaki adımlar kritik maddeler (CPU-tüketen pluginler, world/entity spike'ları, senkron disk I/O) için hızlı teşhis ve müdahale sağlar.

1) Hızlı profiler ile en ağır yolları yakala
   - Geçici olarak `src/debug/TickProfiler.php`'yi etkinleştir: sunucu başlatılmadan veya test ortamında, profiler'ı enable edin (örnek: 10ms eşik).
   - Kısa bir yük testi çalıştırın (ör. 100 bot, 2-5 dakika) ve `diagnostics/tick_profile.log` içindeki en uzun kayıtları inceleyin.

2) En ağır 3 plugin'i geçici olarak devre dışı bırak
   - Tespit sonrası `plugins/` klasöründe ilgili pluginleri geçici olarak taşıyın (örn. `plugins.disabled/`) ve sunucuyu yeniden başlatın.
   - Adımlar (Windows PowerShell örneği):

```powershell
# plugin dosyasını taşı
Move-Item -Path .\plugins\HeavyPlugin.phar -Destination .\plugins.disabled\
# sunucuyu restart et (örnek start script)
.\start.ps1
```

3) World/entity spike'larını azalt
   - Büyük area işlemlerini parçalayın (chunk işlemlerini batch'leyin), spawn patlaması olan mekanikleri geçici olarak kısıtlayın.
   - Eğer belirli world işlemleri (ör. geniş area-scan) tespit edildiyse, bunları scheduler aracılığıyla adım adım çalıştıracak şekilde değiştirin.

4) Senkron disk I/O'yu kısa vadede hafiflet
   - `tools/async_save_example.php`'deki örneği kullanarak player save'leri bufferlayın ve periyodik olarak flush edin.
   - Uzun vadede bu yaklaşımı `AsyncPool` veya arka plan worker'larla entegre edin.

---

Bu acil adımlar, sistem stabil olana kadar hızlı müdahale sağlar. Sonrasında performans iyileştirmeleri için `Timings` ve tam profil raporu üzerinden detaylı patch çalışması yapılmalıdır.

3) AsyncPool & Worker tuning (Yüksek)
   - Worker sayısı, worker bellek limiti ve görev tipleri (IO/CPU/Compress) ayrı havuzlarda değerlendirilmelidir.
   - Donanıma göre "cores-2" gibi heuristikleri test edin; memory pressure veya yüksek GC gözlemlerseniz worker sayısını azaltın.

4) Network optimizasyonu (Yüksek)
   - Paket hazırlama (batch) ve sıkıştırma eşiklerini test edip donanıma göre ayarlayın. Küçük paketleri ana thread'de, büyük/yoğun paketleri işçilere verin.
   - Broadcast'larda throttling / deduplama uygulayarak aynı hedefe sık tekrar gönderimi engelleyin.

5) Disk IO (Kritik)
   - Player veri kaydetmelerini buffer'layın; periyodik veya batch flush kullanın. Senkron write'ler TPS'i düşürür.
   - Bölgesel/level dosya yazımlarını batch'leyin; LevelDB/DatFile işlemlerinde atomic batch write tercih edin.

6) Küçük ama etkili değişiklikler (Orta)
   - Görsel/console güncellemelerini (ör. titleTick) varsayılan frekansta sınırlandırın.
   - Gereksiz debug loglarını devre dışı bırakın veya koşullu hale getirin.

## Ölçme ve test

- Her değişiklik sonrası kısa bir benchmark yapın: 100/200/500 bot ile TPS, latency ve heap memory ölçümlerini kaydedin.
- Timings ve hafif profiler sonuçlarını saklayın; değişikliklerin etkisini karşılaştırılabilir şekilde tutun.

## İlgili dosya & kontrol noktaları

- `src/Server.php`                 -> `tick()` içindeki işlerin sırası ve zaman ölçümleri
- `src/scheduler/AsyncPool.php`    -> işçi havuzu, task dispatch ve memory limitleri
- `src/network/`                   -> packet compress/broadcast/connection tick
- `src/world/`                     -> world tick, chunk IO, cache temizleme
- `src/player/DatFilePlayerDataProvider.php` -> player load/save IO
- `src/MemoryManager.php`         -> bellek basıncı ve tetikler

## Notlar / İletişim

- Bu doküman geliştiricilere hızlı yol gösterici amaçlıdır; isterseniz adım adım patch'ler hazırlayabilirim.
- Önce Timings + basit bir yük testi yapın; sonra en ağır 3 yolu raporlayın, ben ona göre öneri/patch atarım.
