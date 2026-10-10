<?php

namespace App\Models;

use App\Mail\ResetPasswordMail;
use Barzo\Password\Generator;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;


/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property int $role
 * @property string $password
 * @property bool $is_ignore_funds_sufficiency
 * @property bool $is_test
 */
#[OA\Schema(title: 'User', description: 'User model')]
class User extends Authenticatable implements \Illuminate\Contracts\Auth\CanResetPassword
{
    use HasFactory;
    use Notifiable;
    use CanResetPassword;

    const ROLE_USER = 0;
    const ROLE_ADMIN = 20;

    /**
     * Conversion rate from seconds to days
     */
    const K = 60 * 60 * 24;

    /**
     * If user was active during this period, then it is considered as active
     */
    const ACTIVE_DAYS_THRESHOLD = 14;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'locale',
        'is_ignore_funds_sufficiency'
    ];

    protected $casts = [
        'role' => 'integer',
        'is_test' => 'boolean',
        'is_ignore_funds_sufficiency' => 'boolean',
        'last_active_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'role'
    ];

    protected $dateFormat = 'U';

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format($this->dateFormat);
    }

    public static function boot(): void
    {
        parent::boot();

        static::created(function ($model): void {
            $model->createDefaultPortfolio();
        });
    }

    public function createDefaultPortfolio()
    {
        $p = new Portfolio([
            'user_id' => $this->id,
            'name' => __('My Portfolio'),
            'currency_code' => 'USD',
            'account_type' => Portfolio::TYPE_BROKER,
            'opened_at' => time(),
        ]);
        return $p->save();
    }

    public static function generatePassword()
    {
        if (config('app.env') == 'testing') {
            return 'test_password';
        }
        return Generator::generateEn(4, '-');
    }

    public function generateApiToken()
    {
        $token = Str::random(60);
        $token_hash = hash('sha256', $token);
        //$this->api_token = $token_hash;
        //$this->save();

        Cache::forever($token_hash, $this->id);

        return $token;
    }

    /**
     * User's portfolios objects
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *
     * @var Portfolio
     */
    #[OA\Property(title: 'portfolios', format: 'object')]
    public function portfolios()
    {
        return $this->hasMany(Portfolio::class);
    }

    public function customAssets()
    {
        return $this->hasMany(CustomAsset::class, 'user_id', 'id');
    }

    public static function roleLabels(): array
    {
        return [
            self::ROLE_USER => 'User',
            self::ROLE_ADMIN => 'Admin',
        ];
    }

    public function getRoleLabelAttribute()
    {
        return Arr::get(self::roleLabels(), $this->role);
    }

    public function getHashAttribute(): string
    {
        $id = base_convert($this->id, 10, 36);
        return $id . substr(md5($this->id.':'.$this->email.':'.date('Ymd')), 0, 6);
    }

    public static function parseHash($hash): array
    {
        $sum = substr($hash, -6);
        $id = base_convert(substr($hash, 0, strpos($hash, $sum)), 36, 10);
        return [
            'id' => $id,
            'sum' => $sum,
        ];
    }

    public static function findByHash($hash): object|false
    {
        $h = self::parseHash($hash);
        $user = User::find($h['id']);
        if ($user && !empty($user->hash) && $user->hash == $hash) {
            return $user;
        }
        return false;
    }

    /**
     * Scope Active users (active in last 14 days)
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where(
            DB::raw('UNIX_TIMESTAMP() - last_active_at'), '<',
            DB::raw(self::K.' * '.self::ACTIVE_DAYS_THRESHOLD)
        );
    }

    public function locale()
    {
        if (in_array($this->locale, config('app.locales_available'))) {
            return $this->locale;
        }

        return App::getLocale();
    }

    public function meData(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'is_test' => $this->is_test,
            'is_ignore_funds_sufficiency' => $this->is_ignore_funds_sufficiency,
            'locale' => $this->locale(),
            'role' => $this->getRoleLabelAttribute(),
        ];
    }

    /**
     * Overriding laravel Password broker method to send reset link email
     * @param string $token
     */
    public function sendPasswordResetNotification($token): void
    {
        Mail::send(new ResetPasswordMail($this, $token));
    }

    /**
     * Update last_active_at
     */
    public function touchLastActive(): void
    {
        if (!$this->last_active_at || $this->last_active_at->diffInMinutes(Carbon::now()) > 5) {
            $this->last_active_at = time();
            $this->save();
        }
    }

}
