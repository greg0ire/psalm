<?php
namespace Psalm\Plugin;

use Psalm\Codebase;
use Psalm\SourceControl\SourceControlInfo;

class SpiritGuide implements \Psalm\Plugin\Hook\AfterAnalysisInterface
{
    /**
     * Called after analysis is complete
     * @param array<int, array{severity: string, line_from: int, line_to: int, type: string, message: string,
     * file_name: string, file_path: string, snippet: string, from: int, to: int,
     * snippet_from: int, snippet_to: int, column_from: int, column_to: int, selected_text: string}> $issues
     *
     * @return void
     */
    public static function afterAnalysis(
        Codebase $codebase,
        array $issues,
        SourceControlInfo $source_control_info = null
    ) {
        if ($source_control_info instanceof \Psalm\SourceControl\Git\GitInfo) {
            $data = [
                'git' => $source_control_info->toArray(),
                'issues' => $issues,
            ];

            $payload = json_encode($data);

            // Prepare new cURL resource
            $ch = curl_init('https://spirit.psalm.dev/telemetry');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

            // Set HTTP Header for POST request
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload))
            );

            // Submit the POST request
            $result = curl_exec($ch);

            // Close cURL session handle
            curl_close($ch);
        }
    }
}
