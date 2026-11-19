<?php
declare(strict_types=1);

/** Generador PDF local y ligero para reportes SUPAY. No requiere internet ni extensiones externas. */
final class SupayPdf
{
    private const WIDTH = 595.28;
    private const HEIGHT = 841.89;
    private array $pages = [];
    private string $content = '';
    private float $y = 790;
    public function __construct(private string $title, private string $period = '', private string $subtitle = '')
    {
        $this->newPage();
    }

    public function newPage(): void
    {
        if ($this->content !== '') {
            $this->addFooter();
            $this->pages[] = $this->content . " Q\n";
        }
        $this->content = '';
        $this->y = 790;
        $this->content .= "q 1 1 1 rg 0 0 595.28 841.89 re f\n";
        $this->content .= "0.55 0.16 0.10 rg 0 780 595.28 61.89 re f\n";
        $this->content .= "0.78 0.30 0.22 rg 0 777 595.28 3 re f\n";
        $this->text(38, 817, 'SUPAY', 18, [1, 1, 1], true);
        $this->text(38, 800, $this->title, 11, [0.92, 0.82, 0.78], true);
        if ($this->subtitle !== '') {
            $this->text(38, 786, $this->subtitle, 7.5, [0.96, 0.90, 0.86]);
        }
        if ($this->period !== '') {
            $this->text(405, 817, $this->period, 8, [0.96, 0.90, 0.86], false, true);
        }
        $this->y = 750;
    }

    public function heading(string $text): void
    {
        $this->ensureSpace(34);
        $this->content .= "0.55 0.16 0.10 rg 30 {$this->y} 535 25 re f\n";
        $this->text(40, $this->y + 8, $text, 11, [1, 1, 1], true);
        $this->y -= 37;
    }

    public function summary(string $label, string $value): void
    {
        $this->ensureSpace(48);
        $this->content .= "0.94 0.95 0.96 rg 30 {$this->y} 535 38 re f\n";
        $this->text(42, $this->y + 23, $label, 9, [0.35, 0.38, 0.42]);
        $this->text(42, $this->y + 8, $value, 14, [0.70, 0.20, 0.13], true);
        $this->y -= 50;
    }

    public function table(array $headers, array $rows, array $numericColumns = []): void
    {
        $columns = count($headers);
        if ($columns === 0) return;
        $width = 535 / $columns;
        $this->ensureSpace(30);
        $this->content .= "0.55 0.16 0.10 rg 30 {$this->y} 535 24 re f\n";
        foreach ($headers as $i => $header) {
            $this->text(36 + $i * $width, $this->y + 8, $header, 7.5, [1, 1, 1], true);
        }
        $this->y -= 24;
        foreach ($rows as $row) {
            $this->ensureSpace(22);
            $this->content .= "0.96 0.97 0.98 rg 30 {$this->y} 535 21 re f\n";
            foreach (array_values($row) as $i => $value) {
                $value = (string)$value;
                if (mb_strlen($value) > 27) $value = mb_substr($value, 0, 24) . '...';
                $this->text(36 + $i * $width, $this->y + 7, $value, 7.3, [0.18, 0.20, 0.23], false, in_array($i, $numericColumns, true));
            }
            $this->y -= 21;
        }
        $this->y -= 10;
    }

    public function note(string $text): void
    {
        $this->ensureSpace(24);
        $this->text(34, $this->y, $text, 8.5, [0.35, 0.38, 0.42]);
        $this->y -= 20;
    }

    public function output(string $filename): never
    {
        if ($this->content !== '') {
            $this->addFooter();
            $this->pages[] = $this->content . " Q\n";
        }
        $objects = [];
        $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[] = '';
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
        $pageObjectNumbers = [];
        $next = count($objects) + 1;
        foreach ($this->pages as $page) {
            $pageObjectNumbers[] = $next;
            $next += 2;
        }
        $kids = implode(' ', array_map(static fn(int $n): string => $n . ' 0 R', $pageObjectNumbers));
        $objects[1] = '<< /Type /Pages /Kids [' . $kids . '] /Count ' . count($this->pages) . ' >>';
        $next = $pageObjectNumbers[0] ?? (count($objects) + 1);
        foreach ($this->pages as $page) {
            $contentNumber = $next + 1;
            $resources = '/Font << /F1 3 0 R /F2 4 0 R >>';
            $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595.28 841.89] /Resources << ' . $resources . ' >> /Contents ' . $contentNumber . ' 0 R >>';
            $objects[] = '<< /Length ' . strlen($page) . " >>\nstream\n" . $page . "endstream";
            $next += 2;
        }
        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];
        foreach ($objects as $i => $object) {
            $number = $i + 1;
            $offsets[] = strlen($pdf);
            $pdf .= $number . " 0 obj\n" . $object . "\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF";
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $filename) . '"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }

    private function ensureSpace(float $height): void
    {
        if ($this->y - $height < 42) $this->newPage();
    }

    private function addFooter(): void
    {
        $this->content .= "0.78 0.30 0.22 rg 30 34 535 1 re f\n";
        $this->text(30, 22, 'SUPAY | Sistema de gestión de taller', 7, [0.55, 0.58, 0.62]);
        $this->text(565, 22, 'Documento generado automáticamente', 7, [0.55, 0.58, 0.62], false, true);
    }

    private function drawFallbackLogo(): void
    {
        // Logo vectorial para instalaciones donde GD no está habilitado en PHP.
        $this->content .= "0.85 0.47 0.32 rg 57 811 m 57 826.46 44.46 839 29 839 c 13.54 839 1 826.46 1 811 c 1 795.54 13.54 783 29 783 c 44.46 783 57 795.54 57 811 c f\n";
        $this->content .= "0.08 0.12 0.16 rg 53 811 m 53 824.25 42.25 835 29 835 c 15.75 835 5 824.25 5 811 c 5 797.75 15.75 787 29 787 c 42.25 787 53 797.75 53 811 c f\n";
        $this->text(20, 803, 'S', 22, [1, 0.82, 0.70], true);
    }

    private function loadLogo(): ?array
    {
        $path = __DIR__ . '/../assets/img/supay-logo.png';
        if (!function_exists('imagecreatefrompng') || !is_file($path)) return null;
        $source = @imagecreatefrompng($path);
        if ($source === false) return null;
        $width = 92;
        $height = 92;
        $image = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);
        imagecopyresampled($image, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));
        ob_start();
        imagejpeg($image, null, 88);
        $data = ob_get_clean();
        imagedestroy($source);
        imagedestroy($image);
        return $data === false ? null : ['width' => $width, 'height' => $height, 'data' => $data];
    }

    private function text(float $x, float $y, string $text, float $size, array $color, bool $bold = false, bool $right = false): void
    {
        $encoded = pdf_reporte_encode($text);
        $width = max(1, strlen($encoded)) * $size * 0.48;
        if ($right) $x -= $width;
        $font = $bold ? '/F2' : '/F1';
        $this->content .= sprintf("BT %s %.2f Tf %.3f %.3f %.3f rg %.2f %.2f Td (%s) Tj ET\n", $font, $size, $color[0], $color[1], $color[2], $x, $y, pdf_reporte_escape($encoded));
    }
}

function pdf_reporte_encode(string $value): string
{
    if (function_exists('iconv')) {
        $converted = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value);
        if ($converted !== false) return $converted;
    }
    return strtr($value, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ñ'=>'N']);
}

function pdf_reporte_escape(string $value): string
{
    return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '', ' '], $value);
}
