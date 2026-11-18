<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---- Biodata (core of the platform) ----
        Schema::create('biodatas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('added_by', 20)->default('self'); // self|mother|father|sister|brother|other
            $t->string('status', 20)->default('draft');   // draft|pending|approved|rejected|paused|married
            $t->text('reject_reason')->nullable();
            $t->boolean('verified')->default(false);

            // personal
            $t->string('name');
            $t->string('gender', 10);                      // male|female
            $t->date('dob');
            $t->unsignedSmallInteger('height_cm')->nullable();
            $t->unsignedSmallInteger('weight_kg')->nullable();
            $t->string('complexion', 20)->nullable();
            $t->string('blood_group', 5)->nullable();
            $t->string('marital_status', 20)->default('never_married'); // never_married|divorced|widowed
            $t->boolean('manglik')->nullable();
            $t->string('disability')->nullable();
            $t->time('birth_time')->nullable();
            $t->string('birth_place')->nullable();

            // education & work
            $t->string('education')->nullable();
            $t->string('education_level', 30)->nullable(); // for filter: below_10|12|graduate|post_graduate|diploma|professional
            $t->string('occupation_type', 30)->nullable(); // govt|private|business|farming|student|not_working
            $t->string('occupation')->nullable();
            $t->string('company')->nullable();
            $t->string('income_range', 30)->nullable();
            $t->boolean('income_private')->default(true);

            // family
            $t->string('father_name')->nullable();
            $t->string('father_occupation')->nullable();
            $t->string('mother_name')->nullable();
            $t->string('mother_occupation')->nullable();
            $t->unsignedTinyInteger('brothers')->default(0);
            $t->unsignedTinyInteger('brothers_married')->default(0);
            $t->unsignedTinyInteger('sisters')->default(0);
            $t->unsignedTinyInteger('sisters_married')->default(0);
            $t->string('family_type', 20)->nullable();     // joint|nuclear
            $t->string('mama_gotra')->nullable();
            $t->string('mama_village')->nullable();

            // samaj & location
            $t->string('gotra')->nullable();
            $t->string('upjati')->nullable();
            $t->string('village')->nullable();
            $t->string('tehsil')->nullable();
            $t->string('district')->nullable();
            $t->string('state')->nullable();
            $t->string('current_city')->nullable();
            $t->string('area')->nullable();

            // about / expectation
            $t->text('about')->nullable();
            $t->text('expectation')->nullable();
            $t->unsignedTinyInteger('pref_age_min')->nullable();
            $t->unsignedTinyInteger('pref_age_max')->nullable();
            $t->string('pref_education_level', 30)->nullable();
            $t->string('pref_district')->nullable();

            // contact + privacy
            $t->string('contact_phone', 15)->nullable();   // shown ONLY after accepted interest
            $t->string('photo_privacy', 20)->default('members'); // members|blur_until_accepted|accepted_only
            $t->boolean('requests_to_parent')->default(false);

            $t->unsignedInteger('views')->default(0);
            $t->timestamp('approved_at')->nullable();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['status', 'gender']);
            $t->index(['district', 'area']);
            $t->index('dob');
        });

        Schema::create('biodata_photos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('biodata_id')->constrained()->cascadeOnDelete();
            $t->string('path');
            $t->boolean('is_primary')->default(false);
            $t->unsignedTinyInteger('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('interests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('from_biodata_id')->constrained('biodatas')->cascadeOnDelete();
            $t->foreignId('to_biodata_id')->constrained('biodatas')->cascadeOnDelete();
            $t->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
            $t->string('status', 20)->default('sent'); // sent|accepted|rejected|expired|withdrawn
            $t->string('message', 300)->nullable();
            $t->timestamp('responded_at')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();
            $t->unique(['from_biodata_id', 'to_biodata_id']);
        });

        Schema::create('shortlists', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('biodata_id')->constrained()->cascadeOnDelete();
            $t->timestamps();
            $t->unique(['user_id', 'biodata_id']);
        });

        // ---- Community feed ----
        Schema::create('posts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->text('body')->nullable();
            $t->string('image')->nullable();
            $t->string('visibility', 20)->default('members'); // members|city
            $t->string('status', 20)->default('visible');     // visible|hidden
            $t->unsignedInteger('likes_count')->default(0);
            $t->unsignedInteger('comments_count')->default(0);
            $t->timestamps();
            $t->index('created_at');
        });

        Schema::create('likes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('post_id')->constrained()->cascadeOnDelete();
            $t->timestamps();
            $t->unique(['user_id', 'post_id']);
        });

        Schema::create('comments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('post_id')->constrained()->cascadeOnDelete();
            $t->string('body', 1000);
            $t->timestamps();
        });

        // ---- Admin-managed content: news / poster / event ----
        Schema::create('news_items', function (Blueprint $t) {
            $t->id();
            $t->string('type', 20)->default('news'); // news|poster|event
            $t->string('title');
            $t->text('body')->nullable();
            $t->string('image')->nullable();
            $t->string('area')->nullable();           // null = for everyone (later: subadmin area)
            $t->dateTime('event_at')->nullable();
            $t->string('event_place')->nullable();
            $t->boolean('is_published')->default(true);
            $t->boolean('show_on_landing')->default(true);
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('success_stories', function (Blueprint $t) {
            $t->id();
            $t->string('couple_names');
            $t->text('story')->nullable();
            $t->string('image')->nullable();
            $t->boolean('is_published')->default(false);
            $t->timestamps();
        });

        Schema::create('reports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $t->string('target_type', 20); // post|biodata|user|comment
            $t->unsignedBigInteger('target_id');
            $t->string('reason', 500)->nullable();
            $t->string('status', 20)->default('open'); // open|resolved|dismissed
            $t->timestamps();
        });

        Schema::create('push_subscriptions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('endpoint', 500)->unique();
            $t->string('p256dh')->nullable();
            $t->string('auth')->nullable();
            $t->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('action', 60);
            $t->string('target_type', 30)->nullable();
            $t->unsignedBigInteger('target_id')->nullable();
            $t->text('note')->nullable();
            $t->timestamps();
        });

        Schema::create('settings', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->text('value')->nullable();
        });
    }

    public function down(): void
    {
        foreach (['settings','audit_logs','push_subscriptions','reports','success_stories','news_items','comments','likes','posts','shortlists','interests','biodata_photos','biodatas'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
