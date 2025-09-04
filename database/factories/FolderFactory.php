<?php

namespace Database\Factories;

use App\Models\Folder;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Folder>
 */
class FolderFactory extends Factory
{
    protected $model = Folder::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'parent_id' => null,
            'path' => '',
            'level' => 0,
            'created_by' => User::inRandomOrder()->first()->id ?? User::factory(),
            'unit_id' => Unit::inRandomOrder()->first()->id ?? Unit::factory(),
            'is_active' => true
        ];
    }

    public function withParent(Folder $parent): static
    {
        return $this->state(function (array $attributes) use ($parent) {
            return [
                'parent_id' => $parent->id,
                'level' => $parent->level + 1,
                'unit_id' => $parent->unit_id,
                'path' => $parent->path ? $parent->path . '/' . $parent->id : (string)$parent->id
            ];
        });
    }

    public function shared(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'unit_id' => null
            ];
        });
    }

    public function withChildren(int $depth = 1, int $childrenPerLevel = 2): static
    {
        return $this->afterCreating(function (Folder $folder) use ($depth, $childrenPerLevel) {
            if ($depth > 0) {
                $this->createFolderChildren($folder, $depth, $childrenPerLevel);
            }
        });
    }

    // Ganti nama method untuk menghindari conflict
    protected function createFolderChildren(Folder $parent, int $depth, int $childrenPerLevel): void
    {
        for ($i = 0; $i < $childrenPerLevel; $i++) {
            $child = Folder::factory()->withParent($parent)->create();

            if ($depth > 1) {
                $this->createFolderChildren($child, $depth - 1, $childrenPerLevel);
            }
        }
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Folder $folder) {
            if ($folder->parent_id === null) {
                $folder->path = '';
                $folder->level = 0;
            }
        });
    }
}
