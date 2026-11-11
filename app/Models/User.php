<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    /**
     * Keep privileged fields (role, active and password-change flags) out of
     * generic mass assignment. They must be changed by authorized workflows.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function activityLogs(): \\Illuminate\\Database\\Eloquent\\Relations\\HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function stockMovements(): \\Illuminate\\Database\\Eloquent\\Relations\\HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function assignedConversations(): \\Illuminate\\Database\\Eloquent\\Relations\\HasMany
    {
        return $this->hasMany(Conversation::class, 'assigned_user_id');
    }

    public function sentMessages(): \\Illuminate\\Database\\Eloquent\\Relations\\HasMany
    {
        return $this->hasMany(Message::class, 'user_sender_id');
    }

    public function responsibleOrders(): \\Illuminate\\Database\\Eloquent\\Relations\\HasMany
    {
        return $this->hasMany(Order::class, 'responsible_user_id');
    }

    public function orderStatusChanges(): \\Illuminate\\Database\\Eloquent\\Relations\\HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function createdProformas(): \\Illuminate\\Database\\Eloquent\\Relations\\HasMany
    {
        return $this->hasMany(Proforma::class, 'created_by');
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'active' => 'boolean',
            'must_change_password' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }
}
