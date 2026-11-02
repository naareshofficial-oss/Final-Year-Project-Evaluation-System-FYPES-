<?php
/**
 * FYPES student completion certificate and lecturer invitation-letter renderer.
 * Uses PHP GD's built-in bitmap font so the project does not depend on or bundle font files.
 */

function gdIsAvailable(): bool {
    return extension_loaded('gd') && function_exists('imagecreatefrompng');
}


function certificateXmlEscape(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

function certificateSvgText(string $text, int $x, int $y, int $fontSize, string $fill = '#1e1b18', int $maxWidth = 0, string $weight = '600'): string {
    $text = trim(preg_replace('/\s+/', ' ', $text));
    if ($text === '') return '';
    $attrs = '';
    $charCount = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
    $estimatedWidth = $charCount * $fontSize * 0.58;
    if ($maxWidth > 0 && $estimatedWidth > $maxWidth) {
        $attrs = ' textLength="' . $maxWidth . '" lengthAdjust="spacingAndGlyphs"';
    }
    return '<text x="' . $x . '" y="' . $y . '" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="' . $fontSize . '" font-weight="' . certificateXmlEscape($weight) . '" fill="' . certificateXmlEscape($fill) . '"' . $attrs . '>' . certificateXmlEscape($text) . '</text>';
}

function certificateSvgWrappedLines(string $text, int $maxChars = 74, int $maxLines = 2): array {
    $words = preg_split('/\s+/', trim($text));
    if (!$words) return [];
    $lines = [];
    $line = '';
    foreach ($words as $word) {
        $candidate = $line === '' ? $word : $line . ' ' . $word;
        if (strlen($candidate) > $maxChars && $line !== '') {
            $lines[] = $line;
            $line = $word;
            if (count($lines) >= $maxLines - 1) break;
        } else {
            $line = $candidate;
        }
    }
    if ($line !== '' && count($lines) < $maxLines) $lines[] = $line;
    return $lines;
}

function streamCertificateSvgFallback(string $templateKey, string $name, ?string $grade, ?string $roleLabel, string $filename, array $details = []): void {
    $baseDir = __DIR__ . '/../assets/certificates/';
    $templates = [
        'invitation' => $baseDir . 'tpl_invitation.png',
        'completion' => $baseDir . 'tpl_completion.png',
    ];
    if (!isset($templates[$templateKey]) || !is_file($templates[$templateKey])) {
        http_response_code(404);
        echo 'Document template not found.';
        return;
    }

    $background = base64_encode((string)file_get_contents($templates[$templateKey]));
    $elements = '<image href="data:image/png;base64,' . $background . '" x="0" y="0" width="2000" height="1414"/>';

    if ($templateKey === 'invitation') {
        $elements .= '<rect x="665" y="650" width="1250" height="575" fill="#ffffff"/>';
        $elements .= certificateSvgText('THIS INVITATION LETTER IS ISSUED TO', 1290, 725, 28, '#56534f', 1000, '500');
        $elements .= certificateSvgText($name, 1290, 805, 52, '#1e1b18', 1080, '700');
        $elements .= '<line x1="700" y1="830" x2="1880" y2="830" stroke="#cdcdcd" stroke-width="2"/>';
        $elements .= certificateSvgText('APPOINTED AS ' . strtoupper((string)$roleLabel), 1290, 915, 38, '#1e1b18', 1040, '700');
        $elements .= certificateSvgText(programmeTypeLabel((string)($details['programme_type'] ?? '')), 1290, 978, 28, '#56534f', 900, '500');
        $elements .= certificateSvgText(APP_FULL_NAME, 1290, 1038, 25, '#56534f', 950, '500');
        $elements .= certificateSvgText('Reference: ' . ($details['code'] ?? ''), 1290, 1100, 23, '#56534f', 850, '500');
        $elements .= certificateSvgText('Issued: ' . ($details['issued_date'] ?? date('d M Y')), 1290, 1150, 22, '#56534f', 700, '500');
    } elseif ($templateKey === 'completion') {
        $elements .= certificateSvgText($name, 1000, 620, 56, '#1e1b18', 1200, '700');
        $identity = trim(($details['matric_number'] ?? '') . '  |  ' . programmeTypeLabel((string)($details['programme_type'] ?? '')), " |\t\n\r\0\x0B");
        $elements .= certificateSvgText($identity, 1000, 690, 25, '#56534f', 1000, '500');
        if (!empty($details['project_title'])) {
            foreach (certificateSvgWrappedLines('Project: ' . $details['project_title'], 74, 2) as $i => $line) {
                $elements .= certificateSvgText($line, 1000, 750 + ($i * 34), 23, '#56534f', 1050, '500');
            }
        }
        $elements .= certificateSvgText('Certificate No: ' . ($details['code'] ?? ''), 1000, 850, 21, '#56534f', 800, '500');
        if ($grade) $elements .= certificateSvgText(strtoupper($grade), 1700, 1190, 42, '#f58220', 190, '700');
    }

    $svg = '<?xml version="1.0" encoding="UTF-8"?>'
        . '<svg xmlns="http://www.w3.org/2000/svg" width="2000" height="1414" viewBox="0 0 2000 1414">'
        . $elements . '</svg>';

    $safeFilename = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename) ?: 'fypes_document';
    header('Content-Type: image/svg+xml; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');
    header('Content-Disposition: inline; filename="' . $safeFilename . '.svg"');
    echo $svg;
}

