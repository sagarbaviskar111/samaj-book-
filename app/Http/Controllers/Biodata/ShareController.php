<?php

namespace App\Http\Controllers\Biodata;

use App\Http\Controllers\Controller;
use App\Models\Biodata;
use App\Support\BiodataAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Share link = a deep link to the normal biodata page. It is NOT a public page: the viewer must
 * be logged in and admin-approved, and the usual lite/full/photo-privacy rules apply afterwards.
 */
class ShareController extends Controller
{
    public const LOGIN_HINT = 'यह बायोडाटा देखने के लिए लॉगिन करें। नए सदस्यों को एडमिन की मंज़ूरी के बाद पूरा इस्तेमाल मिलता है।';

    /** Public entry point GET /b/{token}. Never renders personal data. */
    public function open(Request $request, string $token)
    {
        $biodata = strlen($token) <= 24
            ? Biodata::where('share_token', $token)->first(['id', 'user_id', 'status', 'share_enabled'])
            : null;

        if (! $biodata || ! $biodata->share_enabled || $biodata->status !== 'approved') {
            return response()->view('biodata.shared-unavailable', [], 404);
        }

        $target = route('biodata.show', $biodata->id);
        $user = $request->user();

        if (! $user) {
            $request->session()->put('url.intended', $target);

            return $this->redirectWithPreview(route('login'), ['status' => self::LOGIN_HINT, 'share_hint' => self::LOGIN_HINT]);
        }

        if ($user->isAdmin() || $user->isActive()) {
            return $this->redirectWithPreview($target);
        }
        if ($user->isPending()) {
            return $this->redirectWithPreview(route('home'), ['pending_notice' => 'एडमिन की मंज़ूरी बाकी है']);
        }

        return $this->redirectWithPreview(route('account.status'));
    }

    /** Owner: JSON with the link (token created lazily). */
    public function show(Request $request, Biodata $biodata): JsonResponse
    {
        $this->authorizeOwner($request, $biodata);
        abort_unless($biodata->status === 'approved', 422, 'Share is available once the biodata is approved.');

        $biodata->ensureShareToken();

        return $this->payload($biodata);
    }

    public function reset(Request $request, Biodata $biodata): JsonResponse
    {
        $this->authorizeOwner($request, $biodata);
        abort_unless($biodata->status === 'approved', 422);

        $biodata->ensureShareToken(true);
        $biodata->forceFill(['share_enabled' => true])->save();

        return $this->payload($biodata);
    }

    public function toggle(Request $request, Biodata $biodata): JsonResponse
    {
        $this->authorizeOwner($request, $biodata);
        abort_unless($biodata->status === 'approved', 422);

        $biodata->ensureShareToken();
        $biodata->forceFill(['share_enabled' => ! $biodata->share_enabled])->save();

        return $this->payload($biodata);
    }

    protected function authorizeOwner(Request $request, Biodata $biodata): void
    {
        abort_unless(BiodataAccess::canManage($request->user(), $biodata), 403);
    }

    protected function payload(Biodata $b): JsonResponse
    {
        $url = $b->shareUrl();

        return response()->json([
            'url' => $url,
            'enabled' => (bool) $b->share_enabled,
            'message' => "नमस्ते, ये मेरा/हमारा बायोडाटा है. देखने के लिए SamajBook पर login करें: {$url}",
            'pdf' => route('biodata.pdf', $b),
        ]);
    }

    /** 302 whose body is a tiny HTML with only generic Open Graph tags (no name/photo/age). */
    protected function redirectWithPreview(string $to, array $flash = [])
    {
        $response = redirect()->to($to)->with($flash);
        $response->setContent(view('biodata.shared-redirect', ['to' => $to])->render());
        $response->headers->set('Content-Type', 'text/html; charset=UTF-8');

        return $response;
    }
}
