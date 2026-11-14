"""
Telegram MTProto Notification Service (Userbot)
Mengirim notifikasi langsung ke nomor HP pelanggan tanpa membutuhkan bot /start.
"""

import os
import sys
import json
import re
import asyncio
from pathlib import Path

try:
    from telethon import TelegramClient
    from telethon.sessions import StringSession
    from telethon.tl.functions.contacts import ImportContactsRequest, DeleteContactsRequest
    from telethon.tl.types import InputPhoneContact
    from telethon.errors import (
        PhoneNumberInvalidError,
        UserPrivacyRestrictedError,
        FloodWaitError,
        SessionPasswordNeededError
    )
except ImportError:
    print(json.dumps({
        "success": False,
        "error_code": "LIBRARY_MISSING",
        "message": "Library Telethon belum terinstall. Jalankan: pip install telethon"
    }))
    sys.exit(1)


def load_env():
    """Load configuration variables from .env file"""
    env_vars = {}
    current_dir = Path(__file__).resolve().parent
    env_path = current_dir.parent / ".env"
    if not env_path.exists():
        env_path = current_dir / ".env"

    if env_path.exists():
        with open(env_path, "r", encoding="utf-8") as f:
            for line in f:
                line = line.strip()
                if not line or line.startswith("#"):
                    continue
                if "=" in line:
                    k, v = line.split("=", 1)
                    env_vars[k.strip()] = v.strip().strip("'\"")
    return env_vars


def clean_phone_number(raw_phone: str) -> str:
    """
    Format nomor HP ke standar internasional E.164 (khususnya Indonesia +62)
    Contoh:
    - 081234567890 -> +6281234567890
    - 6281234567890 -> +6281234567890
    - +6281234567890 -> +6281234567890
    - 81234567890 -> +6281234567890
    """
    if not raw_phone:
        return ""
    # Hapus spasi, strip, tanda kurung
    clean = re.sub(r"[^\d+]", "", str(raw_phone).strip())

    if clean.startswith("+"):
        return clean
    if clean.startswith("0"):
        return "+62" + clean[1:]
    if clean.startswith("62"):
        return "+" + clean
    if clean.startswith("8"):
        return "+62" + clean
    
    return "+" + clean


