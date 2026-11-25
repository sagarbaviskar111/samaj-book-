<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use App\Services\InterestService;
use DomainException;
use Illuminate\Http\Request;

class InterestController extends Controller
{
    public function __construct(private InterestService $service) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $base = Interest::with(['fromBiodata.primaryPhoto', 'toBiodata.primaryPhoto', 'fromUser']);
        $counts = [
            'accepted' => Interest::where('status', 'accepted')->where(fn ($q) => $q->where('from_user_id', $user->id)->orWhere('to_user_id', $user->id))->count(),
            'received' => Interest::where('to_user_id', $user->id)->where('status', 'sent')->count(),
            'sent' => Interest::where('from_user_id', $user->id)->whereIn('status', ['sent', 'rejected', 'expired', 'withdrawn'])->count(),
        ];

        // Easy mode: accepted matches come first. Default tab = accepted, else received (if any waiting), else sent.
        $asked = $request->query('tab');
        $tab = in_array($asked, ['accepted', 'received', 'sent'], true)
            ? $asked
            : ($counts['accepted'] > 0 ? 'accepted' : ($counts['received'] > 0 ? 'received' : 'sent'));

        $query = match ($tab) {
            'received' => $base->where('to_user_id', $user->id)->whereIn('status', ['sent', 'rejected', 'expired']),
            'sent' => $base->where('from_user_id', $user->id)->where('status', '!=', 'accepted'),
            'accepted' => $base->where('status', 'accepted')->where(fn ($q) => $q->where('from_user_id', $user->id)->orWhere('to_user_id', $user->id)),
        };

        $helpIds = \App\Models\Report::where('reporter_id', $user->id)->where('target_type', 'interest_help')->pluck('target_id')->all();

        return view('search.interests', [
            'tab' => $tab,
            'interests' => $query->latest()->paginate(12)->withQueryString(),
            'counts' => $counts,
            'helpIds' => $helpIds,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'from_biodata_id' => ['required', 'integer'],
            'to_biodata_id' => ['required', 'integer'],
            'message' => ['nullable', 'string', 'max:300'],
        ]);

        try {
            $this->service->send($request->user(), (int) $data['from_biodata_id'], (int) $data['to_biodata_id'], $data['message'] ?? null);
        } catch (DomainException $e) {
            return $this->reply($request, false, $e->getMessage());
        }

        return $this->reply($request, true, __('पसंद भेज दी गई। 7 दिन में जवाब न आए तो यह अपने आप बंद हो जाएगी।'));
    }

    public function respond(Request $request, Interest $interest)
    {
        $data = $request->validate(['action' => ['required', 'in:accept,reject']]);
        abort_unless($interest->to_user_id === $request->user()->id, 403);

        try {
            $this->service->respond($request->user(), $interest, $data['action']);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('interests.index', ['tab' => $data['action'] === 'accept' ? 'accepted' : 'received'])
            ->with('status', $data['action'] === 'accept' ? __('रिश्ता मंज़ूर किया। अब संपर्क नंबर दिख रहा है।') : __('आपने मना कर दिया। यह रिश्ता बंद हो गया।'));
    }

    public function withdraw(Request $request, Interest $interest)
    {
        abort_unless($interest->from_user_id === $request->user()->id, 403);
        try {
            $this->service->withdraw($request->user(), $interest);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', __('आपकी पसंद वापस ले ली गई।'));
    }

    public function help(Request $request, Interest $interest)
    {
        abort_unless(in_array($request->user()->id, [$interest->from_user_id, $interest->to_user_id], true), 403);
        try {
            $this->service->requestHelp($request->user(), $interest);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', __('एडमिन को आपका संदेश मिल गया। वे जल्द आपसे संपर्क करेंगे।'));
    }

    private function reply(Request $request, bool $ok, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => $ok, 'message' => $message], $ok ? 200 : 422);
        }

        return back()->with($ok ? 'status' : 'error', $message);
    }
}
