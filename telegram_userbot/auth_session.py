"""
Interactive Setup Script for Telegram MTProto Userbot
Generates a Telethon StringSession and saves it into .env
"""
import os
import re
import sys
from pathlib import Path

try:
    from telethon.sync import TelegramClient
    from telethon.sessions import StringSession
except ImportError:
    print("[ERROR] Telethon belum terinstall. Jalankan: pip install telethon")
    sys.exit(1)

def find_env_file():
    # Cek di root project (1 tingkat di atas telegram_userbot)
    current_dir = Path(__file__).resolve().parent
    env_root = current_dir.parent / ".env"
    if env_root.exists():
        return env_root
    env_example = current_dir.parent / ".env.example"
    if env_example.exists():
        return env_root # Buat file .env baru di root jika belum ada
    return current_dir / ".env"

def load_env_vars(env_path):
    env_vars = {}
    if env_path.exists():
        with open(env_path, "r", encoding="utf-8") as f:
            for line in f:
                line = line.strip()
                if not line or line.startswith("#"):
                    continue
                if "=" in line:
                    key, val = line.split("=", 1)
                    env_vars[key.strip()] = val.strip().strip("'\"")
    return env_vars

def update_env_file(env_path, updates):
    lines = []
    if env_path.exists():
        with open(env_path, "r", encoding="utf-8") as f:
            lines = f.readlines()
    else:
        # Jika belum ada, salin dari .env.example jika ada
        example_path = env_path.parent / ".env.example"
        if example_path.exists():
            with open(example_path, "r", encoding="utf-8") as f:
                lines = f.readlines()

    keys_to_update = set(updates.keys())
    new_lines = []
    for line in lines:
        matched_key = None
        for key in keys_to_update:
            if re.match(rf"^\s*{re.escape(key)}\s*=", line):
                matched_key = key
                break
        if matched_key:
            new_lines.append(f"{matched_key}={updates[matched_key]}\n")
            keys_to_update.remove(matched_key)
        else:
            new_lines.append(line)

    if new_lines and not new_lines[-1].endswith("\n"):
        new_lines.append("\n")

    # Tambahkan key yang belum ada
    if keys_to_update:
        new_lines.append("\n# Telegram MTProto (Userbot) Configuration\n")
        for key in sorted(keys_to_update):
            new_lines.append(f"{key}={updates[key]}\n")

    with open(env_path, "w", encoding="utf-8") as f:
        f.writelines(new_lines)
    print(f"[OK] Berhasil memperbarui konfigurasi di: {env_path.resolve()}")

def main():
    print("=" * 65)
    print(" TELEGRAM MTPROTO (USERBOT) - ONE-TIME LOGIN & SETUP ")
    print("=" * 65)
    print("Petunjuk:")
    print("1. Kunjungi https://my.telegram.org lalu login dengan akun Telegram Admin/Toko.")
    print("2. Pilih menu 'API development tools' lalu buat aplikasi untuk mendapatkan:")
    print("   - API ID (angka)")
    print("   - API HASH (string alfanumerik)")
    print("-" * 65)

    env_path = find_env_file()
    env_vars = load_env_vars(env_path)

    default_api_id = env_vars.get("TELEGRAM_API_ID", "")
    default_api_hash = env_vars.get("TELEGRAM_API_HASH", "")

    api_id_input = input(f"Masukkan Telegram API ID [{default_api_id}]: ").strip() or default_api_id
    api_hash_input = input(f"Masukkan Telegram API HASH [{default_api_hash}]: ").strip() or default_api_hash

    if not api_id_input or not api_hash_input:
        print("[ERROR] API ID dan API HASH wajib diisi!")
        sys.exit(1)

    try:
        api_id = int(api_id_input)
    except ValueError:
        print("[ERROR] API ID harus berupa angka bulat.")
        sys.exit(1)
    
    api_hash = api_hash_input

    print("\n[INFO] Menginisialisasi koneksi ke Telegram Client API...")
    # Gunakan StringSession kosong untuk login interaktif awal
    session = StringSession()
    
    with TelegramClient(session, api_id, api_hash) as client:
        print("[SUCCESS] Otentikasi Berhasil!")
        session_string = client.session.save()
        me = client.get_me()
        user_name = f"{me.first_name} {me.last_name or ''}".strip()
        print(f"[AKUN AKTIF] {user_name} (@{me.username or 'tanpa_username'}) | Phone: +{me.phone}")

        # Mengirim tes pesan ke Saved Messages
        try:
            client.send_message("me", "🔔 [HomeCare Service] Setup MTProto Userbot Telegram Berhasil Terkoneksi!")
            print("[INFO] Pesan uji coba telah dikirim ke 'Saved Messages' (Pesan Tersimpan) Anda.")
        except Exception as e:
            print(f"[WARN] Tidak dapat mengirim pesan uji coba: {e}")

        # Simpan ke .env
        updates = {
            "TELEGRAM_API_ID": str(api_id),
            "TELEGRAM_API_HASH": api_hash,
            "TELEGRAM_SESSION_STRING": session_string,
            "TELEGRAM_ADMIN_PHONE": f"+{me.phone}" if me.phone else ""
        }
        update_env_file(env_path, updates)

        print("\n" + "=" * 65)
        print("SETUP SELESAI!")
        print("Session string telah disimpan secara aman di file .env.")
        print("Sistem sekarang dapat mengirim notifikasi otomatis ke nomor HP pelanggan.")
        print("=" * 65)

if __name__ == "__main__":
    main()
