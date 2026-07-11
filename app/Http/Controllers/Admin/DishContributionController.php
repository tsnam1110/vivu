<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ContributionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateContributionStatusRequest;
use App\Http\Resources\DishContributionResource;
use App\Models\DishContribution;
use App\Services\DishContributionService;
use App\Support\AdminDateRange;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DishContributionController extends Controller
{
    public function __construct(
        private readonly DishContributionService $service,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = DishContribution::query()
            ->with(['dish', 'user'])
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        if ($request->filled('dish_id')) {
            $query->where('dish_id', $request->integer('dish_id'));
        }

        AdminDateRange::apply($query, $request);

        return DishContributionResource::collection($query->paginate(50));
    }

    public function updateStatus(
        UpdateContributionStatusRequest $request,
        DishContribution $dishContribution,
    ): DishContributionResource {
        $status = ContributionStatus::from($request->validated('status'));
        $admin = $request->user('admin');
        $note = $request->validated('review_note');
        $setCanonical = (bool) ($request->validated('set_canonical') ?? true);

        $row = match ($status) {
            ContributionStatus::Approved => $this->service->approve($dishContribution, $admin, $setCanonical, $note),
            ContributionStatus::Rejected => $this->service->reject($dishContribution, $admin, $note),
            ContributionStatus::Pending => tap($dishContribution, function (DishContribution $c) use ($note) {
                $c->update([
                    'status' => ContributionStatus::Pending,
                    'is_canonical' => false,
                    'review_note' => $note,
                    'reviewed_at' => null,
                    'reviewed_by' => null,
                ]);
            })->fresh(['dish', 'user']),
        };

        return new DishContributionResource($row);
    }
}
