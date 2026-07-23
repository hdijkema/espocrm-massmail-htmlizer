<?php

declare(strict_types=1);

const ORIGINAL_SHA256 = '42e1c64c39d93b29fb0fa2b1f79960f191b0ac9e6f7995eb079917a3bd87ac89';

$file = $argv[1] ?? '';

if ($file === '' || !is_file($file)) {
    fwrite(STDERR, "FOUT: SendingProcessor.php niet gevonden: {$file}\n");
    exit(2);
}

$content = file_get_contents($file);

if ($content === false) {
    fwrite(STDERR, "FOUT: SendingProcessor.php kon niet worden gelezen.\n");
    exit(2);
}

$import = 'use Espo\\Modules\\MassMailHtmlizer\\Tools\\MassEmail\\Processor as MassMailHtmlizerProcessor;';
$entityHashCall = '->withEntityHash($this->massMailHtmlizerProcessor->getEntityHash($massEmail))';

if (str_contains($content, $import) && str_contains($content, $entityHashCall)) {
    echo "SendingProcessor.php is al gepatcht voor MassMailHtmlizer.\n";
    exit(0);
}

$eol = str_contains($content, "\r\n") ? "\r\n" : "\n";
$normalized = str_replace("\r\n", "\n", $content);
$hash = hash('sha256', $normalized);

if ($hash !== ORIGINAL_SHA256) {
    fwrite(
        STDERR,
        "FOUT: SendingProcessor.php is niet de verwachte schone EspoCRM 9.0.8-versie.\n" .
        "Verwacht: " . ORIGINAL_SHA256 . "\n" .
        "Gevonden: {$hash}\n"
    );
    exit(1);
}

$replacements = [
    [
        "use Espo\\Tools\\EmailTemplate\\Processor as TemplateProcessor;\n",
        "use Espo\\Tools\\EmailTemplate\\Processor as TemplateProcessor;\n" .
        $import . "\n",
    ],
    [
        "        private MessageHeadersPreparator \$headersPreparator,\n" .
        "        private TemplateProcessor \$templateProcessor\n",
        "        private MessageHeadersPreparator \$headersPreparator,\n" .
        "        private TemplateProcessor \$templateProcessor,\n" .
        "        private MassMailHtmlizerProcessor \$massMailHtmlizerProcessor\n",
    ],
    [
        "            TemplateData::create()\n" .
        "                ->withParent(\$target)\n",
        "            TemplateData::create()\n" .
        "                ->withParent(\$target)\n" .
        "                ->withEntityHash(\$this->massMailHtmlizerProcessor->getEntityHash(\$massEmail))\n",
    ],
];

foreach ($replacements as [$search, $replace]) {
    if (substr_count($normalized, $search) !== 1) {
        fwrite(STDERR, "FOUT: MassMailHtmlizer-patchanker niet exact eenmaal gevonden.\n");
        exit(1);
    }

    $normalized = str_replace($search, $replace, $normalized);
}

if ($eol === "\r\n") {
    $normalized = str_replace("\n", "\r\n", $normalized);
}

file_put_contents($file, $normalized);

echo "SendingProcessor.php gepatcht voor MassMailHtmlizer.\n";
