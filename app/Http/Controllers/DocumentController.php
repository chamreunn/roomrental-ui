<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function destroy($documentId)
    {
        try {
            $response = $this->api()->delete("v1/documents/{$documentId}");

            if (($response['status'] ?? null) === 'success') {
                return back()->with('success', __('document.delete_success'));
            }

            return back()->withErrors(['api' => $response['message'] ?? __('document.delete_failed')]);
        } catch (\Throwable $e) {

            // Default fallback
            $msg = __('document.delete_failed');

            // Try to extract JSON from exception message
            $raw = $e->getMessage();

            // Find first "{" and parse JSON after it
            $pos = strpos($raw, '{');
            if ($pos !== false) {
                $jsonPart = substr($raw, $pos);
                $data = json_decode($jsonPart, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $msg = $data['message'] ?? $msg;
                }
            }

            return back()->withErrors(['api' => $msg]);
        }
    }
}