function certDrawScaledLine($image, string $text, int $centerX, int $topY, int $height, int $maxWidth, int $color): void {
    $text = trim(preg_replace('/\s+/', ' ', $text));
    if ($text === '') return;
    $font = 5;
    $sourceWidth = max(1, imagefontwidth($font) * strlen($text));
    $sourceHeight = imagefontheight($font);
    $targetHeight = max(12, $height);
    $targetWidth = (int)round($sourceWidth * ($targetHeight / $sourceHeight));
    if ($targetWidth > $maxWidth) {
        $ratio = $maxWidth / $targetWidth;
        $targetWidth = $maxWidth;
        $targetHeight = max(12, (int)round($targetHeight * $ratio));
    }

    $tmp = imagecreatetruecolor($sourceWidth + 2, $sourceHeight + 2);
    imagealphablending($tmp, false);
    imagesavealpha($tmp, true);
    $transparent = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
    imagefilledrectangle($tmp, 0, 0, imagesx($tmp), imagesy($tmp), $transparent);
    $r = ($color >> 16) & 0xFF;
    $g = ($color >> 8) & 0xFF;
    $b = $color & 0xFF;
    $tmpColor = imagecolorallocatealpha($tmp, $r, $g, $b, 0);
    imagestring($tmp, $font, 1, 1, $text, $tmpColor);

    imagealphablending($image, true);
    $x = (int)round($centerX - ($targetWidth / 2));
    imagecopyresampled($image, $tmp, $x, $topY, 0, 0, $targetWidth, $targetHeight, $sourceWidth + 2, $sourceHeight + 2);
    imagedestroy($tmp);
}

function certDrawWrappedText($image, string $text, int $centerX, int $topY, int $height, int $maxWidth, int $color, int $maxLines = 2): void {
    $words = preg_split('/\s+/', trim($text));
    if (!$words) return;
    $lines = [];
    $line = '';
    $approxChars = max(12, (int)floor($maxWidth / max(8, $height * 0.56)));
    foreach ($words as $word) {
        $candidate = $line === '' ? $word : $line . ' ' . $word;
        if (strlen($candidate) > $approxChars && $line !== '') {
            $lines[] = $line;
            $line = $word;
            if (count($lines) >= $maxLines - 1) break;
        } else {
            $line = $candidate;
        }
    }
    if ($line !== '' && count($lines) < $maxLines) $lines[] = $line;
    foreach ($lines as $i => $value) certDrawScaledLine($image, $value, $centerX, $topY + $i * ($height + 10), $height, $maxWidth, $color);
}

