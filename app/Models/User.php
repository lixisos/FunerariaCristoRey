<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Auditable, HasFactory, Notifiable, SoftDeletes;

    /** @var list<string>|null */
    private ?array $delegatedRoleSlugs = null;

    protected $fillable = [
        'rut', 'name', 'email', 'telefono', 'password',
        'rol_id', 'en_vacaciones', 'delegado_a_id', 'estado_usuario', 'tipo_ausencia', 'ausencia_hasta',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'en_vacaciones' => 'boolean',
            'ausencia_hasta' => 'date',
        ];
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function delegadoA(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegado_a_id');
    }

    /**
     * @param  list<string>  $slugs
     */
    public function tieneRol(array $slugs): bool
    {
        if ($this->rol && in_array($this->rol->slug, $slugs, true)) {
            return true;
        }

        if ($this->delegatedRoleSlugs === null) {
            $delegados = self::query()
                ->where('delegado_a_id', $this->id)
                ->where('en_vacaciones', true)
                ->with('rol')
                ->get();

            $this->delegatedRoleSlugs = $delegados
                ->map(fn (self $delegante): ?string => $delegante->rol?->slug)
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        return array_intersect($slugs, $this->delegatedRoleSlugs) !== [];
    }
}
