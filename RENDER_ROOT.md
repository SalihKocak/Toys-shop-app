# Render: "Dockerfile: no such file or directory" Çözümü

Render, repoyu clone ettiğinde **repo kökünde** (en üst dizinde) Dockerfile arıyor. Hata alıyorsan büyük ihtimalle Dockerfile bir **alt klasörde**.

## Ne yapmalısın?

### 1. GitHub’da yapıyı kontrol et

- https://github.com/SalihKocak/Toy-shop-app sayfasına git.
- Repo kökünde **Dockerfile** dosyasını görüyor musun?
  - **Görmüyorsan:** Listede bir **tek klasör** (örn. `ToyShop_App`, `Toy-shop-app` veya başka bir ad) görüyorsan ona tıkla.
  - İçinde `Dockerfile`, `composer.json`, `public`, `src` var mı bak. Varsa bu klasörün adı senin **Root Directory** değerin.

### 2. Render’da Root Directory’i ayarla

1. **Render Dashboard** → **toyshop-app** (Web Service) → **Settings**.
2. **Build & Deploy** bölümünde **Root Directory** alanını bul.
3. GitHub’da gördüğün, Dockerfile’ın içinde olduğu klasörün adını yaz (örn. `ToyShop_App`).
   - Sadece klasör adı yeterli, başında/sonunda `/` koyma.
4. **Save Changes** de.
5. **Manual Deploy** → **Deploy latest commit** ile tekrar dene.

### 3. Alternatif: Dockerfile gerçekten kökte mi?

Eğer GitHub’da repo açınca **hemen** Dockerfile görüyorsan (hiç klasöre tıklamadan), o zaman Root Directory’i **boş bırak**.  
Yine de hata alıyorsan:

- **Settings** → **Build & Deploy** → **Dockerfile path** (varsa) alanının boş veya `Dockerfile` olduğundan emin ol.
- Repo’yu tekrar push edip **Clear build cache & deploy** dene.

---

**Kısa özet:** Çoğu durumda sorun, projenin GitHub’da bir alt klasörde olmasıdır. Render’da **Root Directory**’e bu klasör adını yazınca Dockerfile bulunur ve build çalışır.