function renderCertificateImage(string $templateKey, string $name, ?string $grade = null, ?string $roleLabel = null, array $details = []) {
    if (!gdIsAvailable()) return 'GD_MISSING';

    $baseDir = __DIR__ . '/../assets/certificates/';
    $templates = [
        'invitation' => $baseDir . 'tpl_invitation.png',
        'completion' => $baseDir . 'tpl_completion.png',
    ];
    if (!isset($templates[$templateKey]) || !is_file($templates[$templateKey])) return null;

    $im = @imagecreatefrompng($templates[$templateKey]);
    if (!$im) return null;
    imagesavealpha($im, true);
    imagealphablending($im, true);

    $black = imagecolorallocate($im, 30, 27, 24);
    $darkGrey = imagecolorallocate($im, 86, 83, 79);
    $orange = imagecolorallocate($im, 245, 130, 32);
    $white = imagecolorallocate($im, 255, 255, 255);
    $lineGrey = imagecolorallocate($im, 205, 205, 205);

    if ($templateKey === 'invitation') {
        // Remove the sample name, sample paragraph and hard-coded role from the source artwork.
        imagefilledrectangle($im, 665, 650, 1915, 1225, $white);
        certDrawScaledLine($im, 'THIS INVITATION LETTER IS ISSUED TO', 1290, 690, 28, 1000, $darkGrey);
        certDrawScaledLine($im, $name, 1290, 750, 52, 1080, $black);
        imageline($im, 700, 830, 1880, 830, $lineGrey);
        certDrawScaledLine($im, 'APPOINTED AS ' . strtoupper((string)$roleLabel), 1290, 875, 38, 1040, $black);
        certDrawScaledLine($im, programmeTypeLabel((string)($details['programme_type'] ?? '')), 1290, 940, 28, 900, $darkGrey);
        certDrawScaledLine($im, APP_FULL_NAME, 1290, 1005, 25, 950, $darkGrey);
        certDrawScaledLine($im, 'Reference: ' . ($details['code'] ?? ''), 1290, 1075, 23, 850, $darkGrey);
        certDrawScaledLine($im, 'Issued: ' . ($details['issued_date'] ?? date('d M Y')), 1290, 1125, 22, 700, $darkGrey);
    } elseif ($templateKey === 'completion') {
        certDrawScaledLine($im, $name, 1000, 570, 56, 1200, $black);
        $identity = trim(($details['matric_number'] ?? '') . '  |  ' . programmeTypeLabel((string)($details['programme_type'] ?? '')), " |\t\n\r\0\x0B");
        certDrawScaledLine($im, $identity, 1000, 660, 25, 1000, $darkGrey);
        if (!empty($details['project_title'])) certDrawWrappedText($im, 'Project: ' . $details['project_title'], 1000, 715, 23, 1050, $darkGrey, 2);
        certDrawScaledLine($im, 'Certificate No: ' . ($details['code'] ?? ''), 1000, 830, 21, 800, $darkGrey);
        if ($grade) certDrawScaledLine($im, strtoupper($grade), 1700, 1140, 42, 190, $orange);
    }

    return $im;
}

function streamCertificate(string $templateKey, string $name, ?string $grade, ?string $roleLabel, string $filename, array $details = []): void {
    $details['code'] = $details['code'] ?? $filename;
    if ($templateKey === 'invitation') {
        $letterType = (string)($details['letter_type'] ?? letterTypeForRole((string)($details['role_type'] ?? '')));
        streamRoleLetterPdf($name, (string)$roleLabel, $letterType, $filename, $details);
        return;
    }
    if ($templateKey === 'completion') {
        streamCompletionCertificatePdf($name, (string)$grade, $filename, $details);
        return;
    }
    http_response_code(400);
    echo 'Unsupported document type.';
}

/**
 * Dependency-free PDF helpers. They use the built-in PDF Helvetica fonts, so
 * no bundled fonts, PHP GD, Imagick, or third-party libraries are required.
 */
function fypesPdfTextBytes(string $text): string {
    $converted = function_exists('iconv') ? @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text) : $text;
    return $converted === false ? preg_replace('/[^\x20-\x7E]/', '', $text) : $converted;
}

function fypesPdfEscape(string $text): string {
    $text = fypesPdfTextBytes($text);
    $text = str_replace(['\\','(',')'], ['\\\\','\\(','\\)'], $text);
    return preg_replace_callback('/[\x00-\x1F\x7F-\xFF]/', fn($m) => sprintf('\\%03o', ord($m[0])), $text);
}

function fypesPdfTextCommand(string $text, float $x, float $y, float $size, bool $bold=false, string $align='left', float $pageWidth=842): string {
    $text = trim(preg_replace('/\s+/', ' ', $text));
    if ($text === '') return '';
    $estimated = strlen(fypesPdfTextBytes($text)) * $size * 0.5;
    if ($align === 'center') $x = max(28, ($pageWidth - $estimated) / 2);
    elseif ($align === 'right') $x = max(28, $x - $estimated);
    return "BT /" . ($bold ? 'F2' : 'F1') . " " . number_format($size,2,'.','') . " Tf " . number_format($x,2,'.','') . " " . number_format($y,2,'.','') . " Td (" . fypesPdfEscape($text) . ") Tj ET\n";
}

