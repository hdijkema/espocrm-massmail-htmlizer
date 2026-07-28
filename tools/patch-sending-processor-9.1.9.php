<?php
/**
 * Apply the minimal MassMailHtmlizer integration to EspoCRM 9.1.9.
 *
 * Usage:
 *   php patch-sending-processor-9.1.9.php /path/to/SendingProcessor.php
 */

declare(strict_types=1);

const ORIGINAL_SHA256 = '91e4b5b0b2f1842a8b70facd55d9f1b64eb760945fb57760a7d1f64a4ca04e36';

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

$import =
    'use Espo\\Modules\\MassMailHtmlizer\\Tools\\MassEmail\\Processor as MassMailHtmlizerProcessor;';
$constructorProperty =
    'private MassMailHtmlizerProcessor $massMailHtmlizerProcessor,';
$entityHashCall =
    '$this->massMailHtmlizerProcessor->getEntityHash($massEmail)';

$integrationParts = [
    $import,
    $constructorProperty,
    $entityHashCall,
];

$foundPartCount = 0;

foreach ($integrationParts as $part) {
    if (str_contains($content, $part)) {
        $foundPartCount++;
    }
}

if ($foundPartCount === count($integrationParts)) {
    foreach ($integrationParts as $part) {
        if (substr_count($content, $part) !== 1) {
            fwrite(
                STDERR,
                "FOUT: MassMailHtmlizer-integratie komt niet exact eenmaal voor.\n"
            );
            exit(1);
        }
    }

    echo "SendingProcessor.php is al gepatcht voor MassMailHtmlizer.\n";
    exit(0);
}

if ($foundPartCount !== 0) {
    fwrite(
        STDERR,
        "FOUT: SendingProcessor.php bevat een gedeeltelijke of afwijkende " .
        "MassMailHtmlizer-integratie.\n"
    );
    exit(1);
}

$eol = str_contains($content, "\r\n") ? "\r\n" : "\n";
$normalizedContent = str_replace("\r\n", "\n", $content);
$hash = hash('sha256', $normalizedContent);

if ($hash !== ORIGINAL_SHA256) {
    fwrite(
        STDERR,
        "FOUT: SendingProcessor.php is niet de verwachte schone EspoCRM 9.1.9-versie.\n" .
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
        "        private Config\\ApplicationConfig \$applicationConfig,\n" .
        "    ) {}\n",
        "        private Config\\ApplicationConfig \$applicationConfig,\n" .
        "        private MassMailHtmlizerProcessor \$massMailHtmlizerProcessor,\n" .
        "    ) {}\n",
    ],
    [
        "            TemplateData::create()\n" .
        "                ->withParent(\$target)\n" .
        "        );\n",
        "            TemplateData::create()\n" .
        "                ->withParent(\$target)\n" .
        "                ->withEntityHash(\n" .
        "                    \$this->massMailHtmlizerProcessor->getEntityHash(\$massEmail)\n" .
        "                )\n" .
        "        );\n",
    ],
];

foreach ($replacements as [$search, $replace]) {
    $count = substr_count($normalizedContent, $search);

    if ($count !== 1) {
        fwrite(
            STDERR,
            "FOUT: verwacht patchanker niet exact eenmaal gevonden ({$count}).\n"
        );
        exit(1);
    }

    $normalizedContent = str_replace($search, $replace, $normalizedContent);
}

foreach ($integrationParts as $part) {
    if (substr_count($normalizedContent, $part) !== 1) {
        fwrite(
            STDERR,
            "FOUT: controle van de aangebrachte MassMailHtmlizer-integratie is mislukt.\n"
        );
        exit(1);
    }
}

$patchedContent = $eol === "\r\n" ?
    str_replace("\n", "\r\n", $normalizedContent) :
    $normalizedContent;

$tmp = $file . '.massmail-htmlizer.tmp';

if (file_put_contents($tmp, $patchedContent) === false) {
    fwrite(STDERR, "FOUT: tijdelijk gepatcht bestand kon niet worden geschreven.\n");
    exit(2);
}

$permissions = fileperms($file);

if ($permissions !== false) {
    chmod($tmp, $permissions & 0777);
}

if (!rename($tmp, $file)) {
    @unlink($tmp);
    fwrite(STDERR, "FOUT: gepatcht bestand kon niet worden geplaatst.\n");
    exit(2);
}

echo "SendingProcessor.php gepatcht voor MassMailHtmlizer op EspoCRM 9.1.9.\n";
