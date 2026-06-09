<?php

namespace App\Traits\Api;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

trait ApiResponder
{
    public function successResponse($data, $message, $code)
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
            'success' => true,
        ], $code);
    }

    public function dataResponseWithoutMessage($data, $success, $code)
    {
        return response()->json([
            'data' => $data,
            'success' => $success,
        ], $code);
    }

    public function dataResponse($data, $message, $success, $code)
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
            'success' => $success,
        ], $code);
    }

    protected function errorResponse($message, $code)
    {
        return response()->json(['data' => ['error' => $message, 'code' => $code, 'success' => false]], $code);
    }

    protected function showAll(Collection $collection, $code = 200)
    {
        if ($collection->isEmpty()) {
            return $this->successResponse(['data' => $collection], $code);
        }

        $transformer = $collection->first()->allItems;

        // $collection = $this->likeStringData($collection, $transformer);

        $collection = $this->filterData($collection, $transformer);

        $collection = $this->sortData($collection, $transformer);

        if (request()->per_page > 0) {
            $collection = $this->paginate($collection);
        }

        $collection = $this->transformData($collection, $transformer);
        $collection = $this->cacheResponse($collection);

        return $collection;
    }

    protected function showOne(Model $instance, $code = 200)
    {
        $transformer = $instance->oneItem;
        $instance = $this->transformData($instance, $transformer);

        return $instance;
    }

    protected function showOneWithMessage(Model $instance, $message, $code = 200)
    {
        $transformer = $instance->oneItem;

        $instance = $this->transformData($instance, $transformer);

        return $this->successResponse([
            'message' => $message,
            'data' => $instance,
        ], $code);
    }

    protected function showMessage($message, $code = 200)
    {
        return $this->successResponse(['data' => ['message' => $message, 'success' => true]], $code);
    }
    protected function paginate(Collection $collection)
    {
        $page = LengthAwarePaginator::resolveCurrentPage();

        $perPage = 15;

        if (request()->has('per_page')) {
            $perPage = (int) request()->per_page;
        }

        $result = $collection->slice(($page - 1) * $perPage, $perPage)->values();

        $paginated = new LengthAwarePaginator($result, $collection->count(), $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        $paginated->appends(request()->all());

        return $paginated;
    }

    protected function transformData($data, $transformer)
    {
        return new $transformer($data);
    }

    protected function cacheResponse($data)
    {
        $url = request()->url();
        $queryParams = request()->query();
        ksort($queryParams);
        $queryString = http_build_query($queryParams);
        $fullUrl = "{$url}?{$queryString}";

        return Cache::remember($fullUrl, 30 / 60, function () use ($data) {
            return $data;
        });
    }
}
