# Render – Environment Variables

Render Dashboard → **toyshop-app** → **Environment** → **Add Environment Variable** ile aşağıdakileri tek tek ekle.

---

## Kopyala-yapıştır (Key + Value)

Her satır için Render’da **Key** ve **Value** alanlarına yaz. Sadece `APP_URL` ve `MONGODB_URI` değerlerini kendininkilerle değiştir.

| Key | Value |
|-----|--------|
| **APP_ENV** | `production` |
| **APP_URL** | `https://toyshop-app.onrender.com` *(Render'ın verdiği canlı URL; sonda / olmasın)* |
| **MONGODB_DB** | `toyshop_app` |
| **MONGODB_URI** | `mongodb+srv://KULLANICI:SIFRE@cluster0.xxxxx.mongodb.net/?retryWrites=true&w=majority` *(Atlas'tan al; KULLANICI ve SIFRE'yi değiştir)* |
| **SESSION_NAME** | `toyshop_session` |

---

## Tek tek ekleme

1. **APP_ENV** → Key: `APP_ENV` → Value: `production`

2. **APP_URL** → Key: `APP_URL` → Value: `https://toyshop-app.onrender.com`  
   *(İlk deploy sonrası Render'ın verdiği URL'i yaz; sonda / olmasın.)*

3. **MONGODB_DB** → Key: `MONGODB_DB` → Value: `toyshop_app`

4. **MONGODB_URI** → Key: `MONGODB_URI` → Value: Atlas connection string (şifreyi kendi şifrenle değiştir)  
   Örnek: `mongodb+srv://salih:sifre123@cluster0.abc12.mongodb.net/?retryWrites=true&w=majority`

5. **SESSION_NAME** → Key: `SESSION_NAME` → Value: `toyshop_session`

---

**Not:** MongoDB Atlas → Network Access → Add IP → `0.0.0.0/0` ekle ki Render bağlanabilsin.