function fypesPdfWrap(string $text, int $maxChars=78, int $maxLines=3): array {
    $words = preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY);
    $lines = [];
    $line = '';
    foreach ($words as $word) {
        $candidate = $line === '' ? $word : $line . ' ' . $word;
        if (strlen($candidate) > $maxChars && $line !== '') {
            $lines[] = $line;
            $line = $word;
            if (count($lines) >= $maxLines - 1) break;
        } else {
            $line = $candidate;
        }
    }
    if ($line !== '' && count($lines) < $maxLines) $lines[] = $line;
    return $lines;
}

function fypesStreamPdf(array $objects, string $filename): void {
    $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
    $offsets = [0];
    foreach ($objects as $number => $body) {
        $offsets[$number] = strlen($pdf);
        $pdf .= $number . " 0 obj\n" . $body . "\nendobj\n";
    }
    $xref = strlen($pdf);
    $count = max(array_keys($objects)) + 1;
    $pdf .= "xref\n0 $count\n0000000000 65535 f \n";
    for ($i=1; $i<$count; $i++) $pdf .= sprintf('%010d 00000 n ', (int)($offsets[$i] ?? 0)) . "\n";
    $pdf .= "trailer\n<< /Size $count /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
    $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename) ?: 'fypes_document';
    header('Content-Type: application/pdf');
    header('X-Content-Type-Options: nosniff');
    header('Content-Disposition: inline; filename="' . $safe . '.pdf"');
    header('Content-Length: ' . strlen($pdf));
    echo $pdf;
}

function streamRoleLetterPdf(string $name, string $roleLabel, string $letterType, string $filename, array $details=[]): void {
    $pageWidth = 842;
    $systemName = (string)($details['system_name'] ?? APP_NAME);
    $institution = (string)($details['institution_name'] ?? APP_INSTITUTION);
    $programme = programmeTypeLabel((string)($details['programme_type'] ?? ''));
    $reference = (string)($details['code'] ?? $filename);
    $issued = (string)($details['issued_date'] ?? date('d M Y'));
    $letterType = $letterType === 'appointment' ? 'appointment' : 'invitation';
    $typeTitle = strtoupper(letterTypeLabel($letterType));
    $opening = $letterType === 'appointment' ? 'This appointment letter is issued to' : 'This invitation letter is issued to';
    $roleLine = $letterType === 'appointment' ? 'APPOINTED AS ' . strtoupper($roleLabel) : 'INVITED TO SERVE AS ' . strtoupper($roleLabel);

    $content = "q\n0.961 0.510 0.125 rg\n0 510 842 85 re f\nQ\n";
    $content .= fypesPdfTextCommand($systemName,42,555,22,true,'left',$pageWidth);
    $content .= fypesPdfTextCommand($institution,42,532,10,false,'left',$pageWidth);
    $content .= "0.118 0.106 0.173 RG 2 w 28 28 786 539 re S\n";
    $content .= fypesPdfTextCommand('OFFICIAL ' . strtoupper($roleLabel) . ' ' . $typeTitle,0,460,20,true,'center',$pageWidth);
    $content .= fypesPdfTextCommand($opening,0,415,12,false,'center',$pageWidth);
    $content .= fypesPdfTextCommand($name,0,374,25,true,'center',$pageWidth);
    $content .= "0.80 0.80 0.80 RG 1 w 170 355 m 672 355 l S\n";
    $content .= fypesPdfTextCommand($roleLine,0,316,17,true,'center',$pageWidth);
    $content .= fypesPdfTextCommand($programme,0,284,14,false,'center',$pageWidth);

    $responsibility = match(strtolower($roleLabel)) {
        'supervisor' => 'student supervision, progress monitoring, report review and supervisor evaluation',
        'report panel' => 'independent written-report review and Report Panel evaluation',
        'presentation panel' => 'independent project-presentation review and Presentation Panel evaluation',
        default => 'programme coordination, academic oversight, rubric management, deadlines, panel assignment and result administration'
    };
    $verb = $letterType === 'appointment' ? 'formally appointed' : 'formally invited';
    $paragraph = 'You are ' . $verb . ' to serve as ' . $roleLabel . ' for the programme stated above. This duty covers ' . $responsibility . ' in the Final Year Project Evaluation System.';
    foreach (fypesPdfWrap($paragraph,92,4) as $i=>$line) $content .= fypesPdfTextCommand($line,0,232-($i*18),10,false,'center',$pageWidth);
    $content .= fypesPdfTextCommand('Letter type: ' . letterTypeLabel($letterType),65,118,10,true,'left',$pageWidth);
    $content .= fypesPdfTextCommand('Reference: ' . $reference,65,98,10,true,'left',$pageWidth);
    $content .= fypesPdfTextCommand('Issued: ' . $issued,65,78,10,false,'left',$pageWidth);
    $content .= fypesPdfTextCommand('Generated by ' . $systemName,777,78,9,false,'right',$pageWidth);

    $objects = [
        1 => '<< /Type /Catalog /Pages 2 0 R >>',
        2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> /Contents 4 0 R >>',
        4 => '<< /Length ' . strlen($content) . ">>\nstream\n" . $content . 'endstream',
        5 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
        6 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
    ];
    fypesStreamPdf($objects,$filename);
}

