<?php

function apiResponse($status = true, $statusCode = 200, $data = [])
{
    $statusMsg = $status == true ? 'success' : 'error';
    $dataType = $status == true ? 'data' : 'errors';

    return response()->json([
        'status' => $statusMsg,
        $dataType => $data,
    ], $statusCode);

}

function uploadFile($path, $file)
{
    $extension = strtolower($file->getClientOriginalExtension());
    $name = time() . rand(100, 999) . '.' . $extension;
    return $file->move('uploads/' . $path, $name);
}

function deleteFile($file)
{
    if (file_exists($file)) {
        unlink($file);
    }
}

function pagination($data)
{
    return [
        'total' => $data->total(),
        'count' => $data->count(),
        'per_page' => $data->perPage(),
        'current_page' => $data->currentPage(),
        'total_pages' => $data->lastPage(),
    ];
}

function limit($limit = 10)
{
    return $limit;
}