class TelegramMTProtoNotifier:
    def __init__(self):
        env = load_env()
        self.api_id = os.environ.get("TELEGRAM_API_ID") or env.get("TELEGRAM_API_ID")
        self.api_hash = os.environ.get("TELEGRAM_API_HASH") or env.get("TELEGRAM_API_HASH")
        self.session_string = os.environ.get("TELEGRAM_SESSION_STRING") or env.get("TELEGRAM_SESSION_STRING")

    def validate_credentials(self):
        if not self.api_id or not self.api_hash or not self.session_string:
            raise ValueError(
                "Konfigurasi Telegram MTProto belum lengkap di .env (TELEGRAM_API_ID, TELEGRAM_API_HASH, TELEGRAM_SESSION_STRING). "
                "Silakan jalankan 'python telegram_userbot/auth_session.py' terlebih dahulu."
            )

    async def _send_to_phone(self, phone: str, message: str, customer_name: str = "Pelanggan", cleanup_contact: bool = True) -> dict:
        """
        Kirim pesan langsung ke nomor HP target menggunakan MTProto.
        Mendeteksi secara akurat apakah nomor terdaftar di Telegram atau tidak.
        """
        self.validate_credentials()
        formatted_phone = clean_phone_number(phone)

        if not formatted_phone or len(formatted_phone) < 8:
            return {
                "success": False,
                "error_code": "INVALID_PHONE_FORMAT",
                "message": f"Nomor telepon '{phone}' tidak valid atau terlalu pendek.",
                "phone": phone
            }

        client = TelegramClient(StringSession(self.session_string), int(self.api_id), self.api_hash)
        
        try:
            await client.connect()
            if not await client.is_user_authorized():
                return {
                    "success": False,
                    "error_code": "SESSION_REVOKED",
                    "message": "Session string Telegram sudah tidak valid / ter-logout. Jalankan ulang setup: python telegram_userbot/auth_session.py"
                }

            # Import kontak sementara menggunakan Telegram MTProto
            contact = InputPhoneContact(
                client_id=0,
                phone=formatted_phone,
                first_name=customer_name or "Pelanggan",
                last_name=""
            )
            
            result = await client(ImportContactsRequest([contact]))

            # Cek apakah nomor tersebut terdaftar di Telegram
            if not result.users:
                return {
                    "success": False,
                    "error_code": "PHONE_NOT_REGISTERED",
                    "message": f"Nomor telepon {formatted_phone} tidak terdaftar di Telegram.",
                    "phone": formatted_phone
                }

            target_user = result.users[0]

            # Kirim pesan teks dengan format HTML
            sent_msg = await client.send_message(
                target_user,
                message,
                parse_mode="html",
                link_preview=False
            )

            # Opsional: Bersihkan kontak dari buku telepon akun admin agar tidak menumpuk
            if cleanup_contact:
                try:
                    await client(DeleteContactsRequest(id=[target_user.id]))
                except Exception:
                    pass

            return {
                "success": True,
                "message": f"Notifikasi berhasil dikirim ke Telegram (+{target_user.phone or formatted_phone})",
                "phone": formatted_phone,
                "telegram_user_id": target_user.id,
                "telegram_username": target_user.username,
                "message_id": sent_msg.id
            }

        except PhoneNumberInvalidError:
            return {
                "success": False,
                "error_code": "PHONE_INVALID",
                "message": f"Format nomor telepon {formatted_phone} ditolak oleh Telegram.",
                "phone": formatted_phone
            }
        except UserPrivacyRestrictedError:
            return {
                "success": False,
                "error_code": "PRIVACY_RESTRICTED",
                "message": f"Pengguna {formatted_phone} mengaktifkan batasan privasi sehingga tidak dapat menerima pesan dari nomor baru.",
                "phone": formatted_phone
            }
        except FloodWaitError as e:
            return {
                "success": False,
                "error_code": "FLOOD_WAIT",
                "message": f"Telegram Flood Wait limit: Harap tunggu {e.seconds} detik sebelum mengirim lagi.",
                "wait_seconds": e.seconds
            }
        except Exception as e:
            return {
                "success": False,
                "error_code": "MTPROTO_ERROR",
                "message": f"Gagal mengirim pesan via MTProto: {str(e)}"
            }
        finally:
            await client.disconnect()

    async def notify_order_created(self, data: dict) -> dict:
        """
        Kondisi 1: Pesanan Dibuat (notify_order_created)
        Konfirmasi ke pelanggan bahwa pesanan jasa peralatan rumah tangga telah diterima.
        """
        phone = data.get("phone") or data.get("customer_phone")
        customer_name = data.get("customer_name", "Pelanggan")
        order_id = data.get("order_id", "-")
        equipment_name = data.get("equipment_name", "Peralatan Rumah Tangga")
        complaint = data.get("complaint", "Pemeriksaan umum")
        service_date = data.get("service_date", "-")
        address = data.get("address", "-")

        message = (
            f"🛎 <b>PESANAN SERVIS DITERIMA</b> 🛎\n\n"
            f"Halo <b>{customer_name}</b>, pesanan jasa perbaikan peralatan rumah tangga Anda telah berhasil kami terima di <b>HomeCare Service</b>.\n\n"
            f"📋 <b>Detail Pesanan:</b>\n"
            f"• <b>No. Pesanan:</b> #ORD-{str(order_id).zfill(4)}\n"
            f"• <b>Unit Peralatan:</b> {equipment_name}\n"
            f"• <b>Keluhan / Kendala:</b> {complaint}\n"
            f"• <b>Rencana Waktu:</b> {service_date}\n"
            f"• <b>Alamat Servis:</b> {address}\n"
            f"• <b>Status:</b> 🟡 <i>Menunggu Penugasan Teknisi (PENDING)</i>\n\n"
            f"Tim teknisi kami akan segera memeriksa jadwal dan menuju lokasi Anda sesuai waktu yang ditentukan.\n\n"
            f"Terima kasih telah mempercayakan perbaikan peralatan Anda kepada kami! 🙏"
        )

        return await self._send_to_phone(phone, message, customer_name)

    async def notify_in_progress(self, data: dict) -> dict:
        """
        Kondisi 2: Sedang Diperbaiki (notify_in_progress)
        Mengabarkan bahwa barang (sebutkan nama unitnya) sedang dikerjakan oleh teknisi.
        """
        phone = data.get("phone") or data.get("customer_phone")
        customer_name = data.get("customer_name", "Pelanggan")
        order_id = data.get("order_id", "-")
        equipment_name = data.get("equipment_name", "Peralatan Rumah Tangga")
        technician_name = data.get("technician_name", "Teknisi")

        message = (
            f"⚙️ <b>PROSES PERBAIKAN SEDANG BERJALAN</b> ⚙️\n\n"
            f"Halo <b>{customer_name}</b>, kami menginformasikan bahwa unit peralatan Anda:\n\n"
            f"🔧 <b>Unit:</b> <b>{equipment_name}</b>\n"
            f"📋 <b>No. Pesanan:</b> #ORD-{str(order_id).zfill(4)}\n"
            f"👨‍🔧 <b>Teknisi Bertugas:</b> {technician_name}\n"
            f"📍 <b>Status:</b> 🔵 <b>SEDANG DIPERBAIKI (IN_PROGRESS)</b>\n\n"
            f"Teknisi kami saat ini sedang melakukan pembongkaran, diagnosa kerusakan, serta tindakan perbaikan secara teliti pada unit <b>{equipment_name}</b> Anda.\n\n"
            f"Kami akan segera mengirimkan konfirmasi rincian hasil pekerjaan dan total biaya setelah perbaikan selesai. Terima kasih atas kesabarannya! ⏱️"
        )

        return await self._send_to_phone(phone, message, customer_name)

    async def notify_completed(self, data: dict) -> dict:
        """
        Kondisi 3: Selesai Diperbaiki (notify_completed)
        Mengabarkan bahwa perbaikan selesai, barang siap diambil/dikirim, beserta rincian total biaya.
        """
        phone = data.get("phone") or data.get("customer_phone")
        customer_name = data.get("customer_name", "Pelanggan")
        order_id = data.get("order_id", "-")
        equipment_name = data.get("equipment_name", "Peralatan Rumah Tangga")
        technician_name = data.get("technician_name", "Teknisi")
        inspection_result = data.get("inspection_result", "Selesai diperiksa")
        repair_action = data.get("repair_action", "Telah diperbaiki dan diuji fungsi")
        notes = data.get("notes", "")
        
        # Format total biaya rupiah
        total_cost = data.get("total_cost") or data.get("cost") or 0
        try:
            cost_val = float(str(total_cost).replace(".", "").replace(",", "."))
            formatted_cost = f"Rp {cost_val:,.0f}".replace(",", ".")
        except Exception:
            formatted_cost = str(total_cost)

        notes_line = f"• <b>Catatan Khusus:</b> {notes}\n" if notes else ""

        message = (
            f"✅ <b>PERBAIKAN SELESAI & SIAP DIAMBIL / DIKIRIM</b> ✅\n\n"
            f"Kabar baik untuk <b>{customer_name}</b>! Perbaikan untuk unit <b>{equipment_name}</b> (Pesanan #ORD-{str(order_id).zfill(4)}) telah <b>BERHASIL DISELESAIKAN</b>. 🎉\n\n"
            f"📝 <b>Rincian Hasil Pengerjaan:</b>\n"
            f"• <b>Teknisi:</b> {technician_name}\n"
            f"• <b>Hasil Diagnosa:</b> {inspection_result}\n"
            f"• <b>Tindakan Perbaikan:</b> {repair_action}\n"
            f"{notes_line}\n"
            f"💰 <b>RINCIAN TOTAL BIAYA:</b>\n"
            f"👉 <b>Total Tagihan: {formatted_cost}</b>\n\n"
            f"📦 <b>Pengambilan / Pengiriman:</b>\n"
            f"Unit Anda telah selesai melalui uji fungsi dan siap diambil di lokasi kami atau siap dikirimkan kembali ke alamat Anda.\n\n"
            f"Terima kasih telah menggunakan jasa <b>HomeCare Service</b>! Semoga peralatan Anda kembali bekerja maksimal. 🙏✨"
        )

        return await self._send_to_phone(phone, message, customer_name)


