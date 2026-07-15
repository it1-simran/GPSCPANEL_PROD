<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Shared engine for server-side DataTables endpoints.
 *
 * Parses the standard DataTables request (draw/start/length/search/order),
 * applies whitelisted sorting and LIKE search to the given base query, pages
 * the ids first (late row lookup) and renders one page of rows to cell arrays.
 * No layer ever materializes more than one page of data.
 */
class ServerSideTable
{
    /**
     * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $baseQuery
     *         Scoped/filtered query WITHOUT select/order/limit.
     * @param  array  $options
     *   - idColumn      string   qualified PK, e.g. 'templates.id'
     *   - searchColumns string[] columns matched with LIKE by the global search box
     *   - sortable      array    [headerColumnIndex => qualified SQL column]
     *   - defaultOrder  array    optional [[column, dir], ...] used when no order requested
     *   - columnFilters array    optional [headerColumnIndex => fn($query, string $value)]
     *                            applied from DataTables per-column search values
     *   - fetchRows     callable (int[] $ids) => iterable of row objects (any order, keyed by ->id)
     *   - renderRow     callable ($row, int $srNo) => string[] cells
     */
    public static function respond(Request $request, $baseQuery, array $options)
    {
        $draw = (int) $request->input('draw', 1);
        $start = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 25);
        $length = ($length > 0) ? min($length, 500) : 25;

        $recordsTotal = (clone $baseQuery)->count();

        $filtered = false;

        $searchValue = trim((string) $request->input('search.value', ''));
        if ($searchValue !== '' && !empty($options['searchColumns'])) {
            $like = '%' . addcslashes($searchValue, '%_\\') . '%';
            $baseQuery->where(function ($q) use ($like, $options) {
                foreach (array_values($options['searchColumns']) as $i => $col) {
                    $i === 0 ? $q->where($col, 'like', $like) : $q->orWhere($col, 'like', $like);
                }
            });
            $filtered = true;
        }

        foreach ($options['columnFilters'] ?? [] as $idx => $applier) {
            $value = trim((string) $request->input('columns.' . $idx . '.search.value', ''));
            if ($value !== '') {
                $applier($baseQuery, $value);
                $filtered = true;
            }
        }

        $recordsFiltered = $filtered ? (clone $baseQuery)->count() : $recordsTotal;

        $sortable = $options['sortable'] ?? [];
        $orderColIndex = $request->input('order.0.column');
        $orderDir = strtolower((string) $request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        if ($orderColIndex !== null && isset($sortable[(int) $orderColIndex])) {
            $baseQuery->orderBy($sortable[(int) $orderColIndex], $orderDir);
        } elseif (!empty($options['defaultOrder'])) {
            foreach ($options['defaultOrder'] as $order) {
                $baseQuery->orderBy($order[0], $order[1] ?? 'asc');
            }
        }
        $baseQuery->orderBy($options['idColumn']); // deterministic paging tiebreaker

        $pageIds = (clone $baseQuery)->offset($start)->limit($length)->pluck($options['idColumn']);

        $data = [];
        if ($pageIds->isNotEmpty()) {
            $rowsById = collect(call_user_func($options['fetchRows'], $pageIds->all()))->keyBy('id');
            $srNo = $start;
            foreach ($pageIds as $id) {
                $row = $rowsById->get($id);
                if (!$row) {
                    continue;
                }
                $srNo++;
                $data[] = call_user_func($options['renderRow'], $row, $srNo);
            }
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    /** HTML-escape a value for direct concatenation into cell markup. */
    public static function e($value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
