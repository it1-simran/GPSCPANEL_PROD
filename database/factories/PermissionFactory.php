<?php

namespace Database\Factories;

use App\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition()
    {
        $modules = ['device_management', 'account_management', 'settings_management', 'certificate_management'];
        $actions = ['view', 'edit', 'create', 'delete'];
        $module = $this->faker->randomElement($modules);
        $action = $this->faker->randomElement($actions);

        // Generate unique key using word and number
        $key = $this->faker->unique()->word . '.' . $this->faker->unique()->word;

        return [
            'key' => $key,
            'label' => ucfirst($action) . ' ' . ucfirst(str_replace('_', ' ', $module)),
            'description' => "Permission to {$action} in {$module}",
            'module' => $module,
            'action' => $action,
            'is_active' => 1,
            'order' => $this->faker->numberBetween(0, 10),
        ];
    }
}
