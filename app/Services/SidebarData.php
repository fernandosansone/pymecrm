<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Models\User;

class SidebarData
{
    /**
     * Helper para activar o no un menú
     */
    protected function moduleEnabled(string $key): bool
    {
        return (bool) config("pymecrm.modules.$key", true);
    }

    /**
     * Devuelve data lista para la vista del sidebar:
     * - menu (items ya filtrados por permisos)
     * - overdueCount (badge atrasos agenda)
     * - userName / primaryRole
     */
    public function forUser(?User $user): array
    {
        if (!$user) {
            return [
                'menu' => [],
                'overdueCount' => 0,
                'userName' => null,
                'primaryRole' => null,
            ];
        }

        $overdueCount = $this->overdueCountFor($user);
        $primaryRole = $this->primaryRoleFor($user);

        return [
            'menu' => $this->menuFor($user, $overdueCount),
            'overdueCount' => (int) $overdueCount,
            'userName' => $user->name,
            'primaryRole' => $primaryRole,
        ];
    }

    /**
     * Construye items del menú, filtrados por permisos.
     * Cada item:
     *  - key: para active match
     *  - label
     *  - route: route name
     *  - icon: nombre para render en blade
     *  - badge: null|int|string
     *  - badgeVariant: "red"|"green"|"gray"
     */
    protected function menuFor(User $user, int $overdueCount): array
    {
        $crm = [];
        $admin = [];

        // --- CRM
        if ($this->moduleEnabled('dashboard') && $user->can('dashboard.view')) {
            $crm[] = $this->item('dashboard', 'Dashboard', 'dashboard', 'dashboard');
        }

        if ($this->moduleEnabled('agenda') && $user->can('agenda.view')) {
            $crm[] = $this->item(
                'agenda',
                'Agenda',
                'agenda.index',
                'calendar',
                $overdueCount,
                $overdueCount > 0 ? 'red' : 'green'
            );
        }

        if ($this->moduleEnabled('contacts') && $user->can('contacts.view')) {
            $crm[] = $this->item('contacts', 'Contactos', 'contacts.index', 'users');
        }

        if ($this->moduleEnabled('opportunities') && $user->can('opportunities.view')) {
            $crm[] = $this->item('opportunities', 'Oportunidades', 'opportunities.index', 'doc');
        }

        if ($this->moduleEnabled('reports') && $user->can('reports.view')) {
            $crm[] = $this->item('reports', 'Reportes', 'reports.commercial', 'chart', null, null, 'reports/commercial');
        }

        // --- Administración
        if ($this->moduleEnabled('users') && $user->can('users.view')) {
            $admin[] = $this->item('users', 'Usuarios', 'users.index', 'user');
        }

        if ($this->moduleEnabled('roles') && $user->can('roles.view')) {
            $admin[] = $this->item('roles', 'Roles', 'roles.index', 'layers');
        }

        if ($this->moduleEnabled('permissions') && $user->can('permissions.view')) {
            $admin[] = $this->item('permissions', 'Permisos', 'permissions.index', 'clock');
        }
        
        // Limpieza defensiva: quita items sin route/label
        $crm = array_values(array_filter($crm, fn($i) => !empty($i['route']) && !empty($i['label'])));
        $admin = array_values(array_filter($admin, fn($i) => !empty($i['route']) && !empty($i['label'])));

        $sections = [];

        if (!empty($crm)) {
            $sections[] = ['title' => 'CRM', 'items' => $crm];
        }

        // “Administración” desaparece si no hay permisos
        if (!empty($admin)) {
            $sections[] = ['title' => 'Administración', 'items' => $admin];
        }

        return $sections;
    }


    protected function item(
        string $key,
        string $label,
        string $route,
        string $icon,
        int|string|null $badge = null,
        ?string $badgeVariant = null,
        ?string $activePathPrefix = null
    ): array {
        return [
            'key' => $key,
            'label' => $label,
            'route' => $route,
            'icon' => $icon,
            'badge' => $badge,
            'badgeVariant' => $badgeVariant,
            // para request()->is() (si no se pasa, usa $key)
            'activePathPrefix' => $activePathPrefix ?? $key,
        ];
    }

    /**
     * Badge atrasos: oportunidades del usuario cuyo followup más reciente tiene next_contact_date < hoy.
     * Cache por usuario (60s).
     */
    protected function overdueCountFor(User $user): int
    {
        // Si no puede ver agenda, no tiene sentido calcularlo
        if (!$user->can('agenda.view')) {
            return 0;
        }

        $cacheKey = "sidebar_overdue_count_user_{$user->id}";

        return (int) Cache::remember($cacheKey, 60, function () use ($user) {
            $today = Carbon::today()->toDateString();

            // Subquery: último followup por oportunidad (MAX(contact_date))
            $latestFollowup = DB::table('opportunity_followups as f')
                ->select('f.opportunity_id', DB::raw('MAX(f.contact_date) as last_contact_date'))
                ->groupBy('f.opportunity_id');

            // Subquery: next_contact_date del followup más reciente
            $lastFollowupData = DB::table('opportunity_followups as f')
                ->joinSub($latestFollowup, 'lf', function ($join) {
                    $join->on('lf.opportunity_id', '=', 'f.opportunity_id')
                        ->on('lf.last_contact_date', '=', 'f.contact_date');
                })
                ->select('f.opportunity_id', 'f.next_contact_date');

            return DB::table('opportunities as o')
                ->leftJoinSub($lastFollowupData, 'lfd', function ($join) {
                    $join->on('lfd.opportunity_id', '=', 'o.id');
                })
                ->where('o.assigned_user_id', $user->id)
                ->whereNotNull('lfd.next_contact_date')
                ->whereDate('lfd.next_contact_date', '<', $today)
                ->count();
        });
    }

    /**
     * Rol "principal" a mostrar: el primero alfabéticamente (estable),
     * o si tenés un método User::primaryRole() lo toma.
     */
    protected function primaryRoleFor(User $user): ?string
    {
        if (method_exists($user, 'primaryRole')) {
            try {
                return $user->primaryRole();
            } catch (\Throwable $e) {
                // fallback
            }
        }

        // Evita cargar relación si no está; si está cargada, la usa.
        if ($user->relationLoaded('roles')) {
            return $user->roles->pluck('name')->sort()->first();
        }

        // Spatie: roles() relationship
        try {
            return $user->roles()->pluck('name')->sort()->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Opcional: para invalidar cache cuando se registra un followup.
     */
    public function forgetOverdueCacheFor(User $user): void
    {
        Cache::forget("sidebar_overdue_count_user_{$user->id}");
    }
}
