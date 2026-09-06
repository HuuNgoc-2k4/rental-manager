<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class BaseService
{
    const DEFAULT_PER_PAGE = 20;

    /** Các mức phân trang được phép — chặn client tự bơm per_page khổng lồ. */
    const ALLOWED_PER_PAGE = [20, 50, 100, 200];

    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @param Request $request
     * @return int
     */
    public function getPerPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', self::DEFAULT_PER_PAGE);

        if (!in_array($perPage, self::ALLOWED_PER_PAGE)) {
            $perPage = self::DEFAULT_PER_PAGE;
        }

        return $perPage;
    }

    /**
     * Chuyển input dạng `name_like`, `total_gte`... thành mảng điều kiện [[column, operator, value], ...].
     *
     * @param array $inputs
     * @return array
     */
    public function formatFilters(array $inputs = []): array
    {
        if (!$inputs) {
            return [];
        }

        $operators = [
            '_gte' => '>=',
            '_lte' => '<=',
            '_gt' => '>',
            '_lt' => '<',
            '_not' => '!=',
            '_like' => 'like'
        ];

        $filters = [];

        foreach ($inputs as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $matched = false;
            foreach ($operators as $suffix => $op) {
                if (str_ends_with($key, $suffix)) {
                    $column = str_replace($suffix, '', $key);
                    $filters[] = [$column, $op, ($op === 'like') ? "%$value%" : $value];
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                $filters[] = [$key, '=', $value];
            }
        }

        return $filters;
    }
}