function streamCompletionCertificatePdf(string $name, string $grade, string $filename, array $details=[]): void {
    $pageWidth = 842;
    $systemName = (string)($details['system_name'] ?? APP_NAME);
    $institution = (string)($details['institution_name'] ?? APP_INSTITUTION);
    $matric = (string)($details['matric_number'] ?? '');
    $programme = programmeTypeLabel((string)($details['programme_type'] ?? ''));
    $projectTitle = trim((string)($details['project_title'] ?? ''));
    $reference = (string)($details['code'] ?? $filename);
    $issued = (string)($details['issued_date'] ?? date('d M Y'));
    $score = isset($details['final_score']) ? number_format((float)$details['final_score'],2) : '';

    $content = "q\n0.961 0.510 0.125 rg\n0 500 842 95 re f\nQ\n";
    $content .= "0.118 0.106 0.173 RG 3 w 25 25 792 545 re S\n";
    $content .= "0.961 0.510 0.125 RG 1.5 w 37 37 768 521 re S\n";
    $content .= fypesPdfTextCommand($systemName,0,550,24,true,'center',$pageWidth);
    $content .= fypesPdfTextCommand($institution,0,525,11,false,'center',$pageWidth);
    $content .= fypesPdfTextCommand('CERTIFICATE OF COMPLETION',0,450,28,true,'center',$pageWidth);
    $content .= fypesPdfTextCommand('This certificate is proudly presented to',0,407,12,false,'center',$pageWidth);
    $content .= fypesPdfTextCommand($name,0,360,27,true,'center',$pageWidth);
    $content .= "0.80 0.80 0.80 RG 1 w 145 340 m 697 340 l S\n";
    $content .= fypesPdfTextCommand(trim($matric . ($programme ? '  |  ' . $programme : '')),0,314,12,false,'center',$pageWidth);
    $content .= fypesPdfTextCommand('for successfully completing the Final Year Project requirements.',0,270,12,false,'center',$pageWidth);
    if ($projectTitle !== '') {
        foreach (fypesPdfWrap('Project: ' . $projectTitle,86,2) as $i=>$line) $content .= fypesPdfTextCommand($line,0,235-($i*18),11,true,'center',$pageWidth);
    }
    $resultLine = 'Final Grade: ' . strtoupper($grade);
    if ($score !== '') $resultLine .= '   |   Final Score: ' . $score . '/100';
    $content .= fypesPdfTextCommand($resultLine,0,175,14,true,'center',$pageWidth);
    $content .= fypesPdfTextCommand('Certificate No: ' . $reference,60,93,10,true,'left',$pageWidth);
    $content .= fypesPdfTextCommand('Issued: ' . $issued,60,73,10,false,'left',$pageWidth);
    $content .= fypesPdfTextCommand('Verified by ' . $systemName,782,73,9,false,'right',$pageWidth);

    $objects = [
        1 => '<< /Type /Catalog /Pages 2 0 R >>',
        2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> /Contents 4 0 R >>',
        4 => '<< /Length ' . strlen($content) . ">>\nstream\n" . $content . 'endstream',
        5 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
        6 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
    ];
    fypesStreamPdf($objects,$filename);
}
