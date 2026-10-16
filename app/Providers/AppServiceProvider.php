<?php

namespace App\Providers;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Material;
use App\Models\Submission;
use App\Observers\AssignmentObserver;
use App\Observers\CourseObserver;
use App\Observers\MaterialObserver;
use App\Observers\SubmissionObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {

        Schema::defaultStringLength(191);

        
        Model::shouldBeStrict(! $this->app->isProduction());

        // Penghapusan berkas fisik mengikuti siklus hidup record.
        Material::observe(MaterialObserver::class);
        Submission::observe(SubmissionObserver::class);
        Assignment::observe(AssignmentObserver::class);
        Course::observe(CourseObserver::class);

        
        Blade::if('role', function (string ...$roles) {
            $user = auth()->user();

            return $user && in_array($user->role, $roles, true);
        });
    }
}