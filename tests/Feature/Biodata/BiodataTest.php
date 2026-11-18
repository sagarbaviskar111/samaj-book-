<?php

namespace Tests\Feature\Biodata;

use App\Models\Biodata;
use App\Models\BiodataPhoto;
use App\Models\Interest;
use App\Models\User;
use App\Support\BiodataAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BiodataTest extends TestCase
{
    use RefreshDatabase;

    private int $n = 0;

    protected function setUp(): void
    {
        parent::setUp();
        BiodataAccess::flush();
        $this->withoutVite();
        Storage::fake('public');
    }

    private function member(array $o = []): User
    {
        $this->n++;

        return User::factory()->create(array_merge(['phone' => '98000000'.str_pad((string) $this->n, 2, '0', STR_PAD_LEFT), 'status' => 'active', 'role' => 'user'], $o));
    }

    private function payload(array $o = []): array
    {
        return array_merge([
            'added_by' => 'self', 'name' => 'Asha Tester', 'gender' => 'female',
            'dob' => now()->subYears(24)->toDateString(), 'marital_status' => 'never_married',
            'contact_phone' => '9123456780', 'photo_privacy' => 'members',
            'brothers' => 1, 'brothers_married' => 0, 'sisters' => 0, 'sisters_married' => 0,
        ], $o);
    }

    private function image(string $name = 'p.jpg', int $kb = 50): UploadedFile
    {
        return UploadedFile::fake()->image($name, 800, 600)->size($kb);
    }

    public function test_create_pending_and_age_rules(): void
    {
        $u = $this->member();
        $this->actingAs($u)->post(route('biodata.store'), $this->payload())->assertRedirect();
        $b = Biodata::first();
        $this->assertSame('pending', $b->status);
        $this->assertSame($u->id, $b->user_id);

        $this->actingAs($u)->post(route('biodata.store'), $this->payload(['dob' => now()->subYears(17)->toDateString()]))->assertSessionHasErrors('dob');
        $this->actingAs($u)->post(route('biodata.store'), $this->payload(['gender' => 'male', 'dob' => now()->subYears(20)->toDateString()]))->assertSessionHasErrors('dob');
        $this->actingAs($u)->post(route('biodata.store'), $this->payload(['name' => '']))->assertSessionHasErrors('name');
        $this->actingAs($u)->post(route('biodata.store'), $this->payload(['brothers' => 1, 'brothers_married' => 3]))->assertSessionHasErrors('brothers_married');
        $this->assertSame(1, Biodata::count());
    }

    public function test_draft_allows_missing_phone(): void
    {
        $u = $this->member();
        $this->actingAs($u)->post(route('biodata.store'), $this->payload(['contact_phone' => '', 'save_draft' => 1]))->assertSessionDoesntHaveErrors();
        $this->assertSame('draft', Biodata::first()->status);
    }

    public function test_only_owner_can_edit(): void
    {
        $owner = $this->member();
        $other = $this->member();
        $b = Biodata::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)->get(route('biodata.edit', $b))->assertForbidden();
        $this->actingAs($other)->put(route('biodata.update', $b), $this->payload())->assertForbidden();
        $this->actingAs($owner)->get(route('biodata.edit', $b))->assertOk();
    }

    public function test_sensitive_edit_requeues_approved(): void
    {
        $owner = $this->member();
        $b = Biodata::factory()->create(['user_id' => $owner->id, 'name' => 'Same', 'dob' => now()->subYears(24)->toDateString()]);

        $this->actingAs($owner)->put(route('biodata.update', $b), $this->payload(['name' => 'Same', 'dob' => $b->dob->toDateString(), 'about' => 'hello']))->assertRedirect();
        $this->assertSame('approved', $b->fresh()->status);

        $this->actingAs($owner)->put(route('biodata.update', $b), $this->payload(['name' => 'Changed', 'dob' => $b->dob->toDateString()]))->assertRedirect();
        $this->assertSame('pending', $b->fresh()->status);
    }

    public function test_non_owner_cannot_see_pending_but_owner_and_admin_can(): void
    {
        $owner = $this->member();
        $b = Biodata::factory()->create(['user_id' => $owner->id, 'status' => 'pending']);

        $this->actingAs($this->member())->get(route('biodata.show', $b))->assertNotFound();
        $this->actingAs($owner)->get(route('biodata.show', $b))->assertOk();
        $this->actingAs($this->member(['role' => 'admin']))->get(route('biodata.show', $b))->assertOk();
    }

    public function test_contact_and_income_hidden_until_accepted_interest(): void
    {
        $owner = $this->member();
        $viewer = $this->member();
        $b = Biodata::factory()->create(['user_id' => $owner->id, 'contact_phone' => '9123456780']);
        $mine = Biodata::factory()->create(['user_id' => $viewer->id, 'gender' => 'male', 'contact_phone' => '9000000001']);

        $this->actingAs($viewer)->get(route('biodata.show', $b))->assertOk()->assertDontSee('9123456780')->assertSee('छिपी है');
        $this->assertSame(1, $b->fresh()->views);

        $this->actingAs($owner)->get(route('biodata.show', $b))->assertSee('9123456780');
        $this->assertSame(1, $b->fresh()->views, 'owner views are not counted');

        Interest::create(['from_biodata_id' => $mine->id, 'to_biodata_id' => $b->id, 'from_user_id' => $viewer->id, 'to_user_id' => $owner->id, 'status' => 'sent']);
        BiodataAccess::flush();
        $this->actingAs($viewer)->get(route('biodata.show', $b))->assertDontSee('9123456780');

        Interest::query()->update(['status' => 'accepted']);
        BiodataAccess::flush();
        $this->actingAs($viewer)->get(route('biodata.show', $b))->assertSee('9123456780');
        // accepted in the other direction as well
        $this->actingAs($owner)->get(route('biodata.show', $mine))->assertSee('9000000001');
    }

    public function test_photo_limit_and_primary(): void
    {
        $owner = $this->member();
        $b = Biodata::factory()->create(['user_id' => $owner->id, 'status' => 'pending']);

        $this->actingAs($owner)->post(route('biodata.photos.store', $b), ['photos' => [$this->image('a.jpg'), $this->image('b.jpg'), $this->image('c.jpg'), $this->image('d.jpg')]])->assertSessionDoesntHaveErrors();
        $this->actingAs($owner)->post(route('biodata.photos.store', $b), ['photos' => [$this->image('e.jpg'), $this->image('f.jpg')]])->assertSessionDoesntHaveErrors();
        $this->assertSame(6, $b->photos()->count());
        $this->assertSame(1, $b->photos()->where('is_primary', true)->count());

        $this->actingAs($owner)->post(route('biodata.photos.store', $b), ['photos' => [$this->image('g.jpg')]])->assertSessionHasErrors('photos');
        $this->assertSame(6, $b->photos()->count());

        $second = $b->photos()->where('is_primary', false)->first();
        $this->actingAs($owner)->post(route('biodata.photos.primary', $second))->assertRedirect();
        $this->assertTrue($second->fresh()->is_primary);
        $this->assertSame(1, $b->photos()->where('is_primary', true)->count());

        $this->actingAs($owner)->delete(route('biodata.photos.destroy', $second))->assertRedirect();
        $this->assertSame(5, $b->photos()->count());
        $this->assertSame(1, $b->photos()->where('is_primary', true)->count());
    }

    public function test_photo_rejects_non_image_and_oversize_and_other_users(): void
    {
        $owner = $this->member();
        $b = Biodata::factory()->create(['user_id' => $owner->id]);
        $this->actingAs($owner)->post(route('biodata.photos.store', $b), ['photos' => [UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')]])->assertSessionHasErrors('photos.0');
        $this->actingAs($owner)->post(route('biodata.photos.store', $b), ['photos' => [$this->image('big.jpg', 5000)]])->assertSessionHasErrors('photos.0');
        $this->actingAs($this->member())->post(route('biodata.photos.store', $b), ['photos' => [$this->image()]])->assertForbidden();
    }

    private function photoFor(Biodata $b): BiodataPhoto
    {
        $path = "biodata/{$b->id}/t.jpg";
        $im = imagecreatetruecolor(300, 400);
        imagefill($im, 0, 0, imagecolorallocate($im, 200, 50, 50));
        ob_start();
        imagejpeg($im);
        Storage::disk('public')->put($path, ob_get_clean());

        return $b->photos()->create(['path' => $path, 'is_primary' => true, 'sort' => 0]);
    }

    public function test_photo_privacy_blur_placeholder_and_clear(): void
    {
        $owner = $this->member();
        $viewer = $this->member();
        $b = Biodata::factory()->create(['user_id' => $owner->id, 'photo_privacy' => 'blur_until_accepted']);
        $p = $this->photoFor($b);

        $r = $this->actingAs($viewer)->get(route('biodata.photo', [$p, 'size' => 'thumb']));
        $r->assertOk();
        $this->assertSame('image/jpeg', $r->headers->get('Content-Type'));
        $this->assertStringContainsString('private', $r->headers->get('Cache-Control'));
        $this->assertFileExists(storage_path('app/private-cache/'.$p->id.'-blur-thumb-w-'.$viewer->id.'-'.($p->updated_at?->timestamp ?? 0).'.jpg'));

        $b->update(['photo_privacy' => 'accepted_only']);
        $this->actingAs($viewer)->get(route('biodata.photo', $p))->assertOk();
        $this->assertNotEmpty(glob(storage_path('app/private-cache/'.$p->id.'-placeholder-*')));

        $this->actingAs($owner)->get(route('biodata.photo', $p))->assertOk();
        $this->assertNotEmpty(glob(storage_path('app/private-cache/'.$p->id.'-clear-full-n-*')));

        $b->forceFill(['status' => 'pending'])->save();
        $this->actingAs($viewer)->get(route('biodata.photo', $p))->assertNotFound();

        array_map('unlink', glob(storage_path('app/private-cache/'.$p->id.'-*')));
    }

    public function test_pdf_requires_accepted_interest(): void
    {
        $owner = $this->member();
        $viewer = $this->member();
        $b = Biodata::factory()->create(['user_id' => $owner->id]);
        $mine = Biodata::factory()->create(['user_id' => $viewer->id, 'gender' => 'male']);

        $this->actingAs($viewer)->get(route('biodata.pdf', $b))->assertForbidden();
        $this->actingAs($owner)->get(route('biodata.pdf', $b))->assertOk()->assertHeader('content-type', 'application/pdf');

        Interest::create(['from_biodata_id' => $mine->id, 'to_biodata_id' => $b->id, 'from_user_id' => $viewer->id, 'to_user_id' => $owner->id, 'status' => 'accepted']);
        BiodataAccess::flush();
        $this->actingAs($viewer)->get(route('biodata.pdf', $b))->assertOk();
    }

    public function test_mine_status_actions_and_delete(): void
    {
        $owner = $this->member();
        $b = Biodata::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($owner)->get(route('biodata.mine'))->assertOk()->assertSee($b->name);
        $this->actingAs($owner)->post(route('biodata.status', $b), ['action' => 'pause']);
        $this->assertSame('paused', $b->fresh()->status);
        $this->actingAs($owner)->post(route('biodata.status', $b), ['action' => 'resume']);
        $this->assertSame('approved', $b->fresh()->status);
        $this->actingAs($owner)->post(route('biodata.status', $b), ['action' => 'married']);
        $this->assertSame('married', $b->fresh()->status);

        $this->actingAs($this->member())->delete(route('biodata.destroy', $b))->assertForbidden();
        $this->actingAs($owner)->delete(route('biodata.destroy', $b))->assertRedirect(route('biodata.mine'));
        $this->assertSame(0, Biodata::count());
    }

    public function test_create_page_renders(): void
    {
        $this->actingAs($this->member())->get(route('biodata.create'))->assertOk()->assertSee('व्यक्तिगत')->assertSee('contact_phone', false);
    }
}
