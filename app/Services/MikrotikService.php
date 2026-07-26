<?php

namespace App\Services;

use App\Models\MikrotikSetting;
use Illuminate\Support\Facades\Crypt;
use RouterOS\Client;
use RouterOS\Query;
use RuntimeException;
use Throwable;

/**
 * Pembungkus koneksi RouterOS API MikroTik.
 *
 * Semua operasi hotspot (user, profile, sesi aktif, resource) lewat sini.
 * Jika router tidak dapat dihubungi, method akan melempar RuntimeException
 * dengan pesan yang ramah untuk ditampilkan ke operator.
 */
class MikrotikService
{
    protected ?Client $client = null;
    protected ?MikrotikSetting $setting = null;

    public function __construct(?MikrotikSetting $setting = null)
    {
        $this->setting = $setting ?: MikrotikSetting::active();
    }

    /**
     * Apakah ada konfigurasi router aktif?
     */
    public function hasSetting(): bool
    {
        return $this->setting !== null;
    }

    public function setting(): ?MikrotikSetting
    {
        return $this->setting;
    }

    /**
     * Buat / ambil koneksi client RouterOS.
     */
    public function client(): Client
    {
        if ($this->client) {
            return $this->client;
        }

        if (! $this->setting) {
            throw new RuntimeException('Belum ada konfigurasi router MikroTik yang aktif. Silakan atur di menu Pengaturan.');
        }

        try {
            $this->client = new Client([
                'host'    => $this->setting->host,
                'user'    => $this->setting->username,
                'pass'    => $this->decryptPass($this->setting->password),
                'port'    => (int) $this->setting->port,
                'ssl'     => (bool) $this->setting->use_ssl,
                'timeout' => 8,         // timeout koneksi (detik)
                'socket_timeout' => 30, // timeout baca data (detik) — cegah "Stream timed out"
                'attempts' => 1,        // 1x agar dashboard tetap responsif saat router mati
                // Cegah "Stream timed out" palsu saat membaca respons (bug read parsial di library).
                'throw_timeout_exception' => false,
            ]);
        } catch (Throwable $e) {
            throw new RuntimeException('Gagal terhubung ke MikroTik ('.$this->setting->host.'): '.$e->getMessage());
        }

        return $this->client;
    }

    protected function decryptPass(?string $pass): string
    {
        if (empty($pass)) {
            return '';
        }
        // Password disimpan terenkripsi. Jika gagal (mis. plaintext lama), pakai apa adanya.
        try {
            return Crypt::decryptString($pass);
        } catch (Throwable $e) {
            return $pass;
        }
    }

    /**
     * Uji koneksi. Mengembalikan info identitas + resource, atau melempar exception.
     */
    public function testConnection(): array
    {
        $identity = $this->client()->query('/system/identity/print')->read();
        $resource = $this->client()->query('/system/resource/print')->read();

        if ($this->setting) {
            $this->setting->forceFill(['last_connected_at' => now()])->save();
        }

        return [
            'identity' => $identity[0]['name'] ?? '-',
            'resource' => $resource[0] ?? [],
        ];
    }

    /**
     * Info sumber daya router (CPU, memori, uptime, versi).
     */
    public function resource(): array
    {
        return $this->client()->query('/system/resource/print')->read()[0] ?? [];
    }

    public function identity(): string
    {
        return $this->client()->query('/system/identity/print')->read()[0]['name'] ?? '-';
    }

    // ==================== HOTSPOT USER ====================

    public function listHotspotUsers(): array
    {
        return $this->client()->query('/ip/hotspot/user/print')->read();
    }

    public function findHotspotUser(string $username): ?array
    {
        $query = (new Query('/ip/hotspot/user/print'))
            ->where('name', $username);
        $res = $this->client()->query($query)->read();
        return $res[0] ?? null;
    }

    /**
     * Tambah user hotspot. Mengembalikan .id record baru.
     */
    public function addHotspotUser(string $username, string $password, ?string $profile = null, ?string $comment = null, ?string $limitBytes = null): ?string
    {
        $query = (new Query('/ip/hotspot/user/add'))
            ->equal('name', $username)
            ->equal('password', $password);

        if ($profile) {
            $query->equal('profile', $profile);
        }
        if ($comment) {
            $query->equal('comment', $comment);
        }
        if ($limitBytes) {
            $query->equal('limit-bytes-total', $limitBytes);
        }

        $res = $this->client()->query($query)->read();
        return $res['after']['ret'] ?? ($res[0]['ret'] ?? null);
    }

    public function updateHotspotUser(string $mikrotikId, array $fields): void
    {
        $query = (new Query('/ip/hotspot/user/set'))->equal('.id', $mikrotikId);
        foreach ($fields as $key => $value) {
            $query->equal($key, $value);
        }
        $this->client()->query($query)->read();
    }

    public function setHotspotUserDisabled(string $mikrotikId, bool $disabled): void
    {
        $query = (new Query('/ip/hotspot/user/set'))
            ->equal('.id', $mikrotikId)
            ->equal('disabled', $disabled ? 'yes' : 'no');
        $this->client()->query($query)->read();
    }

    public function removeHotspotUser(string $mikrotikId): void
    {
        $query = (new Query('/ip/hotspot/user/remove'))->equal('.id', $mikrotikId);
        $this->client()->query($query)->read();
    }

    // ==================== SESI AKTIF ====================

    /**
     * Daftar user hotspot yang sedang online.
     */
    public function activeUsers(): array
    {
        return $this->client()->query('/ip/hotspot/active/print')->read();
    }

    /**
     * Putuskan sesi aktif berdasarkan .id.
     */
    public function disconnectActive(string $activeId): void
    {
        $query = (new Query('/ip/hotspot/active/remove'))->equal('.id', $activeId);
        $this->client()->query($query)->read();
    }

    // ==================== USER PROFILE (PAKET) ====================

    public function listProfiles(): array
    {
        return $this->client()->query('/ip/hotspot/user/profile/print')->read();
    }

    public function findProfile(string $name): ?array
    {
        $query = (new Query('/ip/hotspot/user/profile/print'))->where('name', $name);
        return $this->client()->query($query)->read()[0] ?? null;
    }

    /**
     * Buat/perbarui user-profile hotspot agar sesuai paket lokal.
     */
    public function upsertProfile(string $name, ?string $rateLimit = null, ?string $sessionTimeout = null, int $sharedUsers = 1): ?string
    {
        $existing = $this->findProfile($name);

        if ($existing) {
            $query = (new Query('/ip/hotspot/user/profile/set'))->equal('.id', $existing['.id']);
        } else {
            $query = (new Query('/ip/hotspot/user/profile/add'))->equal('name', $name);
        }

        $query->equal('shared-users', (string) max(1, $sharedUsers));
        if ($rateLimit) {
            $query->equal('rate-limit', $rateLimit);
        }
        if ($sessionTimeout) {
            $query->equal('session-timeout', $sessionTimeout);
        }

        $res = $this->client()->query($query)->read();
        return $res['after']['ret'] ?? ($existing['.id'] ?? null);
    }

    public function removeProfile(string $mikrotikId): void
    {
        $query = (new Query('/ip/hotspot/user/profile/remove'))->equal('.id', $mikrotikId);
        $this->client()->query($query)->read();
    }
}