async def main_cli():
    """
    Entry point CLI untuk dipanggil dari PHP / Subprocess
    Mendukung argument --action & --data atau --file atau --stdin
    """
    import argparse
    parser = argparse.ArgumentParser(description="Telegram MTProto Userbot Notification Bridge")
    parser.add_argument("--action", choices=["notify_order_created", "notify_in_progress", "notify_completed", "send_message"], help="Action to execute")
    parser.add_argument("--data", help="JSON data string")
    parser.add_argument("--file", help="Path to JSON data file")
    parser.add_argument("--stdin", action="store_true", help="Read payload JSON from STDIN")

    args = parser.parse_args()

    payload = {}
    action = args.action

    if args.stdin:
        try:
            stdin_raw = sys.stdin.read().strip()
            if stdin_raw:
                stdin_data = json.loads(stdin_raw)
                action = stdin_data.get("action", action)
                payload = stdin_data.get("data", stdin_data)
        except Exception as e:
            print(json.dumps({"success": False, "error_code": "INVALID_STDIN_JSON", "message": f"Error parsing STDIN JSON: {e}"}))
            sys.exit(1)
    elif args.file:
        try:
            with open(args.file, "r", encoding="utf-8") as f:
                file_data = json.load(f)
                action = file_data.get("action", action)
                payload = file_data.get("data", file_data)
        except Exception as e:
            print(json.dumps({"success": False, "error_code": "INVALID_FILE_JSON", "message": f"Error reading JSON file: {e}"}))
            sys.exit(1)
    elif args.data:
        try:
            payload = json.loads(args.data)
        except Exception as e:
            print(json.dumps({"success": False, "error_code": "INVALID_DATA_JSON", "message": f"Payload JSON tidak valid: {e}"}))
            sys.exit(1)

    if not action:
        print(json.dumps({"success": False, "error_code": "NO_ACTION", "message": "Argumen --action wajib ditentukan."}))
        sys.exit(1)

    notifier = TelegramMTProtoNotifier()

    try:
        if action == "notify_order_created":
            result = await notifier.notify_order_created(payload)
        elif action == "notify_in_progress":
            result = await notifier.notify_in_progress(payload)
        elif action == "notify_completed":
            result = await notifier.notify_completed(payload)
        elif action == "send_message":
            phone = payload.get("phone", "")
            msg = payload.get("message", "")
            name = payload.get("customer_name", "Pelanggan")
            result = await notifier._send_to_phone(phone, msg, name)
        else:
            result = {"success": False, "error_code": "UNKNOWN_ACTION", "message": f"Action '{action}' tidak dikenal."}

        print(json.dumps(result, ensure_ascii=False))

    except Exception as e:
        print(json.dumps({
            "success": False,
            "error_code": "UNHANDLED_EXCEPTION",
            "message": str(e)
        }, ensure_ascii=False))
        sys.exit(1)


if __name__ == "__main__":
    asyncio.run(main_cli())